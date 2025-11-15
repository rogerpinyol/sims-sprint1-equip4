<?php

require_once __DIR__ . '/../core/Model.php';

class User extends Model {
    protected $table = 'users';
    protected $fillable = ['name', 'email', 'phone', 'accessibility_flags', 'password_hash', 'role'];


    // Functions
    public function __construct(int $tenantId) {
        parent::__construct('users', $tenantId);
    }

    public function getById(int $id): ?array {
        $rows = $this->find(['id' => $id]);
        return $rows ? $rows[0] : null;
    }

    public function emailExists(string $email): bool {
        $rows = $this->find(['email' => $email]);
        return !empty($rows);
    }

    public function getByEmail(string $email): ?array {
        $rows = $this->find(['email' => $email]);
        return $rows ? $rows[0] : null;
    }

    public function authenticate(string $email, string $password): ?array {
        $user = $this->getByEmail($email);
        if (!$user) return null;
        if (!isset($user['password_hash']) || !password_verify($password, (string)$user['password_hash'])) {
            return null;
        }
        return $user;
    }

    public function findAnyTenantByEmail(string $email): ?array {
        try {
            $stmt = $this->pdo->prepare('SELECT id, email, password_hash, role, tenant_id FROM users WHERE email = :email LIMIT 1');
            $stmt->execute(['email' => $email]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (Throwable $e) {
            $this->logError($e);
            return null;
        }
    }

    public function authenticateAnyTenant(string $email, string $password): ?array {
        $row = $this->findAnyTenantByEmail($email);
        if (!$row) return null;
        if (!isset($row['password_hash']) || !password_verify($password, (string)$row['password_hash'])) {
            return null;
        }
        return $row;
    }

    // Superuser users creation with roles
    public function createUserWithRole(string $name, string $email, string $plain_password, string $role): int|false {
        $allowedRoles = ['client', 'manager', 'tenant_admin'];
        if (!in_array($role, $allowedRoles, true)) {
            throw new InvalidArgumentException('Invalid role');
        }

        $hash = password_hash($plain_password, PASSWORD_BCRYPT);
        
        return $this->insert([
            'name'=>$name, 
            'email'=>$email, 
            'password_hash'=>$hash, 
            'role'=>$role
        ]);
    }
    

    public function register(string $name, string $email, string $plain_password): int|false {
        $hash = password_hash($plain_password, PASSWORD_BCRYPT);
        return $this->insert([
            'name'=>$name,
            'email'=>$email,
            'password_hash'=>$hash,
            'role'=>'client'
        ]);
    }

    public function registerWithTenant(string $name, string $email, string $plain_password): array {
        $this->pdo->beginTransaction();
        try {
            if ($this->tenantId <= 0) {
                $this->tenantId = $this->createTenantFromEmail($name, $email);
            }

            if ($this->emailExists($email)) {
                throw new RuntimeException('Email already used');
            }

            $userId = $this->register($name, $email, $plain_password);
            if ($userId === false) {
                throw new RuntimeException('User creation failed');
            }

            $this->pdo->commit();
            return ['tenant_id' => $this->tenantId, 'user_id' => (int)$userId];
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $e;
        }
    }

    private function createTenantFromEmail(string $name, string $email): int {
        $domain = substr(strrchr($email, '@'), 1) ?: null;
        $tenantName = $domain ? explode('.', $domain)[0] : 'public';
        $sub = $tenantName . '-' . time();
        $attempts = 0;
        do {

            $chk = $this->pdo->prepare('SELECT id FROM tenants WHERE subdomain = :sub');
            $chk->execute(['sub' => $sub]);
            if ($chk->fetchColumn()) {
                $sub = $tenantName . '-' . time() . '-' . rand(1000, 9999);
            }

            try {
                $stmt = $this->pdo->prepare('INSERT INTO tenants (name, subdomain, plan_type, created_at) VALUES (?, ?, ?, NOW())');
                $stmt->execute([$name, $sub, 'standard']);
                return (int)$this->pdo->lastInsertId();
            } catch (PDOException $ex) {
                if ($ex->getCode() === '23000' && $attempts < 3) {
                    $sub = $tenantName . '-' . time() . '-' . rand(1000, 9999);
                    $attempts++;
                    continue;
                }
                throw $ex;
            }
        } while ($attempts < 4);

        throw new RuntimeException('Failed to create tenant');
    }


    public function getAll(): array|false {
    return $this->find();
    }


    public function updateDetails(int $user_id, array $data) {
        
        unset($data['role'], $data['password_hash']);
        
        $allowed = ['name' => true, 'email' => true, 'phone' => true, 'accessibility_flags' => true];
        $data = array_intersect_key($data, $allowed);
        
        if (isset($data['phone']) && strlen($data['phone']) > 30) {
            throw new InvalidArgumentException('Phone number too long (max 30 characters)');
        }
        
        // Validate phone format
        if (isset($data['phone'])) {
            $phone = trim($data['phone']);
            $phone = preg_replace('/\D/', '', $phone); // Remove non-digits
            if (strlen($phone) !== 9) {
                throw new InvalidArgumentException('Phone number must be exactly 9 digits.');
            }
            $data['phone'] = $phone; // Store as plain digits
        }

        if (isset($data['accessibility_flags'])) {
            $val = $data['accessibility_flags'];
            if (is_array($val)) {
                $data['accessibility_flags'] = json_encode($val);
            } elseif (is_string($val)) {
                $val = trim($val);
                $data['accessibility_flags'] = $val === '' ? null : json_encode($val);
            } else {
                $data['accessibility_flags'] = null;
            }
        }

        if (isset($data['email'])) {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                throw new InvalidArgumentException('Invalid email format');  
            }

            $existing = $this->find(['email' => $data['email']]);
            if (!empty($existing) && $existing[0]['id'] != $user_id) {
                throw new RuntimeException('Email already in use');
            }
        }

        $result = $this->update($user_id, $data);
        if ($result === false) {
            throw new RuntimeException('Failed to update user details');
        }
        return $result;
    }


    public function changePassword(int $user_id, string $plain_password) {
        $hash = password_hash($plain_password, PASSWORD_BCRYPT);
        return $this->update($user_id, ['password_hash' => $hash]);
    }

    // Update role to 'client' or 'manager' only (tenant-scoped)
    public function updateRole(int $user_id, string $role): bool {
        $role = strtolower(trim($role));
        if (!in_array($role, ['client', 'manager'], true)) {
            throw new InvalidArgumentException('Invalid role');
        }
        return $this->update($user_id, ['role' => $role]);
    }

    
    // Transfer user to another tenant
    public function transferToTenant(int $user_id, int $newTenantId, int $actorUserId = 0): bool {
        if ($user_id <= 0 || $newTenantId <= 0) {
            throw new InvalidArgumentException('Invalid ids');
        }

        $stmt = $this->pdo->prepare('SELECT id FROM tenants WHERE id = :id');
        $stmt->execute(['id' => $newTenantId]);
        if ($stmt->fetchColumn() === false) {
            throw new RuntimeException('Target tenant not found');
        }

        $rows = $this->find(['id' => $user_id]);
        if (empty($rows)) throw new RuntimeException('User not found');
        $user = $rows[0];

        if (!empty($user['email'])) {
            $chk = $this->pdo->prepare('SELECT id FROM users WHERE email = :email AND tenant_id = :tenant_id');
            $chk->execute(['email' => $user['email'], 'tenant_id' => $newTenantId]);
            if ($chk->fetchColumn() !== false) {
                throw new RuntimeException('Email conflict in target tenant');
            }
        }

        $this->pdo->beginTransaction();
        try {
            $upd = $this->pdo->prepare('UPDATE `users` SET tenant_id = :new_t WHERE id = :id AND tenant_id = :old_t');
            $ok = $upd->execute(['new_t' => $newTenantId, 'id' => $user_id, 'old_t' => $this->tenantId]);
            if (!$ok || $upd->rowCount() !== 1) {
                throw new RuntimeException('Failed to move user (concurrent modification?)');
            }

            $log = $this->pdo->prepare('INSERT INTO audit_logs (actor_user_id, entity, entity_id, action, meta, created_at) VALUES (:actor, :entity, :eid, :act, :meta, NOW())');
            $meta = json_encode(['from' => $this->tenantId, 'to' => $newTenantId, 'before' => $user]);
            $log->execute(['actor' => $actorUserId, 'entity' => 'users', 'eid' => $user_id, 'act' => 'transfer_tenant', 'meta' => $meta]);

            $this->pdo->commit();
            return true;
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}