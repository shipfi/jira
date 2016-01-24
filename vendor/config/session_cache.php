<?php
return [
    'class' => 'yii\caching\MemCache',
    'servers' => [
        [
            'host' => '10.173.141.104',
            'port' => 11211,
            'weight' => 60,
        ],
        
        [
            'host' => '10.175.204.42',
            'port' => 11211,
            'weight' => 60,
        ], 
        
        [
            'host' => '10.168.85.174',
            'port' => 11211,
            'weight' => 60,
        ],
        
    ],
];
