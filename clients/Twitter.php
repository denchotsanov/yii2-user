<?php
namespace denchotsanov\user\clients;

use yii\authclient\clients\Twitter as BaseTwitter;
use yii\helpers\ArrayHelper;
class Twitter extends BaseTwitter implements ClientInterface
{
    /**
     * @return string
     */
    public function getUsername()
    {
        return ArrayHelper::getValue($this->getUserAttributes(), 'screen_name');
    }
    /**
     * @return string|null
     */
    public function getEmail()
    {
        return ArrayHelper::getValue($this->getUserAttributes(), 'email');
    }
}