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

            // Invoke handler
            $handler = $route['handler'];
            if (is_callable($handler)) {
                call_user_func($handler, $params);
                return;
            }
            if (is_array($handler) && count($handler) === 2) {
                [$class, $methodName] = $handler;
                if (!class_exists($class)) {
                    http_response_code(500); echo 'Handler class not found'; return;
                }
                $instance = new $class();
                if (!method_exists($instance, $methodName)) {
                    http_response_code(500); echo 'Handler method not found'; return;
                }
                $instance->$methodName(...array_values($params));
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
