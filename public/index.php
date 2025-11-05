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

$dispatched = false;
foreach ($routes as [$routeMethod, $pattern, $controllerName, $action]) {
    if ($routeMethod !== $method) continue;

    // Convert pattern like /users/(\d+) into ^/users/(\d+)$
    $regex = '#^' . $pattern . '$#';
    if (preg_match($regex, $uri, $matches)) {
        // remove full match
        array_shift($matches);
    // instantiate controller (controllers live one level up from public/)
    $controllerFile = __DIR__ . '/../app/controllers/' . $controllerName . '.php';
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