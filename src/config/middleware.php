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

//Not pass
$app->add(new Tkhamez\Slim\RoleAuth\SecureRouteMiddleware(
    [
        // route pattern -> roles, first "starts-with" match is used
        '/secured/public' => ['any'],
        '/api/v1/user'        => ['university','user'],
        '/api/v1/sport/list' => ['university'],
    ],
    ['redirect_url' => null] // optionally add "Location" header instead of 403 status code
));

#Ref https://github.com/tuupola/slim-jwt-auth
/*
*    middleware for jwt HS512 algo
*    Always use HTTPS for jwt.
*/
//pass
$app->add(new Tuupola\Middleware\JwtAuthentication([
    "path" => ["/api"],
    "attribute" => "jwt",
    "ignore" => ["/api/v1/university/login","/api/v1/university/register"],
    "before" => function ($request, $arguments) {
        $jwt = $request->getAttribute("jwt");
        $roles = $jwt['roles'];
        return $request->withAttribute("roles", $roles);//send a roles to next middleware
    },
    "secret" => $config['settings']['token']['key'],
    "error" => function ($response, $arguments) {
        $data["status"] = "error";
        $data["message"] = $arguments["message"];
        return $response
            ->withHeader("Content-Type", "application/json")
            ->getBody()->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    }
]));


$app->add(new Tuupola\Middleware\CorsMiddleware([
    "origin" => ["*"],
    "origin.server" => "*",
    "methods" => ["GET", "POST", "PUT", "PATCH", "DELETE"],
    "headers.allow" => [],
    "headers.expose" => ["Authorization"],
    "credentials" => false,
    "cache" => 0,
    "error" => function ($request, $response, $arguments) {
        $data["status"] = "error";
        $data["message"] = $arguments["message"];
        return $response
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    },
]));
