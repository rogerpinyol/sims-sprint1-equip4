<?php

require_once __DIR__ . '/../models/User.php';

class ManagerAuthService
{
    public function validateLoginInput(string $email, string $password): array
    {
        $errors = [];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
            $errors[] = 'Credenciales inválidas';
        }
        return $errors;
    }

    public function authenticate(int $tenantId, string $email, string $password): ?array
    {
        if ($tenantId > 0) {
            $userModel = new User($tenantId);
            $row = $userModel->authenticate($email, $password);
            return $row ?: null;
        }
        $any = (new User(0))->authenticateAnyTenant($email, $password);
        return $any ?: null;
    }

    public function ensureManagerRole(array $row): bool
    {
        $role = strtolower((string)($row['role'] ?? ''));
        return in_array($role, ['manager', 'tenant_admin'], true);
    }
}
