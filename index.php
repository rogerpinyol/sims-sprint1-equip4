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

// If the new router bootstrap exists, use it. Otherwise fall back to the
// legacy simple dispatcher below. This lets you adopt `routes/web.php`
// without removing the old code immediately.
$routerBootstrap = __DIR__ . '/routes/web.php';
if (file_exists($routerBootstrap)) {
    // Ensure the Router class is loaded first to avoid "class not found"
    $routerClassFile = __DIR__ . '/routes/Router.php';
    if (file_exists($routerClassFile)) {
        require_once $routerClassFile;
    }
    // If the incoming request targets the app root, redirect to the
    // vehicles CRUD so users land directly on the list.
    $rawPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
    // Strip leading /index.php if present (some setups include it)
    if (str_starts_with($rawPath, '/index.php')) {
        $rawPath = substr($rawPath, strlen('/index.php')) ?: '/';
    }
    if ($rawPath === '' || $rawPath === '/') {
        header('Location: /vehicles');
        exit;
    }

    require_once $routerBootstrap;
    return;
}

// Legacy fallback dispatcher (kept for compatibility)
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