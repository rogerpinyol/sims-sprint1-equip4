<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/services/ClientAuthService.php';
require_once __DIR__ . '/../app/services/ManagerAuthService.php';
require_once __DIR__ . '/../app/services/ManagerUserService.php';

class UserDomainTest extends TestCase
{
    private PDO $pdo;
    private int $tenantId;

    protected function setUp(): void
    {
        if (!in_array('mysql', PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('Skipping UserDomainTest: PDO MySQL driver not available. Enable pdo_mysql or run via Docker/GitHub Actions.');
        }
        $host = getenv('MARIADB_HOST') ?: '127.0.0.1';
        $db   = getenv('MARIADB_DB') ?: (getenv('MARIADB_DATABASE') ?: 'test_db');
        $user = getenv('MARIADB_USER') ?: 'test';
        $pass = getenv('MARIADB_PASSWORD') ?: 'test';

        $dsnRoot = "mysql:host=$host;charset=utf8mb4";
        $rootPdo = new PDO($dsnRoot, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        // Ensure database exists
        $rootPdo->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        $this->pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        // Load schema (tenants + users at minimum)
        $schema = file_get_contents(__DIR__ . '/../config/init.sql');
        foreach (array_filter(explode(';', $schema)) as $stmt) {
            $stmt = trim($stmt);
            if ($stmt === '') continue;
            try { $this->pdo->exec($stmt); } catch (PDOException $e) { /* ignore duplicates */ }
        }
        // Extra table needed by transferToTenant
        $this->pdo->exec('CREATE TABLE IF NOT EXISTS audit_logs (id INT AUTO_INCREMENT PRIMARY KEY, actor_user_id INT, entity VARCHAR(50), entity_id INT, action VARCHAR(50), meta TEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)');

        // Clean tables
        $this->pdo->exec('SET FOREIGN_KEY_CHECKS=0');
        foreach (['audit_logs','users','tenants'] as $t) {
            $this->pdo->exec("TRUNCATE TABLE `$t`");
        }
        $this->pdo->exec('SET FOREIGN_KEY_CHECKS=1');

        // Create a base tenant for tests
        $this->pdo->exec("INSERT INTO tenants (name, subdomain, plan_type, is_active) VALUES ('Base', 'base-tenant', 'standard', 1)");
        $this->tenantId = (int)$this->pdo->lastInsertId();
    }

    public function testRegisterWithTenantCreatesTenantAndUser(): void
    {
        $userModel = new User(0, $this->pdo); // dynamic tenant creation path
        $result = $userModel->registerWithTenant('Alice', 'alice@example.com', 'Secure#Pass1');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('tenant_id', $result);
        $this->assertArrayHasKey('user_id', $result);
        $this->assertGreaterThan(0, $result['tenant_id']);
        $this->assertGreaterThan(0, $result['user_id']);
        // User row exists
        $check = $this->pdo->query('SELECT email, tenant_id FROM users WHERE id=' . (int)$result['user_id'])->fetch();
        $this->assertEquals('alice@example.com', $check['email']);
    }

    public function testAuthenticateValidAndInvalidPassword(): void
    {
        $userModel = new User($this->tenantId, $this->pdo);
        $uid = $userModel->register('Bob', 'bob@example.com', 'Strong#Pass2');
        $this->assertIsInt($uid);
        $ok = $userModel->authenticate('bob@example.com', 'Strong#Pass2');
        $this->assertNotNull($ok);
        $fail = $userModel->authenticate('bob@example.com', 'WrongPass');
        $this->assertNull($fail);
    }

    public function testUpdateDetailsValidationPhoneAndEmail(): void
    {
        $userModel = new User($this->tenantId, $this->pdo);
        $uid = $userModel->register('Carol', 'carol@example.com', 'Strong#Pass3');
        // valid phone (9 digits)
        $this->assertTrue($userModel->updateDetails($uid, [
            'phone' => '123 456 789',
            'name' => 'Carol Updated',
            'email' => 'carol.updated@example.com'
        ]));
        $updated = $userModel->getById($uid);
        $this->assertEquals('Carol Updated', $updated['name']);
        $this->assertEquals('123456789', $updated['phone']);
        // invalid phone length
        $this->expectException(InvalidArgumentException::class);
        $userModel->updateDetails($uid, ['phone' => '12345', 'name' => 'X']);
    }

    public function testUpdateRoleInvalidThrows(): void
    {
        $userModel = new User($this->tenantId, $this->pdo);
        $uid = $userModel->register('Dave', 'dave@example.com', 'Strong#Pass4');
        $this->expectException(InvalidArgumentException::class);
        $userModel->updateRole($uid, 'administrator');
    }

    public function testClientAuthServiceRegisterTenantAdminSetsRole(): void
    {
        $svc = new ClientAuthService();
        $result = $svc->registerClient(0, 'Erin', 'erin@example.com', 'Strong#Pass5', 'tenant_admin');
        $this->assertArrayHasKey('tenant_id', $result);
        $tenantId = (int)$result['tenant_id'];
        $userId = (int)$result['user_id'];
        $row = $this->pdo->query('SELECT role, tenant_id FROM users WHERE id=' . $userId)->fetch();
        $this->assertEquals('tenant_admin', $row['role']);
        $this->assertEquals($tenantId, (int)$row['tenant_id']);
    }

    public function testManagerAuthServiceEnsureManagerRole(): void
    {
        $userModel = new User($this->tenantId, $this->pdo);
        $uid = $userModel->createUserWithRole('Frank', 'frank@example.com', 'Strong#Pass6', 'manager');
        $row = $userModel->getById($uid);
        $svc = new ManagerAuthService();
        $this->assertTrue($svc->ensureManagerRole($row));
        $row['role'] = 'client';
        $this->assertFalse($svc->ensureManagerRole($row));
    }

    public function testManagerUserServiceCreateAndUpdate(): void
    {
        $userModel = new User($this->tenantId, $this->pdo);
        $mgrSvc = new ManagerUserService($userModel);
        $input = [
            'name' => 'Gina',
            'email' => 'gina@example.com',
            'password' => 'Strong#Pass7',
            'role' => 'client',
            'phone' => '987654321',
            'accessibility_flags' => json_encode(['contrast' => true])
        ];
        $errors = $mgrSvc->validateCreate($input);
        $this->assertEmpty($errors);
        $id = $mgrSvc->create($input);
        $this->assertIsInt($id);
        $target = $userModel->getById($id);
        $updateInput = [
            'name' => 'Gina M',
            'email' => 'gina.m@example.com',
            'password' => 'ignored',
            'role' => 'manager',
            'phone' => '111222333',
            'accessibility_flags' => json_encode(['contrast' => true, 'zoom' => true])
        ];
        $upErrors = $mgrSvc->validateUpdate($updateInput, $target);
        $this->assertEmpty($upErrors);
        $mgrSvc->update($id, $updateInput, $target);
        $updated = $userModel->getById($id);
        $this->assertEquals('Gina M', $updated['name']);
        $this->assertEquals('manager', $updated['role']);
    }
}
