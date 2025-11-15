<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

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

// Dispatch via Router class (auto parameter extraction)
if ($router instanceof Router) {
    $router->dispatch($method, $uri);
} else {
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error');
    echo 'Router not initialized';
}
