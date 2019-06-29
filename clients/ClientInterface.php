<?php

namespace denchotsanov\user\clients;

use yii\authclient\ClientInterface as BaseInterface;

interface ClientInterface extends BaseInterface
{
    /** @return string|null User's email */
    public function getEmail();

    /** @return string|null User's username */
    public function getUsername();
}
