<?php

namespace shopium24\mod\user\models;

use app\modules\hosting\components\Api;
use Yii;
use panix\engine\db\ActiveRecord;
use yii\swiftmailer\Mailer;
use yii\swiftmailer\Message;
use yii\helpers\Inflector;
use ReflectionClass;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "tbl_user".
 *
 * @property string $id
 * @property integer $status
 * @property string $email
 * @property string $new_email
 * @property string $username
 * @property string $password
 * @property string $auth_key
 * @property string $api_key
 * @property string $login_ip
 * @property string $login_time
 * @property string $create_ip
 * @property string $created_at
 * @property string $updated_at
 * @property string $ban_time
 * @property string $ban_reason
 * @property Sites $sites
 * @property UserKey[] $userKeys
 * @property UserAuth[] $userAuths
 */
class User extends ActiveRecord implements IdentityInterface
{

    const MODULE_ID = 'user';
    /**
     * @var int Inactive status
     */
    const STATUS_INACTIVE = 0;

    /**
     * @var int Active status
     */
    const STATUS_ACTIVE = 1;

    /**
     * @var int Unconfirmed email status
     */
    const STATUS_UNCONFIRMED_EMAIL = 2;

    /**
     * @var string Current password - for account page updates
     */
    public $currentPassword;

    /**
     * @var string New password - for registration and changing password
     */
    public $newPassword;

    /**
     * @var string New password confirmation - for reset
     */
    public $newPasswordConfirm;
    public $password_confirm;
    /**
     * @var array Permission cache array
     */
    protected $_access = [];
    public $verifyCode;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return "{{%user}}";
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [];
        $configApp = Yii::$app->settings->get('app');
        if ($configApp->captcha_class && Yii::$app->user->isGuest) {
            if ($configApp->captcha_class == '\panix\engine\widgets\recaptcha\v2\ReCaptcha') {
                $rules[] = ['verifyCode', 'panix\engine\widgets\recaptcha\v2\ReCaptchaValidator', 'on' => ['register']];
            } else if ($configApp->captcha_class == '\panix\engine\widgets\recaptcha\v3\ReCaptcha') {
                $rules[] = ['verifyCode', 'panix\engine\widgets\recaptcha\v3\ReCaptchaValidator', 'on' => ['register']];
            } else { // \yii\captcha\Captcha
                $rules[] = ['verifyCode', 'captcha', 'on' => ['register']];
                $rules[] = [['verifyCode'], 'required', 'on' => ['register']];
            }
        }
        // general email and username rules
        $rules[] = [['username'], 'required'];
        $rules[] = [['email', 'username'], 'string', 'max' => 255];
        $rules[] = [['email', 'username'], 'unique'];
        $rules[] = [['email', 'username'], 'filter', 'filter' => 'trim'];
        $rules[] = [['email'], 'email'];
        $rules[] = [['username'], 'email', 'on' => ['register']];


