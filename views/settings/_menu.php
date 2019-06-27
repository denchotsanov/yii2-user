<?php

use denchotsanov\user\models\User;
use yii\helpers\Html;
use denchotsanov\user\widgets\UserMenu;
/**
 * @var User $user
 */
$user = Yii::$app->user->identity;
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">
            <?= Html::img($user->profile->getAvatarUrl(24), [
                'class' => 'img-rounded',
                'alt' => $user->username,
            ]) ?>
            <?= $user->username ?>
        </h3>
    </div>
    <div class="panel-body">
        <?php echo UserMenu::widget() ?>
    </div>
</div>
