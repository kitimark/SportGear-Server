<?php
$config = [
    'settings' => [
        'displayErrorDetails' => (strcasecmp(strtolower(getenv('DISPLAY_ERROR_DETAILS')), 'true') ? true : false),
        'addContentLengthHeader' => false,
        'determineRouteBeforeAppMiddleware' => true,

        'logger' => [
            'name' => 'slim-app',
            'path' => __DIR__ . '/../logs/app.log',
            'level' => \Monolog\Logger::DEBUG,
        ],

        'db' => [
            'host' => getenv('DB_HOST'),
            'dbname' => getenv('DB_NAME'),
            'user' => getenv('DB_USER'),
            'pass' => getenv('DB_PASSWORD')
        ],
        'token' =>[
            'key' => base64_encode(getenv('SECRET_KEY'))
        ]
        
    ],
];