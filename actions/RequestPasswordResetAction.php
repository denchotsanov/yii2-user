<?php


namespace denchotsanov\user\actions;


use Yii;
use yii\web\Response;
use yii\widgets\ActiveForm;
/**
 * Class RequestPasswordResetAction
 */
class RequestPasswordResetAction extends Action
{
    /**
     * Event is triggered before requesting password reset.
     */
    const EVENT_BEFORE_REQUEST = 'beforeRequest';
    /**
     * Event is triggered after requesting password reset.
     */
    const EVENT_AFTER_REQUEST = 'afterRequest';
    /**
     * @var string name of the view, which should be rendered
     */
    public $view = '@vendor/denchotsanov/user/views/requestPasswordResetToken';
    /**
     * @var string password reset request form class
     */
    public $modelClass = 'denchotsanov\user\models\PasswordResetRequestForm';
    /**
     * @var string success message to the user when the mail is sent successfully
     */
    public $successMessage = 'Check your email for further instructions.';
    /**
     * @var string error message for the user when the email was not sent
     */
    public $errorMessage = 'Sorry, we are unable to reset password for email provided.';

    /**
     * Request password reset for a user.
     *
     * @return array|string|Response
     * @throws \yii\base\InvalidConfigException
     */
    public function run()
    {
        $model = Yii::createObject($this->modelClass);
        $load = $model->load(Yii::$app->request->post());
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }
        if ($load && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->getSession()->setFlash('success', $this->successMessage);
                return $this->redirectTo(Yii::$app->getHomeUrl());
            } else {
                Yii::$app->getSession()->setFlash('error', $this->errorMessage);
            }
        }
        return $this->controller->render($this->view, [
            'model' => $model,
        ]);
    }
}