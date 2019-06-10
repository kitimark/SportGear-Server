<?php 
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
header("Content-Type: application/json");
if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

require __DIR__ . '/../vendor/autoload.php';

//session_start();

require __DIR__ . '/../app/models/config.php';
$app = new \Slim\App($config);


require __DIR__ . '/../app/models/dependencies.php';
require __DIR__ . '/../app/models/routes.php';
$app->run();