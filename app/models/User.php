<?php

require_once __DIR__ . '/../config/Database.php';

class User {
    private $db;
    private $table = 'users';

    public $id;
    public $name;
    public $role;
    public $email;
    public $phone;
    public $accessibility_flags;
    public $is_active;
    public $created_at;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }


    public function getAllByTenant($tenant_id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE tenant_id = ?");
        $stmt->execute([$tenant_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function register($tenant_id, $name, $email, $password_hash) {
        $stmt = $this->db->prepare(
            "INSERT INTO users (tenant_id, name, email, password_hash) VALUES (?, ?, ?, ?)"
        );
        return $stmt->execute([$tenant_id, $name, $email, $password_hash]);
    }

    public function updateDetails($user_id, $name, $email, $phone, $accessibility_flags) {
        $stmt = $this->db->prepare(
            "UPDATE users SET name = ?, email = ?, phone = ?, accessibility_flags = ? WHERE id = ?"
        );
        return $stmt->execute([
            $name,
            $email,
            $phone,
            json_encode($accessibility_flags),
            $user_id
        ]);
    }}