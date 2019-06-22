<?php
use yii\grid\GridView;

/**
 * @var \shopium24\mod\user\models\search\SitesSearch $searchModel
 * @var \panix\engine\data\ActiveDataProvider $dataProvider
 */

?>
<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <?php
            echo GridView::widget([
                'tableOptions' => ['class' => 'table table-striped'],
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    'subdomain',
                    'plan.name',
                    [
                        'class' => 'panix\engine\grid\columns\ActionColumn',
                        'template' => '{view}'
                    ]
                ],
            ]);
            ?>
        </div>
    </div>
</div>
