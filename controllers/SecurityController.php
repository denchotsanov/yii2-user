<?php
/**
 * User: dencho
 */

namespace denchotsanov\user\controllers;


use denchotsanov\user\Finder;
use denchotsanov\user\models\Account;
use denchotsanov\user\models\LoginForm;
use denchotsanov\user\Module;
use denchotsanov\user\traits\AjaxValidationTrait;
use denchotsanov\user\traits\EventTrait;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\web\Controller;
use yii\authclient\AuthAction;
use yii\authclient\ClientInterface;
use yii\web\Response;

class SecurityController extends Controller
{
    use AjaxValidationTrait;
    use EventTrait;

    const EVENT_BEFORE_LOGIN = 'beforeLogin';
    const EVENT_AFTER_LOGIN = 'afterLogin';
    const EVENT_BEFORE_LOGOUT = 'beforeLogout';
    const EVENT_AFTER_LOGOUT = 'afterLogout';
    const EVENT_BEFORE_AUTHENTICATE = 'beforeAuthenticate';
    const EVENT_AFTER_AUTHENTICATE = 'afterAuthenticate';
    const EVENT_BEFORE_CONNECT = 'beforeConnect';
    const EVENT_AFTER_CONNECT = 'afterConnect';

    /** @var Finder */
    protected $finder;
    /**
     * @param string $id
     * @param Module $module
     * @param Finder $finder
     * @param array  $config
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
                    ['allow' => true, 'actions' => ['login', 'auth'], 'roles' => ['?']],
                    ['allow' => true, 'actions' => ['login', 'auth', 'logout'], 'roles' => ['@']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }
    /** @inheritdoc */
    public function actions()
    {
        return [
            'auth' => [
                'class' => AuthAction::class,
                // if user is not logged in, will try to log him in, otherwise
                // will try to connect social account to user.
                'successCallback' => \Yii::$app->user->isGuest
                    ? [$this, 'authenticate']
                    : [$this, 'connect'],
            ],
        ];
    }

    /**
     * @return string|Response
     * @throws \yii\base\InvalidConfigException
     */
    public function actionLogin()
    {
        if (!\Yii::$app->user->isGuest) {
            $this->goHome();
        }
        /** @var LoginForm $model */
        $model = \Yii::createObject(LoginForm::class);
        $event = $this->getFormEvent($model);
        $this->performAjaxValidation($model);
        $this->trigger(self::EVENT_BEFORE_LOGIN, $event);
        if ($model->load(\Yii::$app->getRequest()->post()) && $model->login()) {
            $this->trigger(self::EVENT_AFTER_LOGIN, $event);
            return $this->goBack();
        }
        return $this->render('login', [
            'model'  => $model,
            'module' => $this->module,
        ]);
    }
    /**
     * Logs the user out and then redirects to the homepage.
     *
     * @return Response
     */
    public function actionLogout()
    {
        $event = $this->getUserEvent(\Yii::$app->user->identity);
        $this->trigger(self::EVENT_BEFORE_LOGOUT, $event);
        \Yii::$app->getUser()->logout();
        $this->trigger(self::EVENT_AFTER_LOGOUT, $event);
        return $this->goHome();
    }

    /**
     * @param ClientInterface $client
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\base\Exception
     */
    public function authenticate(ClientInterface $client)
    {
        $account = $this->finder->findAccount()->byClient($client)->one();
        if (!$this->module->enableRegistration && ($account === null || $account->user === null)) {
            \Yii::$app->session->setFlash('danger', \Yii::t('user', 'Registration on this website is disabled'));
            $this->action->successUrl = Url::to(['/user/security/login']);
            return;
        }
        if ($account === null) {
            /** @var Account $account */
            $accountObj = \Yii::createObject(Account::class);
            $account = $accountObj::create($client);
        }
        $event = $this->getAuthEvent($account, $client);
        $this->trigger(self::EVENT_BEFORE_AUTHENTICATE, $event);
        if ($account->user instanceof User) {
            if ($account->user->isBlocked) {
                \Yii::$app->session->setFlash('danger', \Yii::t('user', 'Your account has been blocked.'));
                $this->action->successUrl = Url::to(['/user/security/login']);
            } else {
                $account->user->updateAttributes(['last_login_at' => time()]);
                \Yii::$app->user->login($account->user, $this->module->rememberFor);
                $this->action->successUrl = \Yii::$app->getUser()->getReturnUrl();
            }
        } else {
            $this->action->successUrl = $account->getConnectUrl();
        }
        $this->trigger(self::EVENT_AFTER_AUTHENTICATE, $event);
    }

    /**
     * @param ClientInterface $client
     * @throws \yii\base\InvalidConfigException
     */
    public function connect(ClientInterface $client)
    {
        /** @var Account $account */
        $account = Yii::createObject(Account::class);
        $event   = $this->getAuthEvent($account, $client);
        $this->trigger(self::EVENT_BEFORE_CONNECT, $event);
        $account->connectWithUser($client);
        $this->trigger(self::EVENT_AFTER_CONNECT, $event);
        $this->action->successUrl = Url::to(['/user/settings/networks']);
    }
}