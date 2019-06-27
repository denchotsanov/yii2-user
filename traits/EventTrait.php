<?php
namespace denchotsanov\user\traits;

use denchotsanov\user\models\Account;
use denchotsanov\user\models\Profile;
use denchotsanov\user\models\RecoveryForm;
use denchotsanov\user\models\Token;
use denchotsanov\user\models\User;
use Yii;
use yii\authclient\ClientInterface;
use yii\base\Model;

trait EventTrait
{
    /**
     * @param  Model     $form
     * @return FormEvent
     * @throws \yii\base\InvalidConfigException
     */
    protected function getFormEvent(Model $form)
    {
        return Yii::createObject(['class' => FormEvent::class, 'form' => $form]);
    }
    /**
     * @param  User      $user
     * @return UserEvent
     * @throws \yii\base\InvalidConfigException
     */
    protected function getUserEvent(User $user)
    {
        return Yii::createObject(['class' => UserEvent::class, 'user' => $user]);
    }
    /**
     * @param  Profile      $profile
     * @return ProfileEvent
     * @throws \yii\base\InvalidConfigException
     */
    protected function getProfileEvent(Profile $profile)
    {
        return Yii::createObject(['class' => ProfileEvent::class, 'profile' => $profile]);
    }
    /**
     * @param  Account      $account
     * @param  User         $user
     * @return ConnectEvent
     * @throws \yii\base\InvalidConfigException
     */
    protected function getConnectEvent(Account $account, User $user)
    {
        return Yii::createObject(['class' => ConnectEvent::class, 'account' => $account, 'user' => $user]);
    }
    /**
     * @param  Account         $account
     * @param  ClientInterface $client
     * @return AuthEvent
     * @throws \yii\base\InvalidConfigException
     */
    protected function getAuthEvent(Account $account, ClientInterface $client)
    {
        return Yii::createObject(['class' => AuthEvent::class, 'account' => $account, 'client' => $client]);
    }
    /**
     * @param  Token        $token
     * @param  RecoveryForm $form
     * @return ResetPasswordEvent
     * @throws \yii\base\InvalidConfigException
     */
    protected function getResetPasswordEvent(Token $token = null, RecoveryForm $form = null)
    {
        return Yii::createObject(['class' => ResetPasswordEvent::class, 'token' => $token, 'form' => $form]);
    }
}