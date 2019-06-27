<?php
namespace denchotsanov\user\models;

use denchotsanov\user\traits\ModuleTrait;
use Yii;
use yii\base\Model;

class RegistrationForm extends Model
{
    use ModuleTrait;
    /** @var string User email address */
    public $email;
    /** @var string Username */
    public $username;
    /** @var string Password */
    public $password;
    /** @inheritdoc */
    public function rules()
    {
        $user = $this->module->modelMap['User'];
        return [
            // username rules
            'usernameTrim'     => ['username', 'trim'],
            'usernameLength'   => ['username', 'string', 'min' => 3, 'max' => 255],
            'usernamePattern'  => ['username', 'match', 'pattern' => $user::$usernameRegexp],
            'usernameRequired' => ['username', 'required'],
            'usernameUnique'   => [
                'username',
                'unique',
                'targetClass' => $user,
                'message' => Yii::t('user', 'This username has already been taken')
            ],
            // email rules
            'emailTrim'     => ['email', 'trim'],
            'emailRequired' => ['email', 'required'],
            'emailPattern'  => ['email', 'email'],
            'emailUnique'   => [
                'email',
                'unique',
                'targetClass' => $user,
                'message' => Yii::t('user', 'This email address has already been taken')
            ],
            // password rules
            'passwordRequired' => ['password', 'required', 'skipOnEmpty' => $this->module->enableGeneratingPassword],
            'passwordLength'   => ['password', 'string', 'min' => 6, 'max' => 72],
        ];
    }
    /** @inheritdoc */
    public function attributeLabels()
    {
        return [
            'email'    => Yii::t('user', 'Email'),
            'username' => Yii::t('user', 'Username'),
            'password' => Yii::t('user', 'Password'),
        ];
    }
    /** @inheritdoc */
    public function formName()
    {
        return 'register-form';
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function register()
    {
        if (!$this->validate()) {
            return false;
        }
        /** @var User $user */
        $user = Yii::createObject(User::class);
        $user->setScenario('register');
        $this->loadAttributes($user);
        if (!$user->register()) {
            return false;
        }
        Yii::$app->session->setFlash(
            'info',
            Yii::t(
                'user',
                'Your account has been created and a message with further instructions has been sent to your email'
            )
        );
        return true;
    }
    /**
     * @param User $user
     */
    protected function loadAttributes(User $user)
    {
        $user->setAttributes($this->attributes);
    }
}