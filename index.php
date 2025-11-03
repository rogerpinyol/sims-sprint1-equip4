<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

require_once __DIR__ . '/config/database.php';
$routes = require __DIR__ . '/routes/web.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Normalize: remove trailing slash except root
if ($uri !== '/' && str_ends_with($uri, '/')) {
    $uri = rtrim($uri, '/');
}

$dispatched = false;
foreach ($routes as [$routeMethod, $pattern, $controllerName, $action]) {
    if ($routeMethod !== $method) continue;

    // Convert pattern like /users/(\d+) into ^/users/(\d+)$
    $regex = '#^' . $pattern . '$#';
    if (preg_match($regex, $uri, $matches)) {
        // remove full match
        array_shift($matches);
        // instantiate controller
        $controllerFile = __DIR__ . '/app/controllers/' . $controllerName . '.php';
        if (!is_file($controllerFile)) {
            header($_SERVER["SERVER_PROTOCOL"] . " 500 Internal Server Error");
            echo "Controller file not found: $controllerFile";
            exit;
        }
        require_once $controllerFile;
        if (!class_exists($controllerName)) {
            header($_SERVER["SERVER_PROTOCOL"] . " 500 Internal Server Error");
            echo "Controller class not found: $controllerName";
            exit;
        }
        $ctrl = new $controllerName();
        if (!method_exists($ctrl, $action)) {
            header($_SERVER["SERVER_PROTOCOL"] . " 500 Internal Server Error");
            echo "Action not found: $controllerName::$action";
            exit;
        }

        // Call with route params
        call_user_func_array([$ctrl, $action], $matches);
        $dispatched = true;
        break;
    }
}

if (!$dispatched) {
    header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
    echo "Page not found";
}