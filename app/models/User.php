<?php

require_once __DIR__ . '/../core/Model.php';

class User extends Model {
    protected $table = 'users';
    protected $fillable = ['name', 'email', 'phone', 'accessibility_flags'];


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
}