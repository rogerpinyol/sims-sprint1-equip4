<?php
declare(strict_types=1);

namespace Tests\Support;

use PDO;
use PDOException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

abstract class DatabaseTestCase extends TestCase
{
    protected static bool $databaseReady = false;
    protected static array $config = [];

    protected PDO $pdo;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        static::bootstrapEnv();
        static::ensureDatabase();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = $this->createConnection();
        $this->resetDatabase($this->pdo);
    }

    protected static function bootstrapEnv(): void
    {
        $root = dirname(__DIR__, 2);
        static::loadEnvFile($root . DIRECTORY_SEPARATOR . '.env.test');
        static::loadEnvFile($root . DIRECTORY_SEPARATOR . '.env');

        $database = static::env('MARIADB_DATABASE', static::env('MARIADB_DB', 'test_db'));
        if ($database === 'ecomotiondb') {
            throw new RuntimeException('Refusing to run tests against production database (ecomotiondb).');
        }

        static::$config = [
            'host' => static::env('MARIADB_HOST', '127.0.0.1'),
            'port' => (int) static::env('MARIADB_PORT', 3306),
            'database' => $database,
            'user' => static::env('MARIADB_USER', 'test'),
            'password' => static::env('MARIADB_PASSWORD', 'test'),
            'charset' => 'utf8mb4',
        ];

        putenv('MARIADB_HOST=' . static::$config['host']);
        putenv('MARIADB_PORT=' . (string) static::$config['port']);
        putenv('MARIADB_DATABASE=' . static::$config['database']);
        putenv('MARIADB_DB=' . static::$config['database']);
        putenv('MARIADB_USER=' . static::$config['user']);
        putenv('MARIADB_PASSWORD=' . static::$config['password']);

        $_ENV['MARIADB_HOST'] = static::$config['host'];
        $_ENV['MARIADB_PORT'] = (string) static::$config['port'];
        $_ENV['MARIADB_DATABASE'] = static::$config['database'];
        $_ENV['MARIADB_DB'] = static::$config['database'];
        $_ENV['MARIADB_USER'] = static::$config['user'];
        $_ENV['MARIADB_PASSWORD'] = static::$config['password'];
    }

    protected static function ensureDatabase(): void
    {
        if (static::$databaseReady) {
            return;
        }

        $cfg = static::$config;
        $dsn = sprintf('mysql:host=%s;port=%d;charset=%s', $cfg['host'], $cfg['port'], $cfg['charset']);

        try {
            $pdo = new PDO($dsn, $cfg['user'], $cfg['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            throw new RuntimeException('Unable to connect to MariaDB for testing: ' . $e->getMessage());
        }

        try {
            $pdo->exec(sprintf(
                'CREATE DATABASE IF NOT EXISTS `%s` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci',
                $cfg['database']
            ));
        } catch (PDOException $e) {
            $message = strtolower($e->getMessage());
            if (!str_contains($message, 'access denied')) {
                throw $e;
            }
        }

        static::$databaseReady = true;
    }

    protected function createConnection(): PDO
    {
        $cfg = static::$config;
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $cfg['host'],
            $cfg['port'],
            $cfg['database'],
            $cfg['charset']
        );

        try {
            return new PDO($dsn, $cfg['user'], $cfg['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            throw new RuntimeException('Unable to connect to MariaDB database: ' . $e->getMessage());
        }
    }

    protected function resetDatabase(PDO $pdo): void
    {
        $this->migrateSchema($pdo);

        $tables = [
            'incidences','settings','ads','partners','support_tickets','subscriptions','payments','maintenance','locations',
            'bookings','vehicles','users','tenants'
        ];

        $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
        foreach ($tables as $table) {
            $pdo->exec(sprintf('TRUNCATE TABLE `%s`', $table));
        }
        $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
    }

    protected function migrateSchema(PDO $pdo): void
    {
        $schemaPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'init.sql';
        if (!is_file($schemaPath)) {
            throw new RuntimeException('Schema file not found at ' . $schemaPath);
        }

        $sql = file_get_contents($schemaPath);
        if ($sql === false) {
            throw new RuntimeException('Unable to read schema file: ' . $schemaPath);
        }

        $statements = array_filter(array_map('trim', explode(';', $sql)));
        foreach ($statements as $statement) {
            if ($statement === '') {
                continue;
            }
            try {
                $pdo->exec($statement);
            } catch (PDOException $e) {
                if (stripos($e->getMessage(), 'already exists') === false) {
                    throw $e;
                }
            }
        }
    }

    protected static function loadEnvFile(string $file): void
    {
        if (!is_readable($file)) {
            return;
        }

        foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '#') || !str_contains($trimmed, '=')) {
                continue;
            }
            [$key, $value] = explode('=', $trimmed, 2);
            $key = trim($key);
            $value = trim($value);
            if ($key === '') {
                continue;
            }
            putenv($key . '=' . $value);
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }

    protected static function env(string $key, mixed $default = null): mixed
    {
        $value = getenv($key);
        return $value === false ? $default : $value;
    }

    protected function createTenant(string $name = 'Test Tenant', ?string $subdomain = null, string $plan = 'standard'): int
    {
        $subdomain ??= strtolower(preg_replace('/[^a-z0-9]+/', '-', $name)) . '-' . uniqid();
        $apiKey = bin2hex(random_bytes(16));

        $stmt = $this->pdo->prepare(
            'INSERT INTO tenants (name, subdomain, plan_type, api_key, created_at, is_active) VALUES (:name, :subdomain, :plan, :api, NOW(), 1)'
        );
        $stmt->execute([
            'name' => $name,
            'subdomain' => $subdomain,
            'plan' => $plan,
            'api' => $apiKey,
        ]);

        return (int) $this->pdo->lastInsertId();
    }
}
