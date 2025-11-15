<?php
declare(strict_types=1);

namespace Tests;

use Tests\Support\DatabaseTestCase;

require_once dirname(__DIR__) . '/app/services/ClientAuthService.php';

final class ClientAuthServiceTest extends DatabaseTestCase
{
    public function testValidateRegistrationInputDetectsInvalidFields(): void
    {
        $service = new \ClientAuthService();

        $errors = $service->validateRegistrationInput('A', 'not-an-email', '123', 'invalid');

        $this->assertContains('Name must be at least 2 characters.', $errors);
        $this->assertContains('Email is not valid.', $errors);
        $this->assertContains('Password must be at least 8 characters and include uppercase, lowercase, number, and symbol.', $errors);
        $this->assertContains('Invalid role selection.', $errors);
    }

    public function testValidateRegistrationInputAcceptsValidData(): void
    {
        $service = new \ClientAuthService();

        $errors = $service->validateRegistrationInput('Laura Marquez', 'laura@example.com', 'ValidPass!8', 'client');

        $this->assertSame([], $errors);
    }

    public function testRegisterClientCreatesTenantAndUser(): void
    {
        $service = new \ClientAuthService();
        $email = 'alice.' . uniqid('', true) . '@example.com';

        $result = $service->registerClient(0, 'Alice Walker', $email, 'Secure#Pass9');

        $this->assertArrayHasKey('tenant_id', $result);
        $this->assertArrayHasKey('user_id', $result);
        $this->assertGreaterThan(0, $result['tenant_id']);
        $this->assertGreaterThan(0, $result['user_id']);

        $tenantStmt = $this->pdo->prepare('SELECT * FROM tenants WHERE id = :id');
        $tenantStmt->execute(['id' => $result['tenant_id']]);
        $tenant = $tenantStmt->fetch();
        $this->assertNotFalse($tenant, 'Tenant should exist in database');
        $this->assertSame(1, (int) $tenant['is_active']);

        $userStmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id');
        $userStmt->execute(['id' => $result['user_id']]);
        $user = $userStmt->fetch();
        $this->assertNotFalse($user, 'User should be persisted');
        $this->assertSame($result['tenant_id'], (int) $user['tenant_id']);
        $this->assertSame('client', $user['role']);
        $this->assertSame('Alice Walker', $user['name']);
        $this->assertSame($email, $user['email']);
        $this->assertNotEmpty($user['password_hash']);
    }

    public function testValidateLoginInputRejectsInvalidCredentials(): void
    {
        $service = new \ClientAuthService();

        $errors = $service->validateLoginInput('not-email', '');
        $this->assertSame(['Credenciales inválidas'], $errors);

        $ok = $service->validateLoginInput('alice@example.com', 'secret');
        $this->assertSame([], $ok);
    }
}
