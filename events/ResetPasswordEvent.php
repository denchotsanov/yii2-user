<?php
namespace denchotsanov\user\events;

use denchotsanov\user\models\RecoveryForm;
use denchotsanov\user\models\Token;
use yii\base\Event;

/**
 * @property Token        $token
 * @property RecoveryForm $form

 */
class ResetPasswordEvent extends Event
{
    /**
     * @var RecoveryForm
     */
    private $_form;

    /**
     * @var Token
     */
    private $_token;

    /**
     * @return Token
     */
    public function getToken()
    {
        return $this->_token;
    }

    /**
     * @param Token $token
     */
    public function setToken(Token $token = null)
    {
        $this->_token = $token;
    }

    /**
     * @return RecoveryForm
     */
    public function getForm()
    {
        return $this->_form;
    }

    /**
     * @param RecoveryForm $form
     */
    public function setForm(RecoveryForm $form = null)
    {
        $this->_form = $form;
    }
}
