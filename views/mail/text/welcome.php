<?php

use denchotsanov\user\models\Token;
use denchotsanov\user\models\User;
use denchotsanov\user\Module;
use yii\helpers\Html;
/**
 * @var Module $module
 * @var User $user
 * @var Token $token
 * @var bool $showPassword
 */
?>
<?= Yii::t('user', 'Hello') ?>,

<?= Yii::t('user', 'Your account on {0} has been created', Yii::$app->name) ?>.
<?php if ($module->enableGeneratingPassword): ?>
    <?= Yii::t('user', 'We have generated a password for you') ?>:
    <?= $user->password ?>
<?php endif ?>

<?php if ($token !== null): ?>
    <?= Yii::t('user', 'In order to complete your registration, please click the link below') ?>.

    <?= $token->url ?>

    <?= Yii::t('user', 'If you cannot click the link, please try pasting the text into your browser') ?>.
<?php endif ?>

<?= Yii::t('user', 'If you did not make this request you can ignore this email') ?>.