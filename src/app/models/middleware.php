<?php
// Application middleware
include( __DIR__ . '/roleprovider.php');
//Init Middleware
//Inside out, so the last one added is the first one executed.
#Ref https://github.com/tkhamez/slim-role-auth
$app->add(new Tkhamez\Slim\RoleAuth\RoleMiddleware(
    new Tkhamez\Slim\RoleAuth\RoleProvider(), // must implement RoleProviderInterface -> implement at roleprovider.php
    ['route_pattern' => ['/api']] // optionally limit to these routes
)); 


$app->add(new Tkhamez\Slim\RoleAuth\SecureRouteMiddleware(
    [
        // route pattern -> roles, first "starts-with" match is used
        '/secured/public' => ['any'],
        '/secured'        => ['user'],
        '/api/v1/sport/list' => ['university'],
    ],
    ['redirect_url' => null] // optionally add "Location" header instead of 403 status code
));

#Ref https://github.com/tuupola/slim-jwt-auth
/*
*    middleware for jwt HS512 algo
*    Always use HTTPS for jwt.
*/

$app->add(new Tuupola\Middleware\JwtAuthentication([
    "path" => ["/api"],
    "attribute" => "token",
    "ignore" => ["/api/v1/university/login"],
    "secret" => $config['settings']['token']['key'],
    "error" => function ($response, $arguments) {
        $data["status"] = "error";
        $data["message"] = $arguments["message"];
        return $response
            ->withHeader("Content-Type", "application/json")
            ->getBody()->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    }
]));



// TODO: setting up CORS http
$app->add(function($request, $response, $next) {
    $route = $request->getAttribute("route");
    $methods = [];

    if (!empty($route)) {
        $pattern = $route->getPattern();

        foreach ($this->router->getRoutes() as $route) {
            if ($pattern === $route->getPattern()) {
                $methods = array_merge_recursive($methods, $route->getMethods());
            }
        }
        //Methods holds all of the HTTP Verbs that a particular route handles.
    } else {
        $methods[] = $request->getMethod();
    }

    $response = $next($request, $response);
    return $response->withHeader("Access-Control-Allow-Methods", implode(",", $methods))
                    ->withHeader("Access-Control-Allow-Origin", "*")
                    ->withHeader('Access-Control-Expose-Headers', 'Authorization')
                    ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization');
});