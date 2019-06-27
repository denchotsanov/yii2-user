<?php
namespace denchotsanov\user\models;

use denchotsanov\user\Finder;
use denchotsanov\user\Mailer;
use Yii;
use yii\base\Model;

class ResendForm extends Model
{
    public $email;
    protected $mailer;
    protected $finder;
    public function __construct(Mailer $mailer, Finder $finder, $config = [])
    {
        $this->mailer = $mailer;
        $this->finder = $finder;
        parent::__construct($config);
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            'emailRequired' => ['email', 'required'],
            'emailPattern' => ['email', 'email'],
        ];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'email' => \Yii::t('user', 'Email'),
        ];
    }
    /**
     * @inheritdoc
     */
    public function formName()
    {
        return 'resend-form';
    }

    /**
     * @return bool
     * @throws \yii\base\InvalidConfigException
     */
    public function resend()
    {
        if (!$this->validate()) {
            return false;
        }
        $user = $this->finder->findUserByEmail($this->email);
        if ($user instanceof User && !$user->isConfirmed) {
            $token = \Yii::createObject([
                'class' => Token::class,
                'user_id' => $user->id,
                'type' => Token::TYPE_CONFIRMATION,
            ]);
            $token->save(false);
            $this->mailer->sendConfirmationMessage($user, $token);
        }
        Yii::$app->session->setFlash(
            'info',
            Yii::t(
                'user',
                'A message has been sent to your email address. It contains a confirmation link that you must click to complete registration.'
            )
        );
        return true;
    }
}