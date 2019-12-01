<?php

use panix\engine\data\ActiveDataProvider;
use shopium24\mod\user\models\Payments;
use yii\grid\GridView;
use panix\engine\widgets\Pjax;

$dataProvider = new ActiveDataProvider([
    'query' => Payments::find()->where([
        'user_id'=>$user->id,

    ]),
    'pagination' => [
        'pageSize' => 20,
    ],
]);
Pjax::begin();
echo GridView::widget([
    'dataProvider' => $dataProvider,
    //'itemView' => '_post',
    'columns'=>[
        'term_time',
        'created_at',
        'site.subdomain'
    ]
]);
Pjax::end();



