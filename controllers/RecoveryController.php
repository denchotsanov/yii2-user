<?php
namespace denchotsanov\user\controllers;

use denchotsanov\user\Finder;
use denchotsanov\user\models\RecoveryForm;
use denchotsanov\user\models\Token;
use denchotsanov\user\traits\AjaxValidationTrait;
use denchotsanov\user\traits\EventTrait;
use Yii;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;

class RecoveryController
{
    use AjaxValidationTrait;
    use EventTrait;

    const EVENT_BEFORE_REQUEST = 'beforeRequest';
    const EVENT_AFTER_REQUEST = 'afterRequest';
    const EVENT_BEFORE_TOKEN_VALIDATE = 'beforeTokenValidate';
    const EVENT_AFTER_TOKEN_VALIDATE = 'afterTokenValidate';
    const EVENT_BEFORE_RESET = 'beforeReset';
    const EVENT_AFTER_RESET = 'afterReset';

    /** @var Finder */
    protected $finder;
    /**
     * @param string           $id
     * @param yii\base\Module $module
     * @param Finder           $finder
     * @param array            $config
     */
    public function __construct($id, $module, Finder $finder, $config = [])
    {
        $this->finder = $finder;
        parent::__construct($id, $module, $config);
    }
    /** @inheritdoc */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'actions' => ['request', 'reset'], 'roles' => ['?']],
                ],
            ],
        ];
    }

    /**
     * @return string
     * @throws \yii\web\NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws Yii\base\ExitException
     */
    public function actionRequest()
    {
        if (!$this->module->enablePasswordRecovery) {
            throw new NotFoundHttpException();
        }
        /** @var RecoveryForm $model */
        $model = \Yii::createObject([
            'class'    => RecoveryForm::class,
            'scenario' => RecoveryForm::SCENARIO_REQUEST,
        ]);
        $event = $this->getFormEvent($model);
        $this->performAjaxValidation($model);
        $this->trigger(self::EVENT_BEFORE_REQUEST, $event);
        if ($model->load(\Yii::$app->request->post()) && $model->sendRecoveryMessage()) {
            $this->trigger(self::EVENT_AFTER_REQUEST, $event);
            return $this->render('/message', [
                'title'  => \Yii::t('user', 'Recovery message sent'),
                'module' => $this->module,
            ]);
        }
        return $this->render('request', [
            'model' => $model,
        ]);
    }

    /**
     * @param int $id
     * @param string $code
     *
     * @return string
     * @throws NotFoundHttpException
     * @throws Yii\base\Exception
     * @throws Yii\base\ExitException
     * @throws Yii\db\StaleObjectException
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     */
    public function actionReset($id, $code)
    {
        if (!$this->module->enablePasswordRecovery) {
            throw new NotFoundHttpException();
        }
        /** @var Token $token */
        $token = $this->finder->findToken(['user_id' => $id, 'code' => $code, 'type' => Token::TYPE_RECOVERY])->one();
        if (empty($token) || ! $token instanceof Token) {
            throw new NotFoundHttpException();
        }
        $event = $this->getResetPasswordEvent($token);
        $this->trigger(self::EVENT_BEFORE_TOKEN_VALIDATE, $event);
        if ($token === null || $token->isExpired || $token->user === null) {
            $this->trigger(self::EVENT_AFTER_TOKEN_VALIDATE, $event);
            \Yii::$app->session->setFlash(
                'danger',
                \Yii::t('user', 'Recovery link is invalid or expired. Please try requesting a new one.')
            );
            return $this->render('/message', [
                'title'  => \Yii::t('user', 'Invalid or expired link'),
                'module' => $this->module,
            ]);
        }
        /** @var RecoveryForm $model */
        $model = \Yii::createObject([
            'class'    => RecoveryForm::class,
            'scenario' => RecoveryForm::SCENARIO_RESET,
        ]);
        $event->setForm($model);
        $this->performAjaxValidation($model);
        $this->trigger(self::EVENT_BEFORE_RESET, $event);
        if ($model->load(\Yii::$app->getRequest()->post()) && $model->resetPassword($token)) {
            $this->trigger(self::EVENT_AFTER_RESET, $event);
            return $this->render('/message', [ 'title'  => Yii::t('user', 'Password has been changed'), 'module' => $this->module, ]);
        }
        return $this->render('reset', [ 'model' => $model,]);
    }
}