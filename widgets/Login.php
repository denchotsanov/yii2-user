<?php
namespace denchotsanov\user\widgets;

use denchotsanov\user\models\LoginForm;
use yii\base\Widget;

class Login extends Widget
{
    /**
     * @var bool
     */
    public $validate = true;

    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->render('login', [
            'model' => \Yii::createObject(LoginForm::className()),
        ]);
    }
}
