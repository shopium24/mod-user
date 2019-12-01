<?php

namespace shopium24\mod\user\controllers;

use panix\engine\CMS;
use panix\engine\controllers\WebController;
use shopium24\mod\user\models\search\SitesSearch;
use shopium24\mod\user\models\Sites;
use shopium24\mod\user\models\User;
use shopium24\mod\user\models\UserKey;
use Yii;
use yii\web\Response;
use yii\widgets\ActiveForm;
use panix\mod\rbac\filters\AccessControl;

/**
 * Default controller for User module
 */
class DefaultController extends WebController
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'allowActions' => [
                    '*',
                    // The actions listed here will be allowed to everyone including guests.
                ]
            ],
        ];
    }

    /**
     * Display index - debug page, login page, or account page
     */
    public function actionIndex()
    {
        if (defined('YII_DEBUG') && YII_DEBUG) {
            $actions = Yii::$app->getModule("user")->getActions();
            return $this->render('index', ["actions" => $actions]);
        } elseif (Yii::$app->user->isGuest) {
            return $this->redirect(["/user/login"]);
        } else {
            return $this->redirect(["/user/account"]);
        }
    }

    /**
     * Display login page
     */
    public function actionLogin()
    {
        // load post data and login
        $model = Yii::$app->getModule("user")->model("LoginForm");
        if ($model->load(Yii::$app->request->post()) && $model->login(Yii::$app->getModule("user")->loginDuration)) {
            return $this->goBack(Yii::$app->getModule("user")->loginRedirect);
        }

        // render
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Log user out and redirect
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->goHome();
    }

    public function actionSites()
    {
        $sites = Sites::find()->all();

        $searchModel = new SitesSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());


        return $this->render("sites", [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);

    }

    /**
     * Display registration page
     */
    public function actionRegister()
    {


        // set up new user/profile objects
        $user = new User; //Yii::$app->getModule("user")->model("User", ["scenario" => "register"]);
        $user->setScenario('register');
        $sites = new Sites;
        //  $sites->setScenario('register');

        $sites->plan_id = Yii::$app->request->get('plan');


        // load post data
        $post = Yii::$app->request->post();
        if ($user->load($post)) {

            // ensure profile data gets loaded
            $sites->load($post);

            // validate for ajax request
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($user, $sites);
            }

            // validate for normal request
            if ($user->validate() && $sites->validate()) {


                $user->save();
                $sites->user_id = $user->id;
                $sites->hosting_account_id = 1;
                $sites->save();
                $this->afterRegister($user);

                // set flash
                // don't use $this->refresh() because user may automatically be logged in and get 403 forbidden
                $successText = Yii::t("user/default", "Successfully registered [ {displayName} ]", ["displayName" => $user->getDisplayName()]);
                $guestText = "";
                if (Yii::$app->user->isGuest) {
                    $guestText = Yii::t("user/default", " - Please check your email to confirm your account");
                }
                Yii::$app->session->setFlash("Register-success", $successText . $guestText);
            } else {
                CMS::dump($user->errors);
                CMS::dump($sites->errors);

            }
        }

        // render
        return $this->render("register", [
            'user' => $user,
            'site' => $sites,
        ]);
    }

    /**
     * Process data after registration
     *
     * @param \shopium24\mod\user\models\User $user
     */
    protected function afterRegister($user)
    {
        // determine userKey type to see if we need to send email
        $userKey = new UserKey();
        if ($user->status == $user::STATUS_INACTIVE) {
            $userKeyType = $userKey::TYPE_EMAIL_ACTIVATE;
        } elseif ($user->status == $user::STATUS_UNCONFIRMED_EMAIL) {
            $userKeyType = $userKey::TYPE_EMAIL_CHANGE;
        } else {
            $userKeyType = null;
        }

        // check if we have a userKey type to process, or just log user in directly
        if ($userKeyType) {

            // generate userKey and send email
            $userKey = $userKey::generate($user->id, $userKeyType);
            if (!$numSent = $user->sendEmailConfirmation($userKey)) {

                // handle email error
                //Yii::$app->session->setFlash("Email-error", "Failed to send email");
            }
        } else {
            Yii::$app->user->login($user, Yii::$app->getModule("user")->loginDuration);
        }
    }

    /**
     * Confirm email
     */
    public function actionConfirm($key)
    {
        /** @var \shopium24\mod\user\models\UserKey $userKey */
        /** @var \shopium24\mod\user\models\User $user */
        // search for userKey
        $success = false;
        $userKey = Yii::$app->getModule("user")->model("UserKey");
        $userKey = $userKey::findActiveByKey($key, [$userKey::TYPE_EMAIL_ACTIVATE, $userKey::TYPE_EMAIL_CHANGE]);
        if ($userKey) {

            // confirm user
            $user = Yii::$app->getModule("user")->model("User");
            $user = $user::findOne($userKey->user_id);
            $user->confirm();

            // consume userKey and set success
            $userKey->consume();
            $success = $user->email;
        }

        // render
        return $this->render("confirm", [
            "userKey" => $userKey,
            "success" => $success
        ]);
    }

    /**
     * Account
     */
    public function actionAccount()
    {
        $user = Yii::$app->user->identity;
        $user->setScenario("account");
        $loadedPost = $user->load(Yii::$app->request->post());

        // validate for ajax request
        if ($loadedPost && Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($user);
        }

        // validate for normal request
        if ($loadedPost && $user->validate()) {

            // generate userKey and send email if user changed his email
            if (Yii::$app->getModule("user")->emailChangeConfirmation && $user->checkAndPrepEmailChange()) {

                $userKey = Yii::$app->getModule("user")->model("UserKey");
                $userKey = $userKey::generate($user->id, $userKey::TYPE_EMAIL_CHANGE);
                if (!$numSent = $user->sendEmailConfirmation($userKey)) {

                    // handle email error
                    //Yii::$app->session->setFlash("Email-error", "Failed to send email");
                }
            }

            // save, set flash, and refresh page
            $user->save(false);
            Yii::$app->session->setFlash("Account-success", Yii::t("user/default", "Account updated"));
            return $this->refresh();
        }

        // render
        return $this->render("account", [
            'user' => $user,
        ]);
    }

    /**
     * Profile
     */
    public function actionProfile()
    {
        if (!Yii::$app->user->isGuest) {
            $user = Yii::$app->user->identity;
            $this->pageName = Yii::t('user/default', 'PROFILE');
            $loadedPost = $user->load(Yii::$app->request->post());

            // validate for ajax request
            if ($loadedPost && Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($user);
            }

            // validate for normal request
            if ($loadedPost && $user->validate()) {
                $user->save(false);
                Yii::$app->session->setFlash("profile-success", Yii::t("user/default", "Profile updated"));
                return $this->refresh();
            }

            // render
            return $this->render("profile", [
                'user' => $user,
            ]);
        } else {
            $this->error404();
        }
    }

    /**
     * Resend email confirmation
     */
    public function actionResend()
    {
        $model = Yii::$app->getModule("user")->model("ResendForm");
        if ($model->load(Yii::$app->request->post()) && $model->sendEmail()) {

            // set flash (which will show on the current page)
            Yii::$app->session->setFlash("Resend-success", Yii::t("user/default", "Confirmation email resent"));
        }
        return $this->render("resend", [
            "model" => $model,
        ]);
    }

    /**
     * Resend email change confirmation
     */
    public function actionResendChange()
    {
        $user = Yii::$app->user->identity;
        $userKey = Yii::$app->getModule("user")->model("UserKey");
        $userKey = $userKey::findActiveByUser($user->id, $userKey::TYPE_EMAIL_CHANGE);
        if ($userKey) {

            // send email and set flash message
            $user->sendEmailConfirmation($userKey);
            Yii::$app->session->setFlash("Resend-success", Yii::t("user/default", "Confirmation email resent"));
        }

        return $this->redirect(["/user/account"]);
    }

    /**
     * Cancel email change
     */
    public function actionCancel()
    {
        $user = Yii::$app->user->identity;
        $userKey = Yii::$app->getModule("user")->model("UserKey");
        $userKey = $userKey::findActiveByUser($user->id, $userKey::TYPE_EMAIL_CHANGE);
        if ($userKey) {

            // remove `user.new_email`
            $user->new_email = null;
            $user->save(false);

            // expire userKey and set flash message
            $userKey->expire();
            Yii::$app->session->setFlash("Cancel-success", Yii::t("user/default", "Email change cancelled"));
        }

        return $this->redirect(["/user/account"]);
    }

    /**
     * Forgot password
     */
    public function actionForgot()
    {
        $model = Yii::$app->getModule("user")->model("ForgotForm");
        if ($model->load(Yii::$app->request->post()) && $model->sendForgotEmail()) {

            // set flash (which will show on the current page)
            Yii::$app->session->setFlash("Forgot-success", Yii::t("user/default", "Instructions to reset your password have been sent"));
        }

        return $this->render("forgot", [
            "model" => $model,
        ]);
    }

    /**
     * Reset password
     */
    public function actionReset($key)
    {
        $userKey = Yii::$app->getModule("user")->model("UserKey");
        $userKey = $userKey::findActiveByKey($key, $userKey::TYPE_PASSWORD_RESET);
        if (!$userKey) {
            return $this->render('reset', ["invalidKey" => true]);
        }

        // get user and set "reset" scenario
        $success = false;
        $user = Yii::$app->getModule("user")->model("User");
        $user = $user::findOne($userKey->user_id);
        $user->setScenario("reset");

        // load post data and reset user password
        if ($user->load(Yii::$app->request->post()) && $user->save()) {

            // consume userKey and set success = true
            $userKey->consume();
            $success = true;
        }

        return $this->render('reset', compact("user", "success"));
    }

}
