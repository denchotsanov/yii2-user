<?php


namespace denchotsanov\user\actions;

use Yii;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;

/**
 * Class PasswordResetAction
 */
class PasswordResetAction extends Action
{

    /**
     * Event is triggered before resetting password.
     */
    const EVENT_BEFORE_RESET = 'beforeReset';
    /**
     * Event is triggered after resetting password.
     */
    const EVENT_AFTER_RESET = 'afterReset';
    /**
     * @var string name of the view, which should be rendered
     */
    public $view = '@vendor/denchotsanov/user/views/resetPassword';
    /**
     * @var string reset password model class
     */
    public $modelClass = 'denchotsanov\user\models\ResetPasswordForm';
    /**
     * @var string message to be set on success
     */
    public $successMessage = 'New password was saved.';

    /**
     * Reset password for a user.
     *
     * @param $token
     *
     * @return string|\yii\web\Response
     *
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function run($token)
    {
        try {
            $model = Yii::createObject($this->modelClass, [$token]);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->getSession()->setFlash('success', $this->successMessage);
            return $this->redirectTo(Yii::$app->getHomeUrl());
        }
        return $this->controller->render($this->view, [
            'model' => $model,
        ]);
    }
}