# Adding new field to user model

Suppose, you need to add new field to `User` model which will be editable in
admin panel. Unfortunately at the moment Yii2-user does not support adding new
fields to the registration form.

## Create new migration

Let's start with creating new migration, which will add new field to `user` table:

run `php yii migrate/create add_new_field_to_user` and open generated migration:

```php
class m123456_654321_add_new_field_to_user extends \yii\db\Migration
{
    public function up()
    {
        $this->addColumn('{{%user}}', 'field', Schema::TYPE_STRING);
    }

    public function down()
    {
        $this->dropColumn('{{%user}}', 'field');
    }
}
```

And now you can apply that migration by running `php yii migrate`.

## Override User model

Override `User` model and add following lines to the overridden model:

```php
namespace app\models;

use denchotsnaov\user\models\User as BaseUser;

class User extends BaseUser
{
    public function register()
    {
        // do your magic
    }
}
```

In order to make Yii2-user use your class you need to configure module as follows:

```php
...
'user' => [
    'class' => 'denchotsnaov\user\Module',
    'modelMap' => [
        'User' => 'app\models\User',
    ],
],
...
```

## Attaching behaviors and event handlers

Yii2-user allows you to attach behavior or event handler to any model. To do this you can set model map like so:

```php
[
    ...
    'user' => [
        'class' => 'denchotsnaov\user\Module',
        'modelMap' => [
            'User' => [
                'class' => 'app\models\User',
                'on user_create_init' => function () {
                    // do you magic
                },
                'as foo' => [
                    'class' => 'Foo',
                ],
            ],
        ],
    ],
    ...
]
```
and finally

```php
class User extends \denchotsnaov\user\models\User
{
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        // add field to scenarios
        $scenarios['create'][]   = 'field';
        $scenarios['update'][]   = 'field';
        $scenarios['register'][] = 'field';
        return $scenarios;
    }

    public function rules()
    {
        $rules = parent::rules();
        // add some rules
        $rules['fieldRequired'] = ['field', 'required'];
        $rules['fieldLength']   = ['field', 'string', 'max' => 10];
        
        return $rules;
    }
}
```

## Adding field to the admin form

You should override view file `@denchotsnaov/user/views/admin/_user.php` with the following content:

```php
...
'components' => [
    'view' => [
        'theme' => [
            'pathMap' => [
                '@denchotsnaov/user/views' => '@app/views/user'
            ],
        ],
    ],
],
...
```
and

```php
<?php

/**
 * @var yii\widgets\ActiveForm    $form
 * @var denchotsnaov\user\models\User $user
 */

?>

<?= $form->field($user, 'username')->textInput(['maxlength' => 25]) ?>
<?= $form->field($user, 'email')->textInput(['maxlength' => 255]) ?>
<?= $form->field($user, 'password')->passwordInput() ?>
<?= $form->field($user, 'field')->textInput(['maxlength' => 10]) ?>
```

## Adding field to the registration form

In order to do such thing you should override registration form class and appropriate view file. Let's start with
overriding registration form. Since all the fields of registration form are passed to the User model, we should only add
a field and appropriate validation rules:

```php
class RegistrationForm extends \denchotsnaov\user\models\RegistrationForm
{
    /**
     * @var string
     */
    public $field;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules['fieldRequired'] = ['field', 'required'];
        $rules['fieldLength']   = ['field', 'string', 'max' => 10];
        return $rules;
    }
}
```

And the last thing you need to do is overriding registration form view file:

```php
<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var yii\web\View              $this
 * @var yii\widgets\ActiveForm    $form
 * @var denchotsnaov\user\models\User $model
 */

$this->title = Yii::t('user', 'Sign up');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-4 col-md-offset-4">
        <div class="panel panel-default">
            <div class="panel-heading">
            <h3 class="panel-title"><?= Html::encode($this->title) ?></h3>
            </div>
            <div class="panel-body">
                <?php $form = ActiveForm::begin([
                    'id' => 'registration-form',
                ]); ?>
                
                <?= $form->field($model, 'email') ?>

                <?= $form->field($model, 'username') ?>

                <?= $form->field($model, 'field') ?>

                <?= $form->field($model, 'password')->passwordInput() ?>

                <?= Html::submitButton(Yii::t('user', 'Sign up'), ['class' => 'btn btn-success btn-block']) ?>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
        <p class="text-center">
            <?= Html::a(Yii::t('user', 'Already registered? Sign in!'), ['/user/security/login']) ?>
        </p>
    </div>
</div>
```