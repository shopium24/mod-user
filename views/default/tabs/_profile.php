<?php
use panix\engine\Html;
use panix\engine\bootstrap\ActiveForm;

$form = ActiveForm::begin([
    'id' => 'profile-form',
    'enableAjaxValidation' => true,
]); ?>


<?= $form->field($user, 'phone') ?>

<div class="form-group">
    <div class="col-lg-offset-2 col-lg-10">
        <?= Html::submitButton(Yii::t('app', 'SAVE'), ['class' => 'btn btn-success']) ?>
    </div>
</div>

<?php ActiveForm::end(); ?>

