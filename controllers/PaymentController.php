<?php

namespace shopium24\mod\user\controllers;

use Yii;
use panix\engine\CMS;
use panix\engine\controllers\WebController;
use yii\httpclient\Client;

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



/*
        $client = new Client([
            'transport' => 'yii\httpclient\CurlTransport',
        ]);
        //$client = new Client();

        $response = $client->createRequest()
            ->setMethod('POST')
            ->setUrl('https://api.privatbank.ua/p24api/ishop')
            ->setData([
                'pay_way' => 'privat24',
                'server_url' => 'https://shopium24.com/user/payment/process',
                'return_url' => 'https://shopium24.com/user/payment/result',
                'ext_details' => 'Оптала тарифного плана Basic, на 1 месяц.',
                'details' => 'Оплата аренды на 1 мес.',
                'order' => 's_'.CMS::gen(111),
                'amt' => 500,
                'ccy' => 'UAH',
                'merchant' => '152357',
            ])
            //->setOptions([
            //    'proxy' => 'tcp://proxy.example.com:5100', // use a Proxy
            //    'timeout' => 5, // set timeout to 5 seconds for the case server is not responding
           // ])
            ->send();
        if ($response->isOk) {
            print_r($response->data);die;
        }

        return $this->redirect($response->headers['location']);
       CMS::dump($response->headers['location']);die;

        print_r($_POST);
        print_r($_REQUEST);
        die;*/

Yii::debug('test');
    }
}