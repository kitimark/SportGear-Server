<?php
// Application middleware
$app->add(new Tuupola\Middleware\JwtAuthentication([
    "secure" => true,
    "secret" => $config['settings']['token']['key'],
    "ignore" => ["/api/v1/users/login","/api/v1/university/login","/user/test/add","/routes"],
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