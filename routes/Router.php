<?php

require_once __DIR__ . '/../app/core/Controller.php'; // For HttpException class

class Router
{
    private array $routes = [];

    public function add(string $method, string $path, $handler, array $middleware = []): void
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

    public function dispatch(string $method, string $uri): void
    {
        $method = strtoupper($method);
        $path = parse_url($uri, PHP_URL_PATH) ?? '/';

        $allowedMethods = [];

        foreach ($this->routes as $route) {
            if (!preg_match($route['regex'], $path, $matches)) {
                continue;
            }
            if ($route['method'] !== $method) {
                $allowedMethods[$route['method']] = true;
                continue;
            }

            // Extract params
            $params = [];
            foreach ($route['paramNames'] as $idx => $name) {
                $params[$name] = $matches[$idx + 1] ?? null;
            }

            // Run middleware
            foreach ($route['middleware'] as $mw) {
                if (is_callable($mw)) {
                    $res = $mw($params);
                    if ($res === false) return; // aborted
                    if (is_array($res)) $params = $res;
                }
            }

            // Invoke handler (callable or [class, method])
            $handler = $route['handler'];
            if (is_callable($handler)) {
                call_user_func($handler, $params);
                return;
            }
            if (is_array($handler) && count($handler) === 2) {
                [$classOrPath, $methodName] = $handler;

                // Support controller path like "auth/ManagerAuthController"
                $class = $classOrPath;
                if (str_contains($classOrPath, '/')) {
                    $segments = explode('/', $classOrPath);
                    $class = end($segments);
                    $controllerFile = __DIR__ . '/../app/controllers/' . $classOrPath . '.php';
                    if (is_file($controllerFile)) {
                        require_once $controllerFile;
                    }
                } else {
                    // Try conventional location
                    $controllerFile = __DIR__ . '/../app/controllers/' . $class . '.php';
                    if (is_file($controllerFile) && !class_exists($class)) {
                        require_once $controllerFile;
                    }
                }

                if (!class_exists($class)) {
                    http_response_code(500); echo 'Handler class not found: ' . htmlspecialchars($class); return;
                }
                $instance = new $class();
                if (!method_exists($instance, $methodName)) {
                    http_response_code(500); echo 'Handler method not found: ' . htmlspecialchars($class . '::' . $methodName); return;
                }
                try {
                    $instance->$methodName(...array_values($params));
                } catch (HttpException $e) {
                    // Handle HTTP exceptions with proper status codes and user-friendly messages
                    $this->handleHttpException($e);
                } catch (Throwable $e) {
                    // Log internal errors but don't expose details to users
                    error_log('Controller error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
                    $this->handleError(500, 'An unexpected error occurred. Please try again later.');
                }
                return;
            }

            http_response_code(500); echo 'Bad route handler definition';
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

    private function pathToRegex(string $path): string
    {
        $regex = preg_replace('#\{[a-zA-Z_][a-zA-Z0-9_]*\}#', '([^/]+)', $path);
        return '#^' . $regex . '$#';
    }

    private function extractParamNames(string $path): array
    {
        if (!preg_match_all('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', $path, $m)) return [];
        return $m[1];
    }

    private function handleHttpException(HttpException $e): void
    {
        $statusCode = $e->getStatusCode();
        $message = $e->getMessage();
        
        // Map status codes to user-friendly messages
        $userMessages = [
            400 => 'Bad Request',
            401 => 'Unauthorized - Please log in',
            403 => 'Access Denied - You do not have permission to access this resource',
            404 => 'Page Not Found',
            405 => 'Method Not Allowed',
            500 => 'Internal Server Error',
        ];
        
        $userMessage = $userMessages[$statusCode] ?? $message;
        
        // If 403, redirect to appropriate login page
        if ($statusCode === 403) {
            $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
            if (str_starts_with($uri, '/admin')) {
                header('Location: /admin/login?redirect=' . urlencode($uri));
                exit;
            } elseif (str_starts_with($uri, '/manager') || str_starts_with($uri, manager_base())) {
                header('Location: /manager/login?redirect=' . urlencode($uri));
                exit;
            } else {
                header('Location: /login?redirect=' . urlencode($uri));
                exit;
            }
        }
        
        $this->handleError($statusCode, $userMessage);
    }

    private function handleError(int $statusCode, string $message): void
    {
        http_response_code($statusCode);
        
        // Try to render error view if available
        $errorViewPath = __DIR__ . '/../app/views/errors/error.php';
        if (is_file($errorViewPath)) {
            $error = $message;
            $layout = __DIR__ . '/../app/views/layouts/app.php';
            include $errorViewPath;
        } else {
            // Fallback to plain HTML
            echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">';
            echo '<title>Error ' . $statusCode . '</title>';
            echo '<style>body{font-family:sans-serif;padding:2rem;max-width:600px;margin:0 auto;}';
            echo 'h1{color:#dc2626;}</style></head><body>';
            echo '<h1>Error ' . $statusCode . '</h1>';
            echo '<p>' . htmlspecialchars($message) . '</p>';
            echo '<p><a href="/">Return to Home</a></p>';
            echo '</body></html>';
        }
    }
}

if (!function_exists('manager_base')) {
    function manager_base(): string {
        $base = getenv('MANAGER_BASE');
        if (!is_string($base) || trim($base) === '') return '/ecomotion-manager';
        if ($base[0] !== '/') $base = '/' . $base;
        return rtrim($base, '/');
    }
}
