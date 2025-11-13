<?php

require_once __DIR__ . '/../models/User.php';

class ClientAuthService
{
    public function validateRegistrationInput(string $name, string $email, string $password): array
    {
        $errors = [];
        $name = trim($name);
        $email = trim($email);
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
        return $errors;
    }

    public function registerClient(int $tenantId, string $name, string $email, string $password): array
    {
        $users = new User($tenantId);
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
