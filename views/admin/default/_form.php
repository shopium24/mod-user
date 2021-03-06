<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;


/**
 * @var yii\web\View $this
 * @var shopium24\mod\user\models\User $user
 * @var yii\widgets\ActiveForm $form
 */
?>



    <?php
    $form = ActiveForm::begin([
                'layout' => 'horizontal',
                'fieldConfig' => [
                    'horizontalCssClasses' => [
                        'label' => 'col-sm-4',
                        'offset' => 'col-sm-offset-4',
                        'wrapper' => 'col-sm-8',
                        'error' => '',
                        'hint' => '',
                    ],
                ],
                'options' => ['class' => 'form-horizontal']
    ]);
    ?>

    <?= $form->field($user, 'email')->textInput(['maxlength' => 255]) ?>

    <?= $form->field($user, 'username')->textInput(['maxlength' => 255]) ?>

    <?= $form->field($user, 'newPassword')->passwordInput() ?>

    <?= $form->field($user, 'status')->dropDownList($user::statusDropdown()); ?>

    <?php // use checkbox for ban_time ?>
    <?php // convert `ban_time` to int so that the checkbox gets set properly ?>
    <?php $user->ban_time = $user->ban_time ? 1 : 0 ?>
    <?= Html::activeLabel($user, 'ban_time', ['label' => Yii::t('user/default', 'Banned')]); ?>
    <?= Html::activeCheckbox($user, 'ban_time'); ?>
    <?= Html::error($user, 'ban_time'); ?>

    <?= $form->field($user, 'ban_reason'); ?>

    <div class="form-group text-center">
        <?= Html::submitButton($user->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $user->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
