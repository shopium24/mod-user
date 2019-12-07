<?php

namespace shopium24\mod\user\controllers;

use panix\engine\CMS;
use panix\engine\controllers\WebController;
use shopium24\mod\user\models\Payments;
use shopium24\mod\user\models\search\SitesSearch;
use shopium24\mod\user\models\Sites;
use shopium24\mod\user\models\User;
use shopium24\mod\user\models\UserKey;
use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;
use panix\mod\rbac\filters\AccessControl;

/**
 * Site controller for User module
 */
class SiteController extends WebController
{


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

    public function actionEdit($id)
    {
        $model = Sites::findOne($id);
        if ($model->user_id != Yii::$app->user->id) {
            $this->error404();
        }
        return $this->render('edit', [
            'model' => $model,
        ]);

    }
}
