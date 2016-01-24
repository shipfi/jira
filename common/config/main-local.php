<?php
return [
    'components' => [
        'mailer' => [
            'useFileTransport' => true,
            'enableSwiftMailerLogging' => true,
        ],
        'cache' => [
            'class' => 'mysoft\caching\MyFileCacheProxy',
        ]
    ] 
];
