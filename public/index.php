<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

require_once __DIR__ . '/../config/database.php';
$routes = require __DIR__ . '/../routes/web.php';

$pdo = $pdo ?? (class_exists('Database') ? Database::getInstance()->getConnection() : null);

// Resolve tenant early (subdomain or dev overrides)
if (empty($_SESSION['tenant_id'])) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    // strip port
    $hostNoPort = preg_replace('/:\\d+$/', '', $host);
    $subdomain = null;

    // Dev override: ?tenant_id= or ?tenant=
    if (!empty($_GET['tenant_id'])) {
        $_SESSION['tenant_id'] = (int)$_GET['tenant_id'];
    } elseif (!empty($_GET['tenant'])) {
        $subdomain = preg_replace('/[^a-z0-9-]/i', '', (string)$_GET['tenant']);
    } else {
        // Environment-provided base domain (e.g., "ecomotion.test") helps detect subdomain precisely
        $base = getenv('BASE_DOMAIN') ?: '';
        if ($base && str_ends_with($hostNoPort, $base)) {
            $maybe = substr($hostNoPort, 0, -strlen('.' . $base));
            if ($maybe && $maybe !== $base) $subdomain = $maybe;
        } else {
            // Heuristic: if it looks like sub.domain.tld -> take first token as subdomain
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
        } catch (Throwable $__) {
            // ignore resolver failures
        }
    }
}

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Normalize: remove trailing slash except root
if ($uri !== '/' && str_ends_with($uri, '/')) {
    $uri = rtrim($uri, '/');
}

// Redirect legacy /manager* to configured base (e.g., /ecomotion-manager)
$MB = getenv('MANAGER_BASE') ?: '/ecomotion-manager';
// normalize base: ensure leading slash and no trailing slash (except root)
if ($MB === '' || $MB === false) { $MB = '/ecomotion-manager'; }
if ($MB[0] !== '/') { $MB = '/' . $MB; }
if ($MB !== '/' && str_ends_with($MB, '/')) { $MB = rtrim($MB, '/'); }

// Redirect only safe methods (GET/HEAD) to avoid breaking POST bodies
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
if ($uri === '/terms') {
    include __DIR__ . '/terms.php';
    exit;
}
if ($uri === '/privacy') {
    include __DIR__ . '/privacy.php';
    exit;
}

$dispatched = false;
foreach ($routes as [$routeMethod, $pattern, $controllerName, $action]) {
    if ($routeMethod !== $method) continue;

    // Convert pattern like /users/(\d+) into ^/users/(\d+)$
    $regex = '#^' . $pattern . '$#';
    if (preg_match($regex, $uri, $matches)) {
        // remove full match and cast numeric params to int (strict_types safe)
        array_shift($matches);
        foreach ($matches as $i => $m) {
            if (is_string($m) && ctype_digit($m)) {
                $matches[$i] = (int)$m;
            }
        }
    // instantiate controller (controllers live one level up from public/)
    $controllerFile = __DIR__ . '/../app/controllers/' . $controllerName . '.php';
        if (!is_file($controllerFile)) {
            header($_SERVER["SERVER_PROTOCOL"] . " 500 Internal Server Error");
            echo "Controller file not found: $controllerFile";
            exit;
        }
        require_once $controllerFile;
        // When using subfolders like auth/ClientAuthController, the class name is the basename
        $classBase = basename(str_replace('\\', '/', $controllerName));
        if (!class_exists($classBase)) {
            header($_SERVER["SERVER_PROTOCOL"] . " 500 Internal Server Error");
            echo "Controller class not found: $controllerName";
            exit;
        }
        $ctrl = new $classBase();
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