<?php

class Database {
  private static ?Database $instance = null;
  private PDO $conn;

  private function __construct()
  {
    $host = getenv('MARIADB_HOST') ?: 'localhost';
    $db   = getenv('MARIADB_DB') ?: (getenv('MARIADB_DATABASE') ?: 'ecomotiondb');
    $user = getenv('MARIADB_USER') ?: 'admin';
    $pass = getenv('MARIADB_PASSWORD') ?: '';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
      PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES  => false,
    ];

    try {
      $this->conn = new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
      throw new RuntimeException('Database connection failed: ' . $e->getMessage());
    }
  }

  public static function getInstance(): Database
  {
    if (self::$instance === null) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  public function getConnection(): PDO
  {
    return $this->conn;
  }
}

if (!isset($pdo)) {
  $pdo = Database::getInstance()->getConnection();
}