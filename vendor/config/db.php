<?php
return [
    'config'=>[
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=10.5.10.109;dbname=_config',
        'charset' => 'utf8',
        'username' => '',
        'password' => '',
    ],
    'log'=>[
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=10.5.10.109;dbname=my_log',
        'charset' => 'utf8',
        'username' => 'platform',
        'password' => 'platform',
    ],
    'report_mall'=>[
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=10.5.10.109;dbname=report_mall',
        'charset' => 'utf8',
        'username' => 'platform',
        'password' => 'platform',
    ]

];