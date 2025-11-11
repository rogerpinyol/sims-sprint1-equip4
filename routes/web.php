<?php
// Router bootstrap: wires up a tiny Router and loads modular route files.

require_once __DIR__ . '/Router.php';
require_once __DIR__ . '/../app/controllers/TenantController.php';

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

$router = new Router();

// Welcome route
$router->add('GET', '/', function () use ($ensureCsrfToken) {
    if (is_callable($ensureCsrfToken)) $ensureCsrfToken();
    include __DIR__ . '/../app/views/home.php';
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

// Include tenant routes module (Disabled for now)
// require_once __DIR__ . '/tenant.routes.php';

// Normalize URI (strip /index.php prefix for routing)
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
if (str_starts_with($requestUri, '/index.php')) {
    $requestUri = substr($requestUri, strlen('/index.php')) ?: '/';
}

$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $requestUri);