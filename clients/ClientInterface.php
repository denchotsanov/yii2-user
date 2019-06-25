<?php
namespace denchotsanov\user\clients;

use yii\authclient\ClientInterface as BaseInterface;

interface ClientInterface extends BaseInterface
{
    public function getEmail();
    public function getUsername();
}