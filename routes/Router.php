<?php

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
                } catch (Throwable $e) {
                    http_response_code(500); echo 'Controller error: ' . htmlspecialchars($e->getMessage());
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
}
