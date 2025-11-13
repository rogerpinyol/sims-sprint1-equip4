<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

require_once __DIR__ . '/../config/database.php';
// Build router instance with all routes
$router = require __DIR__ . '/../routes/web.php';

$pdo = $pdo ?? (class_exists('Database') ? Database::getInstance()->getConnection() : null);

// Resolve tenant early (subdomain or dev overrides)
if (empty($_SESSION['tenant_id'])) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $hostNoPort = preg_replace('/:\d+$/', '', $host);
    $subdomain = null;
    if (!empty($_GET['tenant_id'])) {
        $_SESSION['tenant_id'] = (int)$_GET['tenant_id'];
    } elseif (!empty($_GET['tenant'])) {
        $subdomain = preg_replace('/[^a-z0-9-]/i', '', (string)$_GET['tenant']);
    } else {
        $base = getenv('BASE_DOMAIN') ?: '';
        if ($base && str_ends_with($hostNoPort, $base)) {
            $maybe = substr($hostNoPort, 0, -strlen('.' . $base));
            if ($maybe && $maybe !== $base) $subdomain = $maybe;
        } else {
            if ($hostNoPort && $hostNoPort !== 'localhost' && substr_count($hostNoPort, '.') >= 2) {
                $subdomain = explode('.', $hostNoPort)[0] ?? null;
            }
        }
    }
    if (!empty($subdomain) && $pdo instanceof PDO) {
        try {
            $stmt = $pdo->prepare('SELECT id FROM tenants WHERE subdomain = :sub LIMIT 1');
            $stmt->execute(['sub' => $subdomain]);
            $tid = $stmt->fetchColumn();
            if ($tid) $_SESSION['tenant_id'] = (int)$tid;
        } catch (Throwable $__) { /* ignore */ }
    }
}

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
