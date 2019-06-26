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
<p style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 14px; line-height: 1.6; font-weight: normal; margin: 0 0 10px; padding: 0;">
    <?= Yii::t('user', 'Hello') ?>,
</p>

<p style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 14px; line-height: 1.6; font-weight: normal; margin: 0 0 10px; padding: 0;">
    <?= Yii::t('user', 'Your account on {0} has a new password', Yii::$app->name) ?>.
    <?= Yii::t('user', 'We have generated a password for you') ?>: <strong><?= $user->password ?></strong>
</p>

<p style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; font-size: 14px; line-height: 1.6; font-weight: normal; margin: 0 0 10px; padding: 0;">
    <?= Yii::t('user', 'If you did not make this request you can ignore this email') ?>.
</p>