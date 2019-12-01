<?php

namespace shopium24\mod\user\controllers;


use panix\engine\controllers\WebController;


/**
 * Default controller for User module
 */
class PaymentController extends WebController
{
    public function actionResult(){
        print_r($_POST);
        print_r($_REQUEST);
        die;
    }
}