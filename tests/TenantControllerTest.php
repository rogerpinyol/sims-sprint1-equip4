<?php
// Integration tests for TenantController using MariaDB test database
// Run: php tests/TenantControllerTest.php

require_once __DIR__ . '/../app/controllers/TenantController.php';

function loadEnvFile($file)
{
    if (!is_readable($file)) return;
    foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $k = trim($k); $v = trim($v);
        if ($k !== '' && getenv($k) === false) {
            putenv("$k=$v");
        }
    }
}

loadEnvFile(__DIR__ . '/../.env.test');
loadEnvFile(__DIR__ . '/../.env');

function env_or_default($key, $default = null)
{
    $value = getenv($key);
    return $value !== false ? $value : $default;
}

$user = env_or_default('MARIADB_USER', 'root');
$pass = env_or_default('MARIADB_PASSWORD', '');
$db   = env_or_default('MARIADB_DATABASE', 'ecomotiondb_test');
$host = env_or_default('MARIADB_HOST', '127.0.0.1');
$port = (int)env_or_default('MARIADB_PORT', 3306);

if ($db === 'ecomotiondb') {
    fwrite(STDERR, "\nERROR: Refusing to run tests against the main database. Set MARIADB_DATABASE to a test DB in .env.test\n");
    exit(1);
}

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

$dsnNoDb = "mysql:host=$host;port=$port;charset=utf8mb4";
$adminPdo = new PDO($dsnNoDb, $user, $pass, $options);
$adminPdo->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

$pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass, $options);

$schema = file_get_contents(__DIR__ . '/../config/init.sql');
foreach (array_filter(explode(';', $schema)) as $statement) {
    $statement = trim($statement);
    if ($statement === '') {
        continue;
    }
    try {
        $pdo->exec($statement);
    } catch (PDOException $e) {
        if (!str_contains($e->getMessage(), 'already exists')) {
            throw $e;
        }
    }
}

$pdo->exec('SET FOREIGN_KEY_CHECKS=0');
foreach ([
    'incidences','settings','ads','partners','support_tickets','subscriptions','payments','maintenance','locations','bookings','vehicles','users','tenants'
] as $table) {
    $pdo->exec("TRUNCATE TABLE `$table`");
}
$pdo->exec('SET FOREIGN_KEY_CHECKS=1');

function assert_true($condition, $message)
{
    if (!$condition) {
        throw new Exception("Assertion failed: $message");
    }
}

function assert_equals($expected, $actual, $message)
{
    if ($expected !== $actual) {
        $e = var_export($expected, true);
        $a = var_export($actual, true);
        throw new Exception("Assertion failed: $message\nExpected: $e\nActual:   $a");
    }
}

if (session_status() === PHP_SESSION_ACTIVE) {
    $_SESSION = [];
} else {
    session_start();
}

$_SESSION['user'] = [
    'id' => 1,
    'role' => 'tenant_admin',
];

$controller = new TenantController($pdo);

// Create tenant
$createResponse = $controller->store([
    'name' => 'City Alpha',
    'subdomain' => 'city-alpha',
    'plan_type' => 'premium',
]);
assert_equals(201, $createResponse['status'], 'Store should return 201');
assert_true(isset($createResponse['body']['api_key']), 'API key should be returned');
$tenantId = $createResponse['body']['data']['id'];
$currentKey = $createResponse['body']['api_key'];

// Index should list tenant
$listResponse = $controller->index();
assert_equals(200, $listResponse['status'], 'Index should return 200');
assert_true(count($listResponse['body']['data']) >= 1, 'Index should list tenants');

// Show should return tenant
$showResponse = $controller->show($tenantId);
assert_equals('City Alpha', $showResponse['body']['data']['name'], 'Show should return tenant name');

// Verify API key success
$verifyOk = $controller->verifyApiKey('city-alpha', $currentKey);
assert_equals(200, $verifyOk['status'], 'Verify with correct key should return 200');
assert_true($verifyOk['body']['data']['valid'], 'Verify should be true');

// Update tenant name
$updateResponse = $controller->update($tenantId, ['name' => 'City Beta']);
assert_equals(200, $updateResponse['status'], 'Update should return 200');
assert_equals('City Beta', $updateResponse['body']['data']['name'], 'Name should be updated');

// Rotate API key
$rotateResponse = $controller->rotateApiKey($tenantId);
assert_equals(200, $rotateResponse['status'], 'Rotate should return 200');
$newKey = $rotateResponse['body']['api_key'];
assert_true(is_string($newKey) && strlen($newKey) >= 32, 'New API key should be returned');

// Old key should fail, new key should pass
$verifyOld = $controller->verifyApiKey('city-alpha', $currentKey);
assert_equals(401, $verifyOld['status'], 'Old key should fail with 401');
assert_true(!$verifyOld['body']['data']['valid'], 'Old key should be invalid');

$verifyNew = $controller->verifyApiKey('city-alpha', $newKey);
assert_equals(200, $verifyNew['status'], 'New key should verify');
assert_true($verifyNew['body']['data']['valid'], 'New key should be valid');

// Deactivate tenant and ensure verify fails
$deactivateResponse = $controller->deactivate($tenantId);
assert_equals(200, $deactivateResponse['status'], 'Deactivate should return 200');
$verifyInactive = $controller->verifyApiKey('city-alpha', $newKey);
assert_equals(401, $verifyInactive['status'], 'Deactivated tenant should not verify');

// Unauthorized access test
$_SESSION['user']['role'] = 'manager';
$failed = false;
try {
    $controller->store([
        'name' => 'City Gamma',
        'subdomain' => 'city-gamma',
    ]);
} catch (HttpException $e) {
    $failed = true;
    assert_equals(403, $e->getStatusCode(), 'Managers cannot create tenants');
}
assert_true($failed, 'Unauthorized store should throw HttpException');

// Missing tenant test
$_SESSION['user']['role'] = 'tenant_admin';
$notFound = false;
try {
    $controller->show(9999);
} catch (HttpException $e) {
    $notFound = true;
    assert_equals(404, $e->getStatusCode(), 'Show on missing tenant should 404');
}
assert_true($notFound, 'Missing tenant should throw HttpException');

echo "TenantControllerTest: OK\n";
