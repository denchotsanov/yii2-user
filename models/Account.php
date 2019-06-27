<?php


namespace denchotsanov\user\models;

use denchotsanov\user\clients\ClientInterface;
use denchotsanov\user\Finder;
use denchotsanov\user\traits\ModuleTrait;
use Yii;
use yii\authclient\ClientInterface as BaseClientInterface;
use yii\db\ActiveRecord;
use yii\helpers\Json;
use yii\helpers\Url;
/**
 * @property integer $id
 * @property integer $user_id
 * @property string  $provider
 * @property string  $client_id
 * @property string  $data
 * @property string  $decodedData
 * @property string  $code
 * @property integer $created_at
 * @property string  $email
 * @property string  $username
 *
 * @property User    $user
 *
 */
class Account extends ActiveRecord
{
    use ModuleTrait;
    protected static $finder;
    private $_data;
    public static function tableName()
    {
        return '{{%social_account}}';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne($this->module->modelMap['User'], ['id' => 'user_id']);
    }
    /**
     * @return bool
     */
    public function getIsConnected()
    {
        return $this->user_id != null;
    }

    /**
     * @return mixed
     */
    public function getDecodedData()
    {
        if ($this->_data == null) {
            $this->_data = Json::decode($this->data);
        }
        return $this->_data;
    }

    /**
     * Returns connect url.
     * @return string
     * @throws \yii\base\Exception
     */
    public function getConnectUrl()
    {
        $code = Yii::$app->security->generateRandomString();
        $this->updateAttributes(['code' => md5($code)]);
        return Url::to(['/user/registration/connect', 'code' => $code]);
    }
    public function connect(User $user)
    {
        return $this->updateAttributes([
            'username' => null,
            'email'    => null,
            'code'     => null,
            'user_id'  => $user->id,
        ]);
    }

    /**
     * @return AccountQuery
     * @throws \yii\base\InvalidConfigException
     */
    public static function find()
    {
        return Yii::createObject(AccountQuery::class, [get_called_class()]);
    }

    /**
     * @param BaseClientInterface $client
     * @return Account|object
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public static function create(BaseClientInterface $client)
    {
        $account = Yii::createObject([
            'class'      => static::class,
            'provider'   => $client->getId(),
            'client_id'  => $client->getUserAttributes()['id'],
            'data'       => Json::encode($client->getUserAttributes()),
        ]);
        if ($client instanceof ClientInterface) {
            $account->setAttributes([
                'username' => $client->getUsername(),
                'email'    => $client->getEmail(),
            ], false);
        }
        if (($user = static::fetchUser($account)) instanceof User) {
            $account->user_id = $user->id;
        }
        $account->save(false);
        return $account;
    }

    /**
     * Tries to find an account and then connect that account with current user.
     *
     * @param BaseClientInterface $client
     * @throws \yii\base\InvalidConfigException
     */
    public static function connectWithUser(BaseClientInterface $client)
    {
        if (Yii::$app->user->isGuest) {
            Yii::$app->session->setFlash('danger', Yii::t('user', 'Something went wrong'));
            return;
        }
        $account = static::fetchAccount($client);
        if ($account->user === null) {
            $account->link('user', Yii::$app->user->identity);
            Yii::$app->session->setFlash('success', Yii::t('user', 'Your account has been connected'));
        } else {
            Yii::$app->session->setFlash(
                'danger',
                Yii::t('user', 'This account has already been connected to another user')
            );
        }
    }
    /**
     * Tries to find account, otherwise creates new account.
     *
     * @param BaseClientInterface $client
     *
     * @return Account
     * @throws \yii\base\InvalidConfigException
     */
    protected static function fetchAccount(BaseClientInterface $client)
    {
        $account = static::getFinder()->findAccount()->byClient($client)->one();
        if (null === $account) {
            $account = Yii::createObject([
                'class'      => static::class,
                'provider'   => $client->getId(),
                'client_id'  => $client->getUserAttributes()['id'],
                'data'       => Json::encode($client->getUserAttributes()),
            ]);
            $account->save(false);
        }
        return $account;
    }

    /**
     * Tries to find user or create a new one.
     *
     * @param Account $account
     *
     * @return User|bool False when can't create user.
     * @throws \yii\base\InvalidConfigException
     */
    protected static function fetchUser(Account $account)
    {
        $user = static::getFinder()->findUserByEmail($account->email);
        if (null !== $user) {
            return $user;
        }
        $user = Yii::createObject([
            'class'    => User::class,
            'scenario' => 'connect',
            'username' => $account->username,
            'email'    => $account->email,
        ]);
        if (!$user->validate(['email'])) {
            $account->email = null;
        }
        if (!$user->validate(['username'])) {
            $account->username = null;
        }
        return $user->create() ? $user : false;
    }

    /**
     * @return Finder
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    protected static function getFinder()
    {
        if (static::$finder === null) {
            static::$finder = Yii::$container->get(Finder::class);
        }
        return static::$finder;
    }
}