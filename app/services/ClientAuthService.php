<?php

require_once __DIR__ . '/../models/User.php';

class ClientAuthService
{
    public function validateRegistrationInput(string $name, string $email, string $password, string $role = 'client'): array
    {
        $errors = [];
        $name = trim($name);
        $email = trim($email);
        $role = strtolower(trim($role));
        if ($name === '' || strlen($name) < 2) {
            $errors[] = 'Name must be at least 2 characters.';
        } else if (!preg_match("/^[A-Za-zÀ-ÿ ]+$/u", $name)) {
            $errors[] = 'Name must contain only letters and spaces.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email is not valid.';
        }
        if (strlen($password) < 8
            || !preg_match('/[A-Z]/', $password)
            || !preg_match('/[a-z]/', $password)
            || !preg_match('/\d/', $password)
            || !preg_match('/[^A-Za-z\d]/', $password)) {
            $errors[] = 'Password must be at least 8 characters and include uppercase, lowercase, number, and symbol.';
        }
        if (!in_array($role, ['client','tenant_admin'], true)) {
            $errors[] = 'Invalid role selection.';
        }
        return $errors;
    }

    public function registerClient(int $tenantId, string $name, string $email, string $password, string $role = 'client'): array
    {
        $users = new User($tenantId);
        if ($role === 'tenant_admin') {
            // Create tenant first if needed via registerWithTenant then elevate role
            $result = $users->registerWithTenant($name, $email, $password);
            // Update role to tenant_admin
            $modelAdmin = new User((int)$result['tenant_id']);
            $modelAdmin->update((int)$result['user_id'], ['role' => 'tenant_admin']);
            return $result;
        }
        return $users->registerWithTenant($name, $email, $password);
    }

    public function validateLoginInput(string $email, string $password): array
    {
        $errors = [];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
            $errors[] = 'Credenciales inválidas';
        }
        return $errors;
    }

    public function authenticate(string $email, string $password, int $tenantId = 0): ?array
    {
        if ($tenantId > 0) {
            $userModel = new User($tenantId);
            $row = $userModel->authenticate($email, $password);
            return $row ?: null;
        }
        // cross-tenant lookup (returns row with tenant_id)
        $lookup = (new User(0))->authenticateAnyTenant($email, $password);
        return $lookup ?: null;
    }
}
