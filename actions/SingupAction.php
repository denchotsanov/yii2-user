<?php
namespace denchotsanov\user\actions;

use Yii;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * Class SignupAction
 *
 */
class SingupAction extends Action
{

    /**
     * Event is triggered after creating SignupForm class.
     */
    const EVENT_BEFORE_SIGNUP = 'beforeSignup';
    /**
     * Event is triggered after successful signup.
     */
    const EVENT_AFTER_SIGNUP = 'afterSignup';
    /**
     * @var string name of the view, which should be rendered
     */
    public $view = '@vendor/denchotsanov/yii2-user/views/signup';
    /**
     * @var string signup form class
     */
    public $modelClass = 'denchotsanov\user\models\SignupForm';

    /**
     * Signup a user.
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
        if ($load && ($user = $model->signup()) !== null) {
            if (Yii::$app->getUser()->login($user)) {
                return $this->redirectTo(Yii::$app->getUser()->getReturnUrl());
            }
        }
        return $this->controller->render($this->view, [
            'model' => $model,
        ]);
    }
}