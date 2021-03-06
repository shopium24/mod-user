<?php

use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var shopium24\mod\user\models\User $user
 */
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><?= Html::encode($this->context->pageName) ?></h3>
    </div>
    <div class="panel-body">
        <?=
        $this->render('_form', [
            'user' => $user,
        ])
        ?>
    </div>
</div>
