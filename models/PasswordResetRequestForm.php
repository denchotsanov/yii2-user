<?php
namespace denchotsanov\user\models;

use denchotsanov\user\models\enums\UserStatus;
use Yii;
use yii\base\Model;
/**
 * Class PasswordResetRequestForm
 */
class PasswordResetRequestForm extends Model
{
    /**
     * @var string email field for password reset
     */
    public $email;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'exist',
                'targetClass' => Yii::$app->user->identityClass,
                'message' => Yii::t('yii2module.user', 'User with this email is not found.'),
            ],
            ['email', 'exist',
                'targetClass' => Yii::$app->user->identityClass,
                'filter' => ['status' => UserStatus::ACTIVE],
                'message' => Yii::t('yii2module.user', 'Your account has been deactivated, please contact support for details.'),
            ],
        ];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'email' => Yii::t('yii2module.user', 'Email'),
        ];
    }

    /**
     * Sends an email with a link, for resetting the password.
     *
     * @return bool whether the email was send
     * @throws \yii\base\Exception
     */
    public function sendEmail()
    {
        $user = UserModel::findOne(['status' => UserStatus::ACTIVE, 'email' => $this->email]);
        if (!empty($user)) {
            $user->generatePasswordResetToken();
            if ($user->save()) {
                return Yii::$app->mailer->compose('passwordResetToken', ['user' => $user])
                    ->setFrom([Yii::$app->params['adminEmail'] => Yii::$app->name])
                    ->setTo($this->email)
                    ->setSubject(Yii::t('yii2module.user', 'Password reset for {0}', Yii::$app->name))
                    ->send();
            }
        }
        return false;
    }
}