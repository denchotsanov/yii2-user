<?php
namespace denchotsanov\user\commands;

use denchotsanov\user\models\User;
use denchotsanov\user\Module;
use Yii;
use yii\console\Controller;
use yii\helpers\Console;
/**
 * @property Module $module
 */
class CreateController extends Controller
{
    /**
     * @param string $email Email address
     * @param string $username Username
     * @param null|string $password Password (if null it will be generated automatically)
     * @throws yii\base\InvalidConfigException
     */
    public function actionIndex($email, $username, $password = null)
    {
        $user = Yii::createObject([
            'class'    => User::class,
            'scenario' => 'create',
            'email'    => $email,
            'username' => $username,
            'password' => $password,
        ]);
        if ($user->create()) {
            $this->stdout(Yii::t('user', 'User has been created') . "!\n", Console::FG_GREEN);
        } else {
            $this->stdout(Yii::t('user', 'Please fix following errors:') . "\n", Console::FG_RED);
            foreach ($user->errors as $errors) {
                foreach ($errors as $error) {
                    $this->stdout(' - ' . $error . "\n", Console::FG_RED);
                }
            }
        }
    }
}