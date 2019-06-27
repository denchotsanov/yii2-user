<?php

use denchotsanov\user\Module;

/**
 * @var yii\web\View $this
 * @var Module $module
 */

$this->title = $title;

echo $this->render('/_alert', ['module' => $module]);
