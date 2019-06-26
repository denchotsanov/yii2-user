<?php
/* @var $this \yii\web\View */
/* @var $form static */
/* @var $user User */

use denchotsanov\user\models\User; ?>

<?= $form->field($user, 'email')->textInput(['maxlength' => 255]); ?>
<?= $form->field($user, 'username')->textInput(['maxlength' => 255]); ?>
<?= $form->field($user, 'password')->passwordInput(); ?>