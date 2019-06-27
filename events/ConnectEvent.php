<?php
namespace denchotsanov\user\events;

use denchotsanov\user\models\Account;
use yii\base\Event;
use yii\web\User;

class ConnectEvent extends Event
{
    /** @var User */
    private $_user;
    /** @var Account */
    private $_account;
    /** @return Account */
    public function getAccount()
    {
        return $this->_account;
    }
    /** @param Account $account */
    public function setAccount(Account $account)
    {
        $this->_account = $account;
    }
    /** @return User */
    public function getUser()
    {
        return $this->_user;
    }
    /** @param User $form */
    public function setUser(User $user)
    {
        $this->_user = $user;
    }
}
