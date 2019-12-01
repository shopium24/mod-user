<?php
use yii\grid\GridView;
use yii\helpers\Url;
use panix\engine\widgets\Pjax;
use panix\engine\CMS;
use panix\engine\Html;
use panix\engine\data\ActiveDataProvider;
use shopium24\mod\user\models\Sites;

$dataProvider = new ActiveDataProvider([
    'query' => Sites::find()->where(['user_id' => $user->id]),
    'pagination' => [
        'pageSize' => 20,
    ],
]);
Pjax::begin();
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'attribute' => 'subdomain',
            'format' => 'raw',
            'contentOptions' => ['class' => 'text-center'],
            'value' => function ($model) {
                return Html::a($model->subdomain . '.' . Yii::$app->params['domain'], Url::to('//:'.$model->subdomain . '.' . Yii::$app->params['domain'], true), ['target' => '_blank']);
            }
        ],
        [
            'attribute' => 'plan_id',
            'contentOptions' => ['class' => 'text-center'],
            'value' => function ($model) {
                return $model->plan->name;
            }
        ],
        [
            'attribute' => 'created_at',
            'contentOptions' => ['class' => 'text-center'],
            'value' => function ($model) {
                return CMS::date($model->created_at, false);
            }
        ],
        [
            'attribute' => 'expire',
            'contentOptions' => ['class' => 'text-center'],
            'value' => function ($model) {
                return CMS::date($model->expire);
            }
        ],
        [
            'class' => \panix\engine\grid\columns\ActionColumn::class,
            'template'=>'{payment}',

            'buttons'=>[
                'payment'=>function ($url, $model) {

                    return Html::a( 'Оплатить', ['/user/payment','site_id'=>$model->id], [
                        'title' => Yii::t('yii', 'View'),
                        'class'=>'btn btn-sm btn-outline-success',
                        'data-pjax' => '0'
                    ]);
                }
            ],
        ]
    ]
]);
Pjax::end();

