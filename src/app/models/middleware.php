<?php
// Application middleware
$app->add(new Tuupola\Middleware\JwtAuthentication([
    "secret" => $config['settings']['token']['key'],
    "ignore" => ["/api/v1/users/login","/user/test/add"],
    "callback" => function ($request, $response, $arguments) use ($container) {
        $container["jwt"] = $arguments["decoded"];
    },
    "error" => function ($response, $arguments) {
        $data["status"] = "error";
        $data["message"] = $arguments["message"];
        return $response
            ->withHeader("Content-Type", "application/json")
            ->getBody()->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    }
]));

//HS512