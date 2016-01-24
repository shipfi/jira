<?php

define('YII_DEBUG_MODULE', true);
define('IS_UPGRADING', false);

$db = require(__DIR__ . '/db.php');
$cache = require(__DIR__.'/cache.php');
$slavecache = require(__DIR__.'/slavecache.php');
$sessioncache = $cache;
$sessioncache['class'] = 'mysoft\caching\SessionCache';
$params = require(__DIR__.'/params.php');
$config = [
    'language' => 'zh-CN',
    'params' => $params,
    'components' => [
        
        'errorHandler' => [
            'class' => 'yii\web\ErrorHandler',
            'errorView' => '@vendor/mysoft/web/views/errorhandler/selfexception.php',
            'exceptionView' => '@vendor/mysoft/web/views/errorhandler/selfexception.php',
            'callStackItemView' => '@vendor/mysoft/web/views/errorhandler/callStackItem.php',
            'previousExceptionView' => '@vendor/mysoft/web/views/errorhandler/previousException.php',
        ],
        
        'db' => $db['config'],
        'logDb'=>$db['log'], 
        'urlManager'=>[
            'enablePrettyUrl'  => true,
            'showScriptName' => false,
            'cache' => false,
            'rules'=>array(
                '<__orgcode:\w+>/<module:\w+>/<controller:[\w|-]+>/<action:[\w|-]+>'=>'<module>/<controller>/<action>',
                '<__orgcode:\w+>/<module:\w+>/<controller:[\w|-]+>/<action:[\w|-]+>.<__format:json|xml>'=>'<module>/<controller>/<action>',
            ),
        ],
        
        'cache' => $cache,
        'slavecache'=>$slavecache,
        
        'session'=>[
              'class'=>'mysoft\web\MyCacheSession',
              'cache'=>$sessioncache  
            ],

        'mailer' => [
            'class' => 'mysoft\helpers\Mailer',
            'viewPath' => '@common/mail',
            'enableSwiftMailerLogging' => false,
        ],
        
        'upload'=>[
            'class'=>'mysoft\upload\Oss',
            'rootDirName'=> '',
        ],
        
        'request' => [
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
                'text/json' => 'yii\web\JsonParser',
            ],
            //暂时先禁用cookie完整性校验，因为统一cookie的validation可以过于麻烦了
            'enableCookieValidation'=>false,
        ],
        
        'http' => [
            'class' => 'mysoft\http\Curl'
        ],
        
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning','info'],
                    'categories' => ['wzs'],
                    'logFile' => '@app/runtime/logs/wzs/wzs.log',
                    'maxFileSize' => 1024 * 2,
                    'maxLogFiles' => 20
                ]
            ],
        ],
    ],
];


return $config;
