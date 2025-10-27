<?php
// Cambia las variables de entorno para MariaDB
$host = getenv('MARIADB_HOST') ?: 'localhost';
$db   = getenv('MARIADB_DB') ?: 'ecomotiondb';
$user = getenv('MARIADB_USER') ?: 'admin';
$pass = getenv('MARIADB_PASSWORD') ?: '';
$charset = 'utf8';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
  $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
  die('Database connection failed: ' . $e->getMessage());
}