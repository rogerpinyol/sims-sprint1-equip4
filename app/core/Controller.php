<?php

require_once __DIR__ . '/I18n.php';

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
        // Prefer aggregated user array if present
        $user = $_SESSION['user'] ?? null;
        if (is_array($user)) return $user;
        $userId  = $_SESSION['user_id'] ?? null;
        $role    = $_SESSION['role'] ?? null;
        $tenant  = $_SESSION['tenant_id'] ?? null;
        if ($userId === null && $role === null && $tenant === null) return null;
        return array_filter([
            'id' => is_numeric($userId) ? (int)$userId : $userId,
            'role' => is_string($role) ? $role : $role,
            'tenant_id' => is_numeric($tenant) ? (int)$tenant : $tenant,
        ], static fn($v) => $v !== null);
    }

    protected function requireRole(array $allowedRoles): void
    {
        $user = $this->getAuthenticatedUser();
        $role = $user['role'] ?? null;
        if ($role === null || !in_array($role, $allowedRoles, true)) {
            throw new HttpException(403, 'Forbidden');
        }
    }

    protected function requireSuperAdmin(): void
    {
        // Project policy: platform admin is 'tenant_admin'
        $this->requireRole(['tenant_admin']);
    }

    protected function requireTenant(): int
    {
        $tenantId = (int)($_SESSION['tenant_id'] ?? 0);
        if ($tenantId <= 0) {
            throw new HttpException(400, 'Tenant not resolved. Provide ?tenant or ?tenant_id in dev, or use subdomain.');
        }
        return $tenantId;
    }

    protected function json($data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    protected function jsonResponse(array $body, int $statusCode = 200, array $headers = []): array
    {
        return [
            'status' => $statusCode,
            'headers' => $headers,
            'body' => $body,
        ];
    }

    protected function render(string $viewPath, array $vars = []): void
    {
        $layout = $vars['layout'] ?? null;
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
            include $layout;
            return;
        }
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
    function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}

if (!function_exists('manager_base')) {
    function manager_base(): string {
        $base = getenv('MANAGER_BASE');
        if (!is_string($base) || trim($base) === '') return '/ecomotion-manager';
        if ($base[0] !== '/') $base = '/' . $base;
        return rtrim($base, '/');
    }
}
