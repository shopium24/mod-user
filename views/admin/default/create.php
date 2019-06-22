<?php

use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var shopium24\mod\user\models\User $user
 */

$this->title = Yii::t('user/default', 'Create {modelClass}', [
  'modelClass' => 'User',
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('user/default', 'Users'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-create">
    <?= $this->render('_form', [
        'user' => $user,
    ]) ?>

</div>