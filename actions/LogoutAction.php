<?php


namespace denchotsanov\user\actions;

use Yii;

class LogoutAction extends Action
{
    /**
     * Logs out the current user.
     * @return string
     */
    public function run()
    {
        Yii::$app->user->logout();
        return $this->redirectTo(Yii::$app->getHomeUrl());
    }
}