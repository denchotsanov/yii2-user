<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://avatars0.githubusercontent.com/u/993323" height="100px">
    </a>
    <h1 align="center">Yii2 User Extension</h1>
    <br>
</p>

[![Latest Stable Version](https://poser.pugx.org/denchotsanov/yii2-user/v/stable)](https://packagist.org/packages/denchotsanov/yii2-user)
[![Total Downloads](https://poser.pugx.org/denchotsanov/yii2-user/downloads)](https://packagist.org/packages/denchotsanov/yii2-user)
[![Latest Unstable Version](https://poser.pugx.org/denchotsanov/yii2-user/v/unstable)](https://packagist.org/packages/denchotsanov/yii2-user)
[![License](https://poser.pugx.org/denchotsanov/yii2-user/license)](https://packagist.org/packages/denchotsanov/yii2-user)

Flexible user registration and authentication module for Yii2

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist denchotsanov/yii2-user "*"
```

or add

```
"denchotsanov/yii2-user": "*"
```

to the require section of your composer.json.
         

Configuration
=============
1) If you use this extension, then you need execute migration by the following command:
```
php yii migrate/up --migrationPath=@vendor/denchotsanov/yii2-user/migrations
```
or

add in console config file add 
```
'controllerMap' => [
        'migrate' => [
            'class' => 'yii\console\controllers\MigrateController',
            'migrationPath' => [
                '@app/migrations',
                '@vendor/denchotsanov/yii2-user/migrations',
                ...
            ],
        ],
    ],
```
2) You need to configure the `params` section in your project configuration:
```php
'params' => [
   'user.passwordResetTokenExpire' => 3600
]
```
3) Your need to create the UserModel class that be extends of [UserModel](https://github.com/denchotsanov/yii2-user/blob/master/models/BaseUserModel.php) and configure the property `identityClass` for `user` component in your project configuration, for example:
```php
'user' => [
    'identityClass' => 'denchotsanov\user\models\UserModel',
    // for update last login date for user, you can call the `afterLogin` event as follows
    'on afterLogin' => function ($event) {
        $event->identity->updateLastLogin();
    }
],
```

4) For sending emails you need to configure the `mailer` component in the configuration of your project.

5) If you don't have the `passwordResetToken.php` template file in the mail folder of your project, then you need to create it, for example:
```php
<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $user */

$resetLink = Yii::$app->urlManager->createAbsoluteUrl(['site/password-reset', 'token' => $user->password_reset_token]);
?>

Hello <?php echo Html::encode($user->username) ?>,

Follow the link below to reset your password:

<?php echo Html::a(Html::encode($resetLink), $resetLink) ?>

```
> This template used for password reset email.

6) Add to SiteController (or configure via `$route` param in urlManager):
```php
    /**
     * @return array
     */
    public function actions()
    {
        return [
            'login' => [
                'class' => 'denchotsanov\user\actions\LoginAction'
            ],
            'logout' => [
                'class' => 'denchotsanov\user\actions\LogoutAction'
            ],
            'signup' => [
                'class' => 'denchotsanov\user\actions\SignupAction'
            ],
            'request-password-reset' => [
                'class' => 'denchotsanov\user\actions\RequestPasswordResetAction'
            ],
            'password-reset' => [
                'class' => 'denchotsanov\user\actions\PasswordResetAction'
            ],
        ];
    }
```

You can then access to this actions through the following URL:

1. http://localhost/login
2. http://localhost/logout
3. http://localhost/signup
4. http://localhost/request-password-reset
5. http://localhost/password-reset                