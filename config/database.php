<?php

class Database
{
    private static ?Database $instance = null;
    private \PDO $connection;

    private function __construct()
    {
        $host = getenv('MARIADB_HOST') ?: 'localhost';
        $db   = getenv('MARIADB_DB') ?: 'ecomotiondb';
        $user = getenv('MARIADB_USER') ?: 'admin';
        $pass = getenv('MARIADB_PASSWORD') ?: '';
        $charset = 'utf8mb4';

        $dsn = "mysql:host={$host};dbname={$db};charset={$charset}";
        $options = [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->connection = new \PDO($dsn, $user, $pass, $options);
        } catch (\PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection(): \PDO
    {
        return $this->connection;
    }
}

// Opcional: mantener $pdo global para código existente
$pdo = Database::getInstance()->getConnection();