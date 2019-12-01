<?php

use yii\helpers\Html;


/**
 * @var \panix\mod\plugins\components\View $this
 * @var yii\widgets\ActiveForm $form
 * @var \shopium24\mod\user\models\User $user
 */

?>

<div class="row">
    <div class="col-sm-12">


        <h1><?= Html::encode($this->context->pageName) ?></h1>

        <?php if ($flash = Yii::$app->session->getFlash("profile-success")): ?>

            <div class="alert alert-success">
                <p><?= $flash ?></p>
            </div>

        <?php endif; ?>

        <?php
        echo \panix\engine\bootstrap\Tabs::widget([
            'items' => [
                [
                    'label' => 'Профиль',
                    'content' => $this->render('tabs/_profile', ['user' => $user]),
                    'active' => true
                ],
                [
                    'label' => 'Мои сайты',
                    'content' => $this->render('tabs/_sites', ['user' => $user]),
                    'headerOptions' => [],
                    'options' => ['id' => 'sites'],
                ],
                [
                    'label' => 'Платежи',
                    'content' => $this->render('tabs/_payments', ['user' => $user]),
                    'headerOptions' => [],
                    'options' => ['id' => 'payments'],
                ],
                [
                    'label' => 'Dropdown',
                    'items' => [
                        [
                            'label' => 'DropdownA',
                            'content' => 'DropdownA, Anim pariatur cliche...',
                        ],
                        [
                            'label' => 'DropdownB',
                            'content' => 'DropdownB, Anim pariatur cliche...',
                        ],
                        [
                            'label' => 'External Link',
                            'url' => 'http://www.example.com',
                        ],
                    ],
                ],
            ],
        ]);
        ?>


    </div>
</div>


