<?php

namespace shopium24\mod\user\controllers;


use panix\engine\controllers\WebController;


/**
 * Default controller for User module
 */
class PaymentController extends WebController
{
    public function beforeAction($action)
    {
        if ($action->id == 'result') {
            $this->enableCsrfValidation = false;
            //return true;
        }

        return parent::beforeAction($action);
    }

    public function actionResult(){

        print_r($_POST);
        print_r($_REQUEST);
        die;
    }
}