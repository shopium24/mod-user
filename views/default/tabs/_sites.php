<?php

use panix\engine\data\ActiveDataProvider;
use shopium24\mod\user\models\Sites;
use yii\grid\GridView;
use panix\engine\widgets\Pjax;

$dataProvider = new ActiveDataProvider([
    'query' => Sites::find()->where(['user_id'=>$user->id]),
    'pagination' => [
        'pageSize' => 20,
    ],
]);
Pjax::begin();
echo GridView::widget([
    'dataProvider' => $dataProvider,
    //'itemView' => '_post',
    'columns'=>[
        'subdomain',
        'created_at',
        'plan.name'
    ]
]);
Pjax::end();
foreach ($user->sites as $site){
    echo $site->subdomain;
}
