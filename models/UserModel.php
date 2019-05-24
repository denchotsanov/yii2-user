<?php
namespace denchotsanov\user\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\web\IdentityInterface;
use denchotsanov\user\models\enums\UserStatus;

/**
 * Class UserModel
 *
 * @property int $id
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property string $auth_key
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 * @property int $last_login
 * @property string $password write-only password
 */
class UserModel extends ActiveRecord implements IdentityInterface
{
    /**
     * Event is triggered before creating a user.
     */
    const BEFORE_CREATE = 'beforeCreate';
    /**
     * Event is triggered after creating a user.
     */
    const AFTER_CREATE = 'afterCreate';
    /**
     * @var string plain password
     */
    public $plainPassword;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user}}';
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['email'], 'required'],
            ['email', 'unique', 'message' => Yii::t('denchotsanov.user', 'This email address has already been taken.')],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['plainPassword', 'string', 'min' => 6],
            ['plainPassword', 'required', 'on' => 'create'],
            ['status', 'default', 'value' => UserStatus::STATUS_PENDING],
            ['status', 'in', 'range' => UserStatus::$list],
        ];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'email' => Yii::t('denchotsanov.user', 'Email'),
            'status' => Yii::t('denchotsanov.user', 'Status'),
            'created_at' => Yii::t('denchotsanov.user', 'Registration time'),
            'last_login' => Yii::t('denchotsanov.user', 'Last login'),
            'plainPassword' => Yii::t('denchotsanov.user', 'Password'),
        ];
    }
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
                'value' => time(),
            ],
        ];
    }
    /**
     * Create user
     *
     * @return null|UserModel the saved model or null if saving fails
     *
     * @throws \Exception
     */
    public function create()
    {
        $transaction = $this->getDb()->beginTransaction();
        try {
            $this->setPassword($this->plainPassword);
            $this->generateAuthKey();
            if (!$this->save()) {
                $transaction->rollBack();
                return null;
            }
            $transaction->commit();
            return $this;
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::warning($e->getMessage());
            throw $e;
        }
    }
    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }
    /**
     * @inheritdoc
     * @throws NotSupportedException
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }
    /**
     * Finds user (with active status) by e-mail
     *
     * @param  string $email
     *
     * @return static|null
     */
    public static function findActiveByEmail($email)
    {
        return static::findOne(['email' => $email, 'status' => UserStatus::STATUS_ACTIVE]);
    }
    /**
     * Finds user by email
     *
     * @param $email
     *
     * @return null|static
     */
    public static function findByEmail($email)
    {
        return static::findOne(['email' => $email]);
    }
    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     *
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }
        return static::findOne([
            'password_reset_token' => $token,
            'status' => UserStatus::STATUS_ACTIVE,
        ]);
    }
    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     *
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }
        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = ArrayHelper::getValue(Yii::$app->params, 'user.passwordResetTokenExpire', 3600);
        return $timestamp + $expire >= time();
    }
    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }
    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }
    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }
    /**
     * Validates password
     *
     * @param  string $password password to validate
     *
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->getSecurity()->validatePassword($password, $this->password_hash);
    }
    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     * @throws \yii\base\Exception
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->getSecurity()->generatePasswordHash($password);
    }
    /**
     * Generates "remember me" authentication key
     * @throws \yii\base\Exception
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->getSecurity()->generateRandomString();
    }
    /**
     * Generates new password reset token
     * @throws \yii\base\Exception
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->getSecurity()->generateRandomString() . '_' . time();
    }
    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }
    /**
     * @param $lastLogin
     */
    public function setLastLogin($lastLogin)
    {
        $this->last_login = $lastLogin;
    }
    /**
     * Update last login
     */
    public function updateLastLogin()
    {
        $this->updateAttributes(['last_login' => time()]);
    }
    /**
     * Resets password.
     *
     * @param string $password
     *
     * @return bool
     * @throws \yii\base\Exception
     */
    public function resetPassword($password)
    {
        $this->setPassword($password);
        return $this->save(true, ['password_hash']);
    }
}