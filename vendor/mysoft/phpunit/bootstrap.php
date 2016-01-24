<?php
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'unittest');

require(__DIR__ . '/../../../vendor/autoload.php');
require(__DIR__ . '/../../../vendor/yiisoft/yii2/Yii.php');
require(__DIR__ . '/../../common/config/bootstrap.php');

$config = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../../../vendor/config/main.php'),
    require(__DIR__ . '/../../common/config/main.php'),
    require(__DIR__ . '/../../common/config/main-local.php'),
    require(__DIR__ . '/config/main.php')
);

if(file_exists(__DIR__.'/config/main-local.php')) {
    $config = yii\helpers\ArrayHelper::merge($config, require(__DIR__ . '/config/main-local.php'));
}

//注意，这里*-end/config里面的配置没有加载，如果有特殊的要求，请自行在tests/phpunit/config里面添加

\Yii::setAlias('tests/phpunit', __DIR__);

(new mysoft\base\TestApplication($config))->run();

//请自行将myosft/phpunit目录拷贝到项目的/tests/phpunit目录处。
