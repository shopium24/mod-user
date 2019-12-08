<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var shopium24\mod\user\models\User $user
 * @var shopium24\mod\user\models\Sites $site
 * @var string $userDisplayName
 */

$this->title = Yii::t('user/default', 'REGISTER');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container">
    <div class="row">
        <div class="col-md-6 offset-md-2">
            <h1><?= Html::encode($this->title) ?></h1>

            <?php if ($flash = Yii::$app->session->getFlash("Register-success")) { ?>

                <div class="alert alert-success"><?= $flash ?></div>

            <?php } else { ?>

                <p><?= Yii::t("user/default", "Please fill out the following fields to register:") ?></p>

                <?php $form = ActiveForm::begin([
                    'id' => 'register-form',
                    'enableAjaxValidation' => true,
                ]);

                ?>


                <?= $form->field($user, 'username')->label($user->getAttributeLabel('email')) ?>
                <?= $form->field($site, 'subdomain', [
                'template' => '{label}
<div class="input-group">
  {input}
    <div class="input-group-append">
    <span class="input-group-text">.shopium24.com</span>
  </div>
</div>
{error}{hint}']) ?>

                <?= $form->field($site, 'plan_id', [
                    'template' => '{label}
<div class="input-group">
  {input}
    <div class="input-group-append">
    <span class="input-group-text">14 дней бесплатно</span>
  </div>
</div>
{error}{hint}'])->dropDownList(\yii\helpers\ArrayHelper::map(\shopium24\mod\plans\models\Plans::find()->all(), 'id', function ($model) {
                    return $model['name'] . ' (' . number_format($model['price_month'], 0, '', '') . ' грн/мес.)';
                    // return $model['name'] . ' (14 дней бесплатно)';
                })); ?>
                <?= $form->field($user, 'password')->passwordInput() ?>
                <?= $form->field($user, 'password_confirm')->passwordInput() ?>

                <?php
                if (Yii::$app->settings->get('app', 'captcha_class') && Yii::$app->user->isGuest) { ?>
                    <?php
                    $captchaConfig = \panix\mod\admin\models\SettingsForm::captchaConfig();
                    echo $form->field($user, 'verifyCode')->widget(Yii::$app->settings->get('app', 'captcha_class'), $captchaConfig[Yii::$app->settings->get('app', 'captcha_class')]);
                }
                ?>
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
</div>