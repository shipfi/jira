<?php
// Here you can initialize variables that will for your tests
new yii\web\Application(require(dirname(dirname(__DIR__)) . '/config/frontend/unit.php'));

\Yii::setAlias('demo', YII_APP_BASE_PATH.'/frontend/modules/demo');