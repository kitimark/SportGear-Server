<?php

define("DbHost", "localhost");
define("DbName", "gearsport");
define("DbUser", "gearsport");
define("DbPass", "Z2VhcnNwb3J0");

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
            'host' => DbHost,
            'dbname' => DbName,
            'user' => DbUser,
            'pass' => DbPass
        ],
        'token' =>[
            'key' => base64_encode('testing')
        ]
        
    ],
];