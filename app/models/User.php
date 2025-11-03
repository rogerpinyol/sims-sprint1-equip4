<?php

require_once __DIR__ . '/../core/Model.php';

class User extends Model {
    protected $table = 'users';
    protected $fillable = ['name', 'email', 'phone', 'accessibility_flags', 'password_hash', 'role'];


// Functions
    public function __construct(int $tenantId) {
        parent::__construct('users', $tenantId);
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


    public function getAll(): array|false {
    return $this->find();
    }


    public function updateDetails(int $user_id, array $data) {
        
        unset($data['role'], $data['password_hash']);
        
        $allowed = ['name' => true, 'email' => true, 'phone' => true, 'accessibility_flags' => true];
        $data = array_intersect_key($data, $allowed);
        
        if (isset($data['accessibility_flags']) && is_array($data['accessibility_flags'])) {
            $data['accessibility_flags'] = json_encode($data['accessibility_flags']);
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

        return $this->update($user_id, $data);
    }

    public function changePassword(int $user_id, string $plain_password) {
        $hash = password_hash($plain_password, PASSWORD_BCRYPT);
        return $this->update($user_id, ['password_hash' => $hash]);
    }

    /**
     * Transfer a user to another tenant in a controlled way.
     * Performs checks (target tenant exists, email uniqueness) and writes an audit log.
     */
    public function transferToTenant(int $user_id, int $newTenantId, int $actorUserId = 0): bool {
        if ($user_id <= 0 || $newTenantId <= 0) {
            throw new InvalidArgumentException('Invalid ids');
        }

        // Ensure target tenant exists
        $stmt = $this->pdo->prepare('SELECT id FROM tenants WHERE id = :id');
        $stmt->execute(['id' => $newTenantId]);
        if ($stmt->fetchColumn() === false) {
            throw new RuntimeException('Target tenant not found');
        }

        // Load current user and ensure exists and belongs to current tenant
        $rows = $this->find(['id' => $user_id]);
        if (empty($rows)) throw new RuntimeException('User not found');
        $user = $rows[0];

        // Check email uniqueness in target tenant
        if (!empty($user['email'])) {
            $chk = $this->pdo->prepare('SELECT id FROM users WHERE email = :email AND tenant_id = :tenant_id');
            $chk->execute(['email' => $user['email'], 'tenant_id' => $newTenantId]);
            if ($chk->fetchColumn() !== false) {
                throw new RuntimeException('Email conflict in target tenant');
            }
        }

        // Begin transaction
        $this->pdo->beginTransaction();
        try {
            $upd = $this->pdo->prepare('UPDATE `users` SET tenant_id = :new_t WHERE id = :id AND tenant_id = :old_t');
            $ok = $upd->execute(['new_t' => $newTenantId, 'id' => $user_id, 'old_t' => $this->tenantId]);
            if (!$ok || $upd->rowCount() !== 1) {
                throw new RuntimeException('Failed to move user (concurrent modification?)');
            }

            // Insert audit log (create table audit_logs if necessary is left to DB migration)
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