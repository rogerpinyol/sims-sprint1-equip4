<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

require_once __DIR__ . '/../app/core/I18n.php';
require_once __DIR__ . '/../app/core/Controller.php'; // For HttpException class

$requestedLocale = $_GET['lang'] ?? ($_SESSION['lang'] ?? ($_COOKIE['lang'] ?? null));
$availableLocales = I18n::availableLocales();
$locale = is_string($requestedLocale) && in_array($requestedLocale, $availableLocales, true)
    ? $requestedLocale
    : I18n::availableLocales()[0];

$_SESSION['lang'] = $locale;
if (!headers_sent()) {
    setcookie('lang', $locale, ['expires' => time() + 60 * 60 * 24 * 30, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
}

I18n::init($locale);

require_once __DIR__ . '/../config/database.php';

$router = require __DIR__ . '/../routes/web.php';

$pdo = $pdo ?? (class_exists('Database') ? Database::getInstance()->getConnection() : null);

// Tenant context handled by middleware on protected routes

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Static assets bypass (css/js/images)
if (preg_match('#^/(assets|favicon\.ico|robots\.txt)#', $uri)) {
    return false; // Let web server serve these directly
}

if ($uri !== '/' && str_ends_with($uri, '/')) {
    $uri = rtrim($uri, '/');
}


$MB = getenv('MANAGER_BASE') ?: '/ecomotion-manager';
if ($MB === '' || $MB === false) { $MB = '/ecomotion-manager'; }
if ($MB[0] !== '/') { $MB = '/' . $MB; }
if ($MB !== '/' && str_ends_with($MB, '/')) { $MB = rtrim($MB, '/'); }

if (in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET','HEAD'], true)
    && $MB !== '/manager' && preg_match('#^/manager($|/)#', $uri)) {
    $target = preg_replace('#^/manager#', $MB, $uri);
    $qs = $_SERVER['QUERY_STRING'] ?? '';
    if (is_string($qs) && $qs !== '') {
        $target .= (str_contains($target, '?') ? '&' : '?') . $qs;
    }
    header('Location: ' . $target, true, 301);
    exit;
}

if ($uri === '/' || $uri === '/landingpage') {
    include __DIR__ . '/landingpage.php';
    exit;
}
if ($uri === '/terms') { include __DIR__ . '/terms.php'; exit; }
if ($uri === '/privacy') { include __DIR__ . '/privacy.php'; exit; }

// Custom route for /ecomotion/renting to show the new landing page
if ($uri === '/ecomotion/renting') {
    if (file_exists(__DIR__ . '/landingpage-Renting.php')) {
        require __DIR__ . '/landingpage-Renting.php';
        exit;
    }
}

// Dispatch via Router class (auto parameter extraction)
if ($router instanceof Router) {
    try {
        $router->dispatch($method, $uri);
    } catch (HttpException $e) {
        // Handle HTTP exceptions (403, 404, etc.)
        http_response_code($e->getStatusCode());
        $error = $e->getMessage();
        
        // Redirect to login for 403 errors
        if ($e->getStatusCode() === 403) {
            if (str_starts_with($uri, '/admin')) {
                header('Location: /admin/login?redirect=' . urlencode($uri));
            } elseif (str_starts_with($uri, '/manager') || str_starts_with($uri, $MB)) {
                header('Location: /manager/login?redirect=' . urlencode($uri));
            } else {
                header('Location: /login?redirect=' . urlencode($uri));
            }
            exit;
        }
        
        // Render error page
        $errorViewPath = __DIR__ . '/../app/views/errors/error.php';
        if (is_file($errorViewPath)) {
            $layout = __DIR__ . '/../app/views/layouts/app.php';
            include $errorViewPath;
        } else {
            echo '<h1>Error ' . $e->getStatusCode() . '</h1><p>' . htmlspecialchars($error) . '</p>';
        }
    } catch (Throwable $e) {
        // Log internal errors but don't expose details
        error_log('Application error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        http_response_code(500);
        
        // Show generic error page
        $errorViewPath = __DIR__ . '/../app/views/errors/error.php';
        if (is_file($errorViewPath)) {
            $error = 'An unexpected error occurred. Please try again later.';
            $layout = __DIR__ . '/../app/views/layouts/app.php';
            include $errorViewPath;
        } else {
            echo '<h1>Error 500</h1><p>An unexpected error occurred. Please try again later.</p>';
        }
    }
} else {
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error');
    echo 'Router not initialized';
}
