<?php
namespace denchotsanov\user\controllers;

use denchotsanov\user\filters\AccessRule;
use denchotsanov\user\Finder;
use denchotsanov\user\models\Profile;
use denchotsanov\user\models\User;
use denchotsanov\user\models\UserSearch;
use denchotsanov\user\Module;
use denchotsanov\user\traits\EventTrait;
use yii;
use yii\base\ExitException;
use yii\base\Model;
use yii\base\Module as Module2;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * @property Module $module
 */
class AdminController extends Controller
{
    use EventTrait;
    const EVENT_BEFORE_CREATE = 'beforeCreate';
    const EVENT_AFTER_CREATE = 'afterCreate';
    const EVENT_BEFORE_UPDATE = 'beforeUpdate';
    const EVENT_AFTER_UPDATE = 'afterUpdate';
    const EVENT_BEFORE_IMPERSONATE = 'beforeImpersonate';
    const EVENT_AFTER_IMPERSONATE = 'afterImpersonate';
    const EVENT_BEFORE_PROFILE_UPDATE = 'beforeProfileUpdate';
    const EVENT_AFTER_PROFILE_UPDATE = 'afterProfileUpdate';
    const EVENT_BEFORE_CONFIRM = 'beforeConfirm';
    const EVENT_AFTER_CONFIRM = 'afterConfirm';
    const EVENT_BEFORE_DELETE = 'beforeDelete';
    const EVENT_AFTER_DELETE = 'afterDelete';
    const EVENT_BEFORE_BLOCK = 'beforeBlock';
    const EVENT_AFTER_BLOCK = 'afterBlock';
    const EVENT_BEFORE_UNBLOCK = 'beforeUnblock';
    const EVENT_AFTER_UNBLOCK = 'afterUnblock';
    const ORIGINAL_USER_SESSION_KEY = 'original_user';
    protected $finder;
    /**
     * @param string  $id
     * @param Module2 $module
     * @param Finder  $finder
     * @param array   $config
     */
    public function __construct($id, $module, Finder $finder, $config = [])
    {
        $this->finder = $finder;
        parent::__construct($id, $module, $config);
    }
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete'          => ['post'],
                    'confirm'         => ['post'],
                    'resend-password' => ['post'],
                    'block'           => ['post'],
                    'switch'          => ['post'],
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'ruleConfig' => [
                    'class' => AccessRule::class,
                ],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['switch'],
                        'roles' => ['@'],
                    ],
                    [
                        'allow' => true,
                        'roles' => ['admin'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return mixed
     * @throws yii\base\InvalidConfigException
     */
    public function actionIndex()
    {
        Url::remember('', 'actions-redirect');
        $searchModel  = \Yii::createObject(UserSearch::class);
        $dataProvider = $searchModel->search(\Yii::$app->request->get());
        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel'  => $searchModel,
        ]);
    }

    /**
     * @return mixed
     * @throws ExitException
     * @throws yii\base\InvalidConfigException
     */
    public function actionCreate()
    {
        /** @var User $user */
        $user = \Yii::createObject([
            'class'    => User::class,
            'scenario' => 'create',
        ]);
        $event = $this->getUserEvent($user);
        $this->performAjaxValidation($user);
        $this->trigger(self::EVENT_BEFORE_CREATE, $event);
        if ($user->load(\Yii::$app->request->post()) && $user->create()) {
            \Yii::$app->getSession()->setFlash('success', \Yii::t('user', 'User has been created'));
            $this->trigger(self::EVENT_AFTER_CREATE, $event);
            return $this->redirect(['update', 'id' => $user->id]);
        }
        return $this->render('create', [
            'user' => $user,
        ]);
    }

    /**
     * @param int $id
     *
     * @return mixed
     *
     * @throws ExitException
     * @throws NotFoundHttpException
     * @throws yii\base\InvalidConfigException
     */
    public function actionUpdate($id)
    {
        Url::remember('', 'actions-redirect');
        $user = $this->findModel($id);
        $user->scenario = 'update';
        $event = $this->getUserEvent($user);
        $this->performAjaxValidation($user);
        $this->trigger(self::EVENT_BEFORE_UPDATE, $event);
        if ($user->load(\Yii::$app->request->post()) && $user->save()) {
            \Yii::$app->getSession()->setFlash('success', \Yii::t('user', 'Account details have been updated'));
            $this->trigger(self::EVENT_AFTER_UPDATE, $event);
            return $this->refresh();
        }
        return $this->render('_account', [
            'user' => $user,
        ]);
    }

    /**
     * @param int $id
     *
     * @return mixed
     * @throws ExitException
     * @throws NotFoundHttpException
     * @throws yii\base\InvalidConfigException
     */
    public function actionUpdateProfile($id)
    {
        Url::remember('', 'actions-redirect');
        $user    = $this->findModel($id);
        $profile = $user->profile;
        if ($profile == null) {
            $profile = \Yii::createObject(Profile::class);
            $profile->link('user', $user);
        }
        $event = $this->getProfileEvent($profile);
        $this->performAjaxValidation($profile);
        $this->trigger(self::EVENT_BEFORE_PROFILE_UPDATE, $event);
        if ($profile->load(\Yii::$app->request->post()) && $profile->save()) {
            \Yii::$app->getSession()->setFlash('success', \Yii::t('user', 'Profile details have been updated'));
            $this->trigger(self::EVENT_AFTER_PROFILE_UPDATE, $event);
            return $this->refresh();
        }
        return $this->render('_profile', [
            'user'    => $user,
            'profile' => $profile,
        ]);
    }

    /**
     * @param int $id
     *
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionInfo($id)
    {
        Url::remember('', 'actions-redirect');
        $user = $this->findModel($id);
        return $this->render('_info', [
            'user' => $user,
        ]);
    }

    /**
     * @param int $id
     *
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws yii\base\InvalidConfigException
     */
    public function actionSwitch($id = null)
    {
        if (!$this->module->enableImpersonateUser) {
            throw new ForbiddenHttpException(Yii::t('user', 'Impersonate user is disabled in the application configuration'));
        }
        if(!$id && Yii::$app->session->has(self::ORIGINAL_USER_SESSION_KEY)) {
            $user = $this->findModel(Yii::$app->session->get(self::ORIGINAL_USER_SESSION_KEY));
            Yii::$app->session->remove(self::ORIGINAL_USER_SESSION_KEY);
        } else {
            if (!Yii::$app->user->identity->isAdmin) {
                throw new ForbiddenHttpException;
            }
            $user = $this->findModel($id);
            Yii::$app->session->set(self::ORIGINAL_USER_SESSION_KEY, Yii::$app->user->id);
        }
        $event = $this->getUserEvent($user);
        $this->trigger(self::EVENT_BEFORE_IMPERSONATE, $event);

        Yii::$app->user->switchIdentity($user, 3600);

        $this->trigger(self::EVENT_AFTER_IMPERSONATE, $event);
        return $this->goHome();
    }
    /**
     * @param int $id
     *
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionAssignments($id)
    {
        if (!isset(\Yii::$app->extensions['denchotsanov/yii2-user-rbac'])) {
            throw new NotFoundHttpException();
        }
        Url::remember('', 'actions-redirect');
        $user = $this->findModel($id);
        return $this->render('_assignments', [
            'user' => $user,
        ]);
    }

    /**
     * @param int $id
     *
     * @return Response
     * @throws NotFoundHttpException
     * @throws yii\base\InvalidConfigException
     */
    public function actionConfirm($id)
    {
        $model = $this->findModel($id);
        $event = $this->getUserEvent($model);
        $this->trigger(self::EVENT_BEFORE_CONFIRM, $event);
        $model->confirm();
        $this->trigger(self::EVENT_AFTER_CONFIRM, $event);
        \Yii::$app->getSession()->setFlash('success', \Yii::t('user', 'User has been confirmed'));
        return $this->redirect(Url::previous('actions-redirect'));
    }

    /**
     * @param int $id
     *
     * @return mixed
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        if ($id == \Yii::$app->user->getId()) {
            \Yii::$app->getSession()->setFlash('danger', \Yii::t('user', 'You can not remove your own account'));
        } else {
            $model = $this->findModel($id);
            $event = $this->getUserEvent($model);
            $this->trigger(self::EVENT_BEFORE_DELETE, $event);
            $model->delete();
            $this->trigger(self::EVENT_AFTER_DELETE, $event);
            \Yii::$app->getSession()->setFlash('success', \Yii::t('user', 'User has been deleted'));
        }
        return $this->redirect(['index']);
    }

    /**
     * @param int $id
     *
     * @return Response
     * @throws NotFoundHttpException
     * @throws yii\base\InvalidConfigException
     * @throws yii\base\Exception
     */
    public function actionBlock($id)
    {
        if ($id == \Yii::$app->user->getId()) {
            \Yii::$app->getSession()->setFlash('danger', \Yii::t('user', 'You can not block your own account'));
        } else {
            $user  = $this->findModel($id);
            $event = $this->getUserEvent($user);
            if ($user->getIsBlocked()) {
                $this->trigger(self::EVENT_BEFORE_UNBLOCK, $event);
                $user->unblock();
                $this->trigger(self::EVENT_AFTER_UNBLOCK, $event);
                \Yii::$app->getSession()->setFlash('success', \Yii::t('user', 'User has been unblocked'));
            } else {
                $this->trigger(self::EVENT_BEFORE_BLOCK, $event);
                $user->block();
                $this->trigger(self::EVENT_AFTER_BLOCK, $event);
                \Yii::$app->getSession()->setFlash('success', \Yii::t('user', 'User has been blocked'));
            }
        }
        return $this->redirect(Url::previous('actions-redirect'));
    }

    /**
     * @param $id
     * @return Response
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws yii\base\InvalidConfigException
     */
    public function actionResendPassword($id)
    {
        $user = $this->findModel($id);
        if ($user->isAdmin) {
            throw new ForbiddenHttpException(Yii::t('user', 'Password generation is not possible for admin users'));
        }
        if ($user->resendPassword()) {
            Yii::$app->session->setFlash('success', \Yii::t('user', 'New Password has been generated and sent to user'));
        } else {
            Yii::$app->session->setFlash('danger', \Yii::t('user', 'Error while trying to generate new password'));
        }
        return $this->redirect(Url::previous('actions-redirect'));
    }
    /**
     * @param int $id
     *
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $user = $this->finder->findUserById($id);
        if ($user === null) {
            throw new NotFoundHttpException('The requested page does not exist');
        }
        return $user;
    }
    /**
     * Performs AJAX validation.
     *
     * @param array|Model $model
     *
     * @throws ExitException
     */
    protected function performAjaxValidation($model)
    {
        if (Yii::$app->request->isAjax && !Yii::$app->request->isPjax) {
            if ($model->load(Yii::$app->request->post())) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                Yii::$app->response->data = json_encode(ActiveForm::validate($model));
                Yii::$app->end();
            }
        }
    }
}