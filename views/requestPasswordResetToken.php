<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $model \denchotsanov\user\models\PasswordResetRequestForm */

$this->title = Yii::t('denchotsanov.user', 'Recover Password');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-request-password-reset">
    <h1><?php echo Html::encode($this->title) ?></h1>

    <p><?php echo Yii::t('denchotsanov.user', 'Please fill out your email. A link to reset password will be sent there.'); ?></p>

    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(['id' => 'request-password-reset-form']); ?>
            <?php echo $form->field($model, 'email'); ?>
            <div class="form-group">
                <?php echo Html::submitButton(Yii::t('denchotsanov.user', 'Send'), ['class' => 'btn btn-primary']) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>