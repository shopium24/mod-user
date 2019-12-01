<?php

use panix\engine\data\ActiveDataProvider;
use shopium24\mod\user\models\Payments;
use yii\grid\GridView;
use panix\engine\widgets\Pjax;

$dataProvider = new ActiveDataProvider([
    'query' => Payments::find()->where([
        'user_id' => $user->id,

    ]),
    'pagination' => [
        'pageSize' => 20,
    ],
]);
Pjax::begin();
echo GridView::widget([
    'dataProvider' => $dataProvider,
    //'itemView' => '_post',
    'columns' => [
        'site.subdomain',
        [
            'attribute' => 'month',
            'format' => 'raw',
            'contentOptions' => ['class' => 'text-center'],
            'value' => function ($model) {
                if ($model->month == 12) {
                    return '1 год.';
                }
                return $model->month . ' мес.';
            }
        ],
        [
            'attribute' => 'price',
            'format' => 'raw',
            'contentOptions' => ['class' => 'text-center'],
            'value' => function ($model) {
                return number_format($model->price, 0, 2, 2) . ' UAH';
            }
        ],
        [
            'attribute' => 'created_at',
            'format' => 'raw',
            'contentOptions' => ['class' => 'text-center'],
            'value' => function ($model) {
                return \panix\engine\CMS::date($model->created_at);
            }
        ],
    ]
]);
Pjax::end();





