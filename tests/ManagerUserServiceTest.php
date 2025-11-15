<?php
declare(strict_types=1);

namespace Tests;

use Tests\Support\DatabaseTestCase;

require_once dirname(__DIR__) . '/app/services/ManagerUserService.php';

final class ManagerUserServiceTest extends DatabaseTestCase
{
    private int $tenantId;
    private \ManagerUserService $service;
    private \User $userModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenantId = $this->createTenant('Managers Hub');
        $this->userModel = new \User($this->tenantId);
        $this->service = new \ManagerUserService($this->userModel);
    }

    public function testValidateCreateRejectsInvalidData(): void
    {
        $input = [
            'name' => 'A',
            'email' => 'not-email',
            'password' => '123',
            'role' => 'invalid-role',
            'phone' => '',
            'accessibility_flags' => null,
        ];

        $errors = $this->service->validateCreate($input);

        $this->assertContains('Name must be at least 2 chars', $errors);
        $this->assertContains('Invalid email', $errors);
        $this->assertContains('Password min length 6', $errors);
        $this->assertContains('Invalid role (allowed: client, manager)', $errors);
    }

    public function testValidateCreateAcceptsValidData(): void
    {
        $input = [
            'name' => 'Carlos Rivera',
            'email' => 'carlos.' . uniqid('', true) . '@example.com',
            'password' => 'GoodPwd!7',
            'role' => 'manager',
            'phone' => '612345678',
            'accessibility_flags' => null,
        ];

        $errors = $this->service->validateCreate($input);

        $this->assertSame([], $errors);
    }

    public function testCreatePersistsManagerAndDetails(): void
    {
        $input = [
            'name' => 'Laura Martin',
            'email' => 'laura.' . uniqid('', true) . '@example.com',
            'password' => 'Strong!9',
            'role' => 'manager',
            'phone' => '612 111 222',
            'accessibility_flags' => ['audio_assist'],
        ];

        $id = $this->service->create($input);

        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id);

        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $saved = $stmt->fetch();
        $this->assertNotFalse($saved, 'User should be stored in database');
        $this->assertSame($this->tenantId, (int) $saved['tenant_id']);
        $this->assertSame('Laura Martin', $saved['name']);
        $this->assertSame('manager', $saved['role']);
        $this->assertSame('612111222', $saved['phone']);
        $this->assertSame(json_encode(['audio_assist']), $saved['accessibility_flags']);
        $this->assertNotEmpty($saved['password_hash']);
    }

    public function testValidateUpdateFlagsInvalidValues(): void
    {
        $input = [
            'name' => 'John Doe',
            'email' => 'bad-email',
            'password' => '',
            'role' => 'tenant_admin',
            'phone' => '600000000',
            'accessibility_flags' => null,
        ];

        $errors = $this->service->validateUpdate($input, []);

        $this->assertContains('Invalid email', $errors);
        $this->assertContains('Invalid role (allowed: client, manager)', $errors);
    }

    public function testUpdateChangesRoleWhenDifferent(): void
    {
        $email = 'bob.' . uniqid('', true) . '@example.com';
        $userId = $this->userModel->createUserWithRole('Bob Smith', $email, 'Secret!9', 'client');
        $this->userModel->updateDetails($userId, [
            'name' => 'Bob Smith',
            'email' => $email,
            'phone' => '600600600',
            'accessibility_flags' => null,
        ]);

        $target = $this->userModel->getById($userId);
        $input = [
            'name' => 'Bob Smith Updated',
            'email' => 'bob.updated.' . uniqid('', true) . '@example.com',
            'password' => '',
            'role' => 'manager',
            'phone' => '600 700 800',
            'accessibility_flags' => ['screen_reader'],
        ];

        $this->service->update($userId, $input, $target);

        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute(['id' => $userId]);
        $updated = $stmt->fetch();
        $this->assertNotFalse($updated);
        $this->assertSame('Bob Smith Updated', $updated['name']);
        $this->assertSame('manager', $updated['role']);
        $this->assertSame('600700800', $updated['phone']);
        $this->assertSame(json_encode(['screen_reader']), $updated['accessibility_flags']);
    }

    public function testValidateDeleteAllowsOnlyClients(): void
    {
        $this->assertSame([], $this->service->validateDelete(['role' => 'client']));
        $this->assertSame([
            'Managers can only delete client users',
        ], $this->service->validateDelete(['role' => 'manager']));
    }

    public function testDeleteRemovesUser(): void
    {
        $email = 'carla.' . uniqid('', true) . '@example.com';
        $userId = $this->userModel->createUserWithRole('Carla Jones', $email, 'Secure!8', 'client');
        $this->userModel->updateDetails($userId, [
            'name' => 'Carla Jones',
            'email' => $email,
            'phone' => '690690690',
            'accessibility_flags' => null,
        ]);

        $deleted = $this->service->delete($userId);
        $this->assertTrue($deleted, 'Delete should return true on success');

        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM users WHERE id = :id');
        $stmt->execute(['id' => $userId]);
        $count = (int) $stmt->fetchColumn();
        $this->assertSame(0, $count, 'User row should be removed');
    }
}
