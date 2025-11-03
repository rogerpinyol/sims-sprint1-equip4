<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session early before any output so session_start() in views won't trigger
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


spl_autoload_register(function ($class) {
    $paths = [
        "app/controllers/{$class}.php",
        "app/models/{$class}.php",
        "app/core/{$class}.php"
    ];

    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});


$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

$controller = new VehicleController();

match ($uri) {
    '', 'vehicles'          => $controller->index(),
    'vehicle/create'        => $controller->create(),
    'vehicle/store'         => $controller->store(),
    'vehicle/edit'          => $controller->edit(),
    'vehicle/update'        => $controller->update(),
    'vehicle/delete'        => $controller->delete(),
    default                 => $controller->notFound(),
};