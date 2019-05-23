<?php
namespace denchotsanov\user\actions;

/**
 * Class Action
 *
 */
class Action extends \yii\base\Action
{
    /**
     * @var string|array URL, which user should be redirected to on success.
     * This could be a plain string URL, URL array configuration which returns actual URL
     */
    public $returnUrl;
    /**
     * @param string $defaultActionId
     *
     * @return \yii\web\Response
     */
    public function redirectTo($defaultActionId = 'index')
    {
        if ($this->returnUrl !== null) {
            return $this->controller->redirect($this->returnUrl);
        }
        return $this->controller->redirect($defaultActionId);
    }
}