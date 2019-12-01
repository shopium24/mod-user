<?php
use panix\engine\Html;
use panix\engine\bootstrap\ActiveForm;
use panix\engine\CMS;
?>
<h1>Плата: <?= $site->plan->name; ?></h1>
<?php

$form = ActiveForm::begin([
    'id' => 'payment-form',
    'enableAjaxValidation' => true,
]); ?>


<?= $form->field($payment, 'month')->dropDownList([1 => '1 мес.',6 => '6 мес.',12 => '1 год']) ?>

    <div class="form-group text-center">

            <?= Html::submitButton(Yii::t('app', 'PAYMENT'), ['class' => 'btn btn-success']) ?>

    </div>

<?php ActiveForm::end(); ?>



<form method="POST" action="https://api.privatbank.ua/p24api/ishop">
    <input type="hidden" name="amt" value="500"/>
    <input type="hidden" name="ccy" value="UAH"/>
    <input type="hidden" name="merchant" value="152357"/>
    <input type="hidden" name="order" value="s_<?= CMS::gen(111); ?>"/>
    <input type="hidden" name="details" value="Оплата аренды на 1 мес."/>
    <input type="hidden" name="ext_details" value="Оптала тарифного плана Basic, на 1 месяц."/>
    <input type="hidden" name="return_url" value="https://shopium24.com/user/payment/result"/>
    <input type="hidden" name="server_url" value="https://shopium24.com/user/payment/process"/>
    <input type="hidden" name="pay_way" value="privat24"/>
    <button type="submit">payment</button>
</form>

