<?php

class HttpException extends RuntimeException
{
    private int $statusCode;
    private array $payload;

    public function __construct(int $statusCode, string $message, array $payload = [])
    {
        parent::__construct($message, $statusCode);
        $this->statusCode = $statusCode;
        $this->payload = $payload;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }
}

abstract class Controller
{
    public function __construct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }
    }

    protected function getAuthenticatedUser(): ?array
    {
        $userId  = $_SESSION['user_id'] ?? null;
        $role    = $_SESSION['role'] ?? null;
        $tenant  = $_SESSION['tenant_id'] ?? null;
        if ($userId === null && $role === null && $tenant === null) return null;
        return array_filter([
            'id' => is_numeric($userId) ? (int)$userId : null,
            'role' => is_string($role) ? $role : null,
            'tenant_id' => is_numeric($tenant) ? (int)$tenant : null,
        ], static fn($v) => $v !== null);
    }

    protected function requireRole(array $allowedRoles): void
    {
        $user = $this->getAuthenticatedUser();
        $role = $user['role'] ?? null;
        if ($role === null || !in_array($role, $allowedRoles, true)) {
            http_response_code(403);
            echo 'Forbidden';
            exit;
        }
    }

    protected function requireTenant(): int
    {
        $tenantId = (int)($_SESSION['tenant_id'] ?? 0);
        if ($tenantId <= 0) {
            http_response_code(400);
            echo 'Tenant not resolved. Provide ?tenant or ?tenant_id in dev, or use subdomain.';
            exit;
        }
        return $tenantId;
    }

    protected function json($data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    protected function render(string $viewPath, array $vars = []): void
    {
        $layout = $vars['layout'] ?? null; // e.g., path to app/views/layouts/app.php
        $title = $vars['title'] ?? null;

        // Render the view into a buffer first
        if (!is_file($viewPath)) {
            http_response_code(500);
            echo 'View not found: ' . htmlspecialchars($viewPath, ENT_QUOTES, 'UTF-8');
            return;
        }

        extract($vars, EXTR_SKIP);
        ob_start();
        include $viewPath;
        $content = ob_get_clean();

        if ($layout && is_file($layout)) {
            // Provide $content and optional $title to layout
            include $layout;
            return;
        }

        // No layout requested, output raw content
        echo $content;
    }

    protected function parseJsonBody(?string $rawBody = null): array
    {
        $rawBody ??= file_get_contents('php://input');
        if ($rawBody === false || trim((string)$rawBody) === '') return [];
        $decoded = json_decode($rawBody, true);
        return is_array($decoded) ? $decoded : [];
    }

}

if (!function_exists('e')) {
    function e($v) {
        return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
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