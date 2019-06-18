<?php
$config = [
    'settings' => [
        'displayErrorDetails' => true,
        'addContentLengthHeader' => false,

        'logger' => [
            'name' => 'slim-app',
            'path' => __DIR__ . '/../logs/app.log',
            'level' => \Monolog\Logger::DEBUG,
        ],

        'db' => [
            'host' => getenv('DbHost'),
            'dbname' => getenv('DbName'),
            'user' => getenv('DbUser'),
            'pass' => getenv('DbPass')
        ],
        'token' =>[
            'key' => base64_encode(getenv('SECRET_KEY'))
        ]
        
    ],
];