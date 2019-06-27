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
    'identityClass' => 'denchotsanov\user\models\User',    
],
```