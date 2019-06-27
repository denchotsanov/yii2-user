# Getting started with Yii2-user

> If you're using Yii2 advanced template, you should read [this article](docs/usage-with-advanced-template.md) firstly.

### 1. Download

Yii2-user can be installed using composer. Run following command to download and
install Yii2-user:

```bash
composer require denchotsanov/yii2-user
```

### 2. Configure

> **NOTE:** Make sure that you don't have `user` component configuration in your config files.

Add following lines to your main configuration file:

```php
'modules' => [
    'user' => [
        'class' => 'denchotsanov\user\Module',
    ],
],
```

Your need to create the User class that be extends of **User** and configure the property `identityClass` for `user` component in your project configuration, for example:
```php
'user' => [
    'identityClass' => 'denchotsanov\user\models\User',    
],
```

### 3. Update database schema

The last thing you need to do is updating your database schema by applying the
migrations.

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

# Yii2-user with Yii2 advanced template

When using advanced template, you may want to have AdminController only available in backend, and all other controllers available in frontend. This guide will help you with implementing this.

## Configure application

Let's start with defining module in `@common/config/main.php`:

```php
'modules' => [
    'user' => [
        'class' => 'denchotsanov\user\Module',
        // you will configure your module inside this file
        // or if need different configuration for frontend and backend you may
        // configure in needed configs
    ],
],
```

Restrict access to admin controller from frontend. Open `@frontend/config/main.php` and add following:

```
'modules' => [
    'user' => [
        // following line will restrict access to admin controller from frontend application
        'as frontend' => 'denchotsnaov\user\filters\FrontendFilter',
    ],
],
```

Restrict access to profile, recovery, registration and settings controllers from backend. Open `@backend/config/main.php` and add the following:

```
'modules' => [
    'user' => [
        // following line will restrict access to profile, recovery, registration and settings controllers from backend
        'as backend' => 'denchotsnaov\user\filters\BackendFilter',
    ],
],
```

Remove predefined Yii2 user component from frontend `@frontend/config/main.php` and backend `@backend/config/main.php` configuration files. You may simply comment it out the way it is shown below:

```
'components' => [
        ...
        /*'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
        ],*/
        ...
    ],
```

That's all, now you have module installed and configured in advanced template.

## Use independent sessions in one domain

If you have frontend and backend apps in one domain (e.g. domain.com and domain.com/admin),
sometimes you may need to have independent sessions for them, which means that
if you log in on frontend, you will not be logged in on backend.

Configure `@backend\config\main.php`:

```php
'components' => [
    'user' => [
        'identityCookie' => [
            'name'     => '_backendIdentity',
            'path'     => '/admin',
            'httpOnly' => true,
        ],
    ],
    'session' => [
        'name' => 'BACKENDSESSID',
        'cookieParams' => [
            'httpOnly' => true,
            'path'     => '/admin',
        ],
    ],  
],
```

Then configure `@frontend\config\main.php`:

```php
'components' => [
    'user' => [
        'identityCookie' => [
            'name'     => '_frontendIdentity',
            'path'     => '/',
            'httpOnly' => true,
        ],
    ],
    'session' => [
        'name' => 'FRONTENDSESSID',
        'cookieParams' => [
            'httpOnly' => true,
            'path'     => '/',
        ],
    ],  
],
```

From now you have two different sessions for frontend and backend.
