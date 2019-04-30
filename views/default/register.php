<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

/**
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var shopium24\mod\user\models\User $user
 * @var string $userDisplayName
 */

$this->title = Yii::t('user/default', 'Register');
$this->params['breadcrumbs'][] = $this->title;
?>1112
<div class="row">
    <div class="col-md-4 offset-md-4">
        <h1><?= Html::encode($this->title) ?></h1>

        <?php if ($flash = Yii::$app->session->getFlash("Register-success")) { ?>

            <div class="alert alert-success"><?= $flash ?></div>

        <?php } else { ?>

            <p><?= Yii::t("user/default", "Please fill out the following fields to register:") ?></p>

            <?php $form = ActiveForm::begin([
                'id' => 'register-form',
                'options' => ['class' => 'form-horizontal'],
                'fieldClass'=>'panix\engine\bootstrap\ActiveField',
                'fieldConfig' => [
                    'template' => "{label}\n<div class=\"col-lg-3\">{input}</div>\n<div class=\"col-lg-7\">{error}</div>",
                    'labelOptions' => ['class' => 'col-lg-2 control-label'],
                ],
                'enableAjaxValidation' => true,
            ]); ?>


            <?= $form->field($user, 'email') ?>
            <?= $form->field($user, 'username') ?>
            <?= $form->field($user, 'newPassword')->passwordInput() ?>

            <?php /* uncomment if you want to add profile fields here
        <?= $form->field($profile, 'full_name') ?>
        */ ?>

            <div class="form-group row">
                <div class="col-lg-offset-2 col-lg-10">
                    <?= Html::submitButton(Yii::t('user/default', 'REGISTER'), ['class' => 'btn btn-primary']) ?>

                    <br/><br/>
                    <?= Html::a(Yii::t('user/default', 'Login'), ["/user/login"]) ?>
                </div>
            </div>

            <?php ActiveForm::end(); ?>

        <?php } ?>
    </div>
</div>