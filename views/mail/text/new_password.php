<?php
use denchotsanov\user\helpers\Password;
use denchotsanov\user\models\User;
use denchotsanov\user\Module;
/**
 * @var Module          $module
 * @var User     $user
 * @var Password $password
 */
?>
<?= Yii::t('user', 'Hello') ?>,
<?= Yii::t('user', 'Your account on {0} has a new password', Yii::$app->name) ?>.
<?= Yii::t('user', 'We have generated a password for you') ?>:
<?= $user->password ?>
<?= Yii::t('user', 'If you did not make this request you can ignore this email') ?>.