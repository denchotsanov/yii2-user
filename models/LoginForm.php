<?php
namespace denchotsanov\user\models;

use denchotsanov\user\models\enums\UserStatus;
use Yii;
use yii\base\Model;
/**
 * Login Form
 */
class LoginForm extends Model
{
    /**
     * @var string email
     */
    public $email;
    /**
     * @var string password
     */
    public $password;
    /**
     * @var bool remember me
     */
    public $rememberMe = true;

    /**
     * @var UserModel|null|false UserModel
     */
    private $_user = false;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['email', 'password'], 'required'],
            ['email', 'email'],
            ['password', 'validatePassword'],
            ['rememberMe', 'boolean'],
        ];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'email' => Yii::t('denchotsanov.user', 'Email'),
            'password' => Yii::t('denchotsanov.user', 'Password'),
            'rememberMe' => Yii::t('denchotsanov.user', 'Remember Me'),
        ];
    }
    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param $attribute
     * @param $params
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if ($user && $user->status === UserStatus::STATUS_DELETED) {
                $this->addError($attribute, Yii::t('denchotsanov.user', 'Your account has been deactivated, please contact support for details.'));
            } elseif ($user && $user->status === UserStatus::STATUS_PENDING) {
                $this->addError($attribute, Yii::t('denchotsanov.user', 'Your account not confirm, please check your mail or contact support for details.'));
            } elseif (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, Yii::t('denchotsanov.user', 'Incorrect email or password.'));
            }
        }
    }
    /**
     * Logs in a user using the provided username and password.
     *
     * @return bool whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0);
        } else {
            return false;
        }
    }
    /**
     * Finds user by [[email]]
     *
     * @return UserModel|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = UserModel::findByEmail($this->email);
        }
        return $this->_user;
    }
}