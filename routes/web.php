<?php
// Router bootstrap: wires up a tiny Router and loads modular route files.

// Require the Router implementation. If it's missing, emit a clear error so
// the runtime (and logs) show why the Router class cannot be found.
$routerFile = __DIR__ . '/Router.php';
if (!file_exists($routerFile)) {
    http_response_code(500);
    echo "Router implementation not found: {$routerFile}.\n";
    echo "Please ensure routes/Router.php exists and is readable.";
    exit(1);
}
require_once $routerFile;

if (session_status() === PHP_SESSION_NONE) session_start();
// Simulate super_admin for local testing if not already set
if (!isset($_SESSION['user'])) {
    $_SESSION['user'] = ['id' => 1, 'role' => 'super_admin'];
}

// Helpers
$renderError = function (string $message, int $status = 500) {
    http_response_code($status);
    $errorMessage = $message;
    include __DIR__ . '/../app/views/errors/error.php';
};

$ensureCsrfToken = function () {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
    }
};

$requireSuperAdmin = function () use ($renderError) {
    $role = $_SESSION['user']['role'] ?? null;
    if ($role !== 'super_admin') {
        $renderError('Forbidden: super_admin required', 403);
        return false;
    }
    return true;
};

$csrfCheck = function () use ($renderError) {
    if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') return true;
    $ok = isset($_POST['csrf_token'], $_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string)$_POST['csrf_token']);
    if (!$ok) {
        $renderError('Invalid CSRF token', 400);
        return false;
    }
    return true;
};

$router = null;

// If the Router class didn't get declared (deployment quirk or PHP
// incompatibility), provide a small, compatible fallback implementation
// (no typed properties or return types) so the app can run.
if (!class_exists('Router')) {
    class Router
    {
        private $routes = [];

        public function add($method, $path, $handler, $middleware = [])
        {
            $this->routes[] = [
                'method' => strtoupper($method),
                'path' => $path,
                'handler' => $handler,
                'middleware' => $middleware,
                'regex' => $this->pathToRegex($path),
                'paramNames' => $this->extractParamNames($path),
            ];
        }

        public function dispatch($method, $uri)
        {
            $method = strtoupper($method);
            $path = parse_url($uri, PHP_URL_PATH) ?: '/';

            $allowedMethods = [];

            foreach ($this->routes as $route) {
                if (!preg_match($route['regex'], $path, $matches)) {
                    continue;
                }
                if ($route['method'] !== $method) {
                    $allowedMethods[$route['method']] = true;
                    continue;
                }

                $params = [];
                foreach ($route['paramNames'] as $idx => $name) {
                    $params[$name] = isset($matches[$idx + 1]) ? $matches[$idx + 1] : null;
                }

                foreach ($route['middleware'] as $mw) {
                    if (is_callable($mw)) {
                        $res = $mw($params);
                        if ($res === false) return;
                        if (is_array($res)) $params = $res;
                    }
                }

                $handler = $route['handler'];
                if (is_callable($handler)) {
                    call_user_func($handler, $params);
                    return;
                }
                if (is_array($handler) && count($handler) === 2) {
                    list($class, $methodName) = $handler;
                    if (!class_exists($class)) { http_response_code(500); echo 'Handler class not found'; return; }
                    $instance = new $class();
                    if (!method_exists($instance, $methodName)) { http_response_code(500); echo 'Handler method not found'; return; }
                    call_user_func_array([$instance, $methodName], array_values($params));
                    return;
                }

                http_response_code(500); echo 'Bad route handler';
                return;
            }

            if (!empty($allowedMethods)) {
                header('Allow: ' . implode(', ', array_keys($allowedMethods)));
                http_response_code(405);
                echo '405 Method Not Allowed';
                return;
            }

            http_response_code(404);
            echo '404 Not Found';
        }

        private function pathToRegex($path)
        {
            $regex = preg_replace('#\{[a-zA-Z_][a-zA-Z0-9_]*\}#', '([^/]+)', $path);
            return '#^' . $regex . '$#';
        }

        private function extractParamNames($path)
        {
            if (!preg_match_all('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', $path, $m)) return [];
            return $m[1];
        }
    }

    // Optional: log that fallback Router was used
    if (function_exists('error_log')) {
        error_log('[INFO] routes/web.php: using fallback Router implementation');
    }
}

$router = new Router();

// Welcome route
$router->add('GET', '/', function () {
    echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Welcome - EcoMotion Platform</title>
    <script src='https://cdn.tailwindcss.com'></script>
    <style>body{font-family:Arial,sans-serif}</style>
    </head>
<body style='max-width: 800px; margin: 50px auto; padding: 20px;'>
    <h1>Welcome to EcoMotion Platform</h1>
    <p>This is a multi-tenant SaaS platform for vehicle fleet management.</p>
    <ul>
        <li><a href='/index.php/admin/tenants'>Super Admin Dashboard (Tenants Management)</a></li>
    </ul>
</body>
</html>";
});

// Load modular routes (e.g., admin.routes.php)
// Expose variables to route modules
$__routeContext = [
    'router' => $router,
    'requireSuperAdmin' => $requireSuperAdmin,
    'csrfCheck' => $csrfCheck,
    'renderError' => $renderError,
    'ensureCsrfToken' => $ensureCsrfToken,
];

// Include admin routes module
require_once __DIR__ . '/admin.routes.php';

// Normalize URI (strip /index.php prefix for routing)
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
if (str_starts_with($requestUri, '/index.php')) {
    $requestUri = substr($requestUri, strlen('/index.php')) ?: '/';
}

$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $requestUri);