        //[['username'], 'match', 'pattern' => '/^[A-Za-z0-9_]+$/u', 'message' => Yii::t('user/default', '{attribute} can contain only letters, numbers, and "_"')],
        // password rules
        $rules[] = [['password'], 'string', 'min' => 3];
        $rules[] = [['password'], 'filter', 'filter' => 'trim'];
        $rules[] = [['password', 'password_confirm'], 'required', 'on' => ['register', 'reset']];
        //[['newPasswordConfirm'], 'required', 'on' => ['reset']],
        //[['newPasswordConfirm'], 'compare', 'compareAttribute' => 'newPassword', 'message' => Yii::t('user/default', 'Passwords do not match')],
        $rules[] = [['password_confirm'], 'compare', 'compareAttribute' => 'password', 'message' => Yii::t('user/default', 'Passwords do not match')];
        // account page
        //[['currentPassword'], 'required', 'on' => ['account']],
        //[['currentPassword'], 'validateCurrentPassword', 'on' => ['account']],
        $rules[] = [['ban_time'], 'integer', 'on' => ['admin']];
        $rules[] = [['ban_reason'], 'string', 'max' => 255, 'on' => 'admin'];
        return $rules;
    }


    /**
     * Validate current password (account page)
     */
    public function validateCurrentPassword()
    {
        if (!$this->verifyPassword($this->currentPassword)) {
            $this->addError("currentPassword", "Current password incorrect");
        }
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSites()
    {
        return $this->hasMany(Sites::class, ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery

    public function getRole() {
     * $role = Yii::$app->getModule("user")->model("Role");
     * return $this->hasOne($role::className(), ['id' => 'role_id']);
     * }*/

    public function getSession()
    {
        return $this->hasOne(SessionUser::class, ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserKeys()
    {
        $userKey = Yii::$app->getModule("user")->model("UserKey");
        return $this->hasMany($userKey::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserAuths()
    {
        return $this->hasMany(UserAuth::class, ['user_id' => 'id']);
    }


    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(["api_key" => $token]);
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->auth_key === $authKey;
    }

    /**
     * Verify password
     *
     * @param string $password
     * @return bool
     */
    public function verifyPassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password);
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        // hash new password if set
        if ($this->password) {
            $this->password = Yii::$app->security->generatePasswordHash($this->password);
        }

        // convert ban_time checkbox to date
        if ($this->ban_time) {
            $this->ban_time = date("Y-m-d H:i:s");
        }
        if (!$this->email)
            $this->email = $this->username;

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * Set attributes for registration
     *
     * @param string $userIp
     * @param string $status
     * @return static
     */
    public function setRegisterAttributes($userIp, $status = null)
    {
        // set default attributes
        $attributes = [
            "create_ip" => $userIp,
            "auth_key" => Yii::$app->security->generateRandomString(),
            "api_key" => Yii::$app->security->generateRandomString(),
            "status" => static::STATUS_ACTIVE,
        ];

        // determine if we need to change status based on module properties
        $emailConfirmation = Yii::$app->getModule("user")->emailConfirmation;
        $requireEmail = Yii::$app->getModule("user")->requireEmail;
        $useEmail = Yii::$app->getModule("user")->useEmail;
        if ($status) {
            $attributes["status"] = $status;
        } elseif ($emailConfirmation && $requireEmail) {
            $attributes["status"] = static::STATUS_INACTIVE;
        } elseif ($emailConfirmation && $useEmail && $this->email) {
            $attributes["status"] = static::STATUS_UNCONFIRMED_EMAIL;
        }

        // set attributes and return
        $this->setAttributes($attributes, false);
        return $this;
    }

    /**
     * Check and prepare for email change
     *
     * @return bool True if user set a `new_email`
     */
    public function checkAndPrepEmailChange()
    {
        // check if user is removing email address (only if Module::$requireEmail = false)
        if (trim($this->email) === "") {
            return false;
        }

        // check for change in email
        if ($this->email != $this->getOldAttribute("email")) {

            // change status
            $this->status = static::STATUS_UNCONFIRMED_EMAIL;

            // set `new_email` attribute and restore old one
            $this->new_email = $this->email;
            $this->email = $this->getOldAttribute("email");

            return true;
        }

        return false;
    }

    /**
     * Update login info (ip and time)
     *
     * @return bool
     */
    public function updateLoginMeta()
    {
        // set data
        $this->login_ip = Yii::$app->getRequest()->getUserIP();
        $this->login_time = date("Y-m-d H:i:s");

        // save and return
        return $this->save(false, ["login_ip", "login_time"]);
    }

    /**
     * Confirm user email
     *
     * @return bool
     */
    public function confirm()
    {
        // update status
        $this->status = static::STATUS_ACTIVE;

        // update new_email if set
        if ($this->new_email) {
            $this->email = $this->new_email;
            $this->new_email = null;
        }

        // save and return
        return $this->save(false, ["email", "new_email", "status"]);
    }

    /**
     * Check if user can do specified $permission
     *
     * @param string $permissionName
     * @param array $params
     * @param bool $allowCaching
     * @return bool
     */
    public function can($permissionName, $params = [], $allowCaching = true)
    {
        // check for auth manager rbac
        $auth = Yii::$app->getAuthManager();
        if ($auth) {
            if ($allowCaching && empty($params) && isset($this->_access[$permissionName])) {
                return $this->_access[$permissionName];
            }
            $access = $auth->checkAccess($this->getId(), $permissionName, $params);
            if ($allowCaching && empty($params)) {
                $this->_access[$permissionName] = $access;
            }

            return $access;
        }

        // otherwise use our own custom permission (via the role table)
        return $this->role->checkPermission($permissionName);
    }

    /**
     * Get display name for the user
     *
     * @var string $default
     * @return string|int
     */
    public function getDisplayName($default = "")
    {
        // define possible fields
        $possibleNames = [
            "username",
            "email",
            "id",
        ];

        // go through each and return if valid
        foreach ($possibleNames as $possibleName) {
            if (!empty($this->$possibleName)) {
                return $this->$possibleName;
            }
        }

        return $default;
    }

    /**
     * Send email confirmation to user
     *
     * @param UserKey $userKey
     * @return int
     */
    public function sendEmailConfirmation($userKey)
    {
        /** @var Mailer $mailer */
        /** @var Message $message */
        // modify view path to module views
        $mailer = Yii::$app->mailer;
        $oldViewPath = $mailer->viewPath;
        $mailer->viewPath = Yii::$app->getModule("user")->emailViewPath;

        // send email
        $user = $this;
        $email = $user->new_email !== null ? $user->new_email : $user->email;
        $subject = Yii::$app->id . " - " . Yii::t("user/default", "Email Confirmation");
        $message = $mailer->compose('confirmEmail', compact("subject", "user", "userKey"))
            ->setTo($email)
            ->setSubject($subject);

        // check for messageConfig before sending (for backwards-compatible purposes)
        if (empty($mailer->messageConfig["from"])) {
            $message->setFrom(Yii::$app->params["adminEmail"]);
        }
        $result = $message->send();

        // restore view path and return result
        $mailer->viewPath = $oldViewPath;
        return $result;
    }

    /**
     * Get list of statuses for creating dropdowns
     *
     * @return array
     */
    public static function statusDropdown()
    {
        // get data if needed
        static $dropdown;
        if ($dropdown === null) {

            // create a reflection class to get constants
            $reflClass = new ReflectionClass(get_called_class());
            $constants = $reflClass->getConstants();

            // check for status constants (e.g., STATUS_ACTIVE)
            foreach ($constants as $constantName => $constantValue) {

                // add prettified name to dropdown
                if (strpos($constantName, "STATUS_") === 0) {
                    $prettyName = str_replace("STATUS_", "", $constantName);
                    $prettyName = Inflector::humanize(strtolower($prettyName));
                    $dropdown[$constantValue] = $prettyName;
                }
            }
        }

        return $dropdown;
    }

}
