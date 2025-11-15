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
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
    }

    protected function getAuthenticatedUser(): ?array
    {
        $user = $_SESSION['user'] ?? null;
        if (is_array($user)) {
            return $user;
        }

        // Fallback to individual session keys
        $role = $_SESSION['role'] ?? null;
        $tenantId = $_SESSION['tenant_id'] ?? null;
        $userId = $_SESSION['user_id'] ?? null;

        if ($role === null && $tenantId === null && $userId === null) {
            return null;
        }

        return array_filter([
            'id' => $userId,
            'role' => $role,
            'tenant_id' => $tenantId,
        ], static fn($value) => $value !== null);
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
        $this->requireRole(['super_admin']);
    }

    protected function jsonResponse(array $body, int $statusCode = 200, array $headers = []): array
    {
        return [
            'status' => $statusCode,
            'headers' => $headers,
            'body' => $body,
        ];
    }

    protected function parseJsonBody(?string $rawBody = null): array
    {
        $rawBody ??= file_get_contents('php://input');
        if ($rawBody === false || trim($rawBody) === '') {
            return [];
        }

        $decoded = json_decode($rawBody, true);
        return is_array($decoded) ? $decoded : [];
    }
}