<?php
// Integration test for Tenant model using MariaDB (test DB)
// Run: php tests/TenantModelMySqlTest.php
require_once __DIR__ . '/../app/models/Tenant.php';



// Load .env.test first, then .env for fallback
function loadEnv($file) {
  if (!is_readable($file)) return;
  foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    if (str_starts_with(trim($line), '#')) continue;
    if (!str_contains($line, '=')) continue;
    [$k, $v] = explode('=', $line, 2);
    $k = trim($k); $v = trim($v);
    if (getenv($k) === false) putenv("$k=$v");
  }
}
if (file_exists(__DIR__ . '/../.env.test')) loadEnv(__DIR__ . '/../.env.test');
if (file_exists(__DIR__ . '/../.env')) loadEnv(__DIR__ . '/../.env');

function env($k, $d = null) { $v = getenv($k); return $v !== false ? $v : $d; }


$user = env('MARIADB_USER', 'root');
$pass = env('MARIADB_PASSWORD', '');
$db   = env('MARIADB_DATABASE', 'ecomotiondb_test');

// Always use a dedicated test DB for safety
if ($db === 'ecomotiondb') {
  fwrite(STDERR, "\nERROR: Refusing to run tests against the main database. Set MARIADB_DATABASE to a test DB (e.g., ecomotiondb_test) in .env.test\n");
  exit(1);
}
$host = env('MARIADB_HOST', '127.0.0.1');
$port = (int)env('MARIADB_PORT', 3306);

$dsnNoDb = "mysql:host=$host;port=$port;charset=utf8mb4";
$options = [
  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

$adminPdo = new PDO($dsnNoDb, $user, $pass, $options);
$adminPdo->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

$pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass, $options);

// Load schema (idempotent) from config/init.sql into test DB
$schema = file_get_contents(__DIR__ . '/../config/init.sql');
foreach (array_filter(explode(';', $schema)) as $stmt) {
  $stmt = trim($stmt);
  if ($stmt === '') {
    continue;
  }
  try {
    $pdo->exec($stmt);
  } catch (PDOException $e) {
    if (!str_contains($e->getMessage(), 'already exists')) {
      throw $e;
    }
  }
}

// Basic assert helpers
function assert_true($cond, $msg) { if (!$cond) { throw new Exception("Assertion failed: $msg"); } }
function assert_equals($exp, $act, $msg) {
  if ($exp !== $act) {
    $e = var_export($exp, true); $a = var_export($act, true);
    throw new Exception("Assertion failed: $msg\nExpected: $e\nActual:   $a");
  }
}

// Clean start
$pdo->exec('SET FOREIGN_KEY_CHECKS=0');
foreach ([
  'incidences','settings','ads','partners','support_tickets','subscriptions','payments','maintenance','locations','bookings','vehicles','users','tenants'
] as $t) {
  $pdo->exec("TRUNCATE TABLE `$t`");
}
$pdo->exec('SET FOREIGN_KEY_CHECKS=1');

// Use injected PDO
$tenant = new Tenant($pdo);

// 1) Create tenant
$res = $tenant->createTenant(['name' => 'City A', 'subdomain' => 'city-a', 'plan_type' => 'premium']);
assert_true(is_array($res) && isset($res['id'], $res['api_key']), 'createTenant returns id and api_key');
$id = (int)$res['id']; $key1 = $res['api_key'];

$row = $pdo->query('SELECT * FROM tenants WHERE id = '.(int)$id)->fetch();
assert_true($row !== false, 'tenant inserted');
assert_true(password_verify($key1, $row['api_key']), 'api key stored as hash');

// 2) findById / findBySubdomain
$byId = $tenant->findById($id);
assert_equals('City A', $byId['name'], 'findById name');
$bySub = $tenant->findBySubdomain('city-a');
assert_true($bySub && (int)$bySub['id'] === $id, 'findBySubdomain works');

// 3) verifyApiKey
assert_true($tenant->verifyApiKey('city-a', $key1) === true, 'verify ok');
assert_true($tenant->verifyApiKey('city-a', 'bad') === false, 'verify bad');

// 4) rotateApiKey
$new = $tenant->rotateApiKey($id);
assert_true(is_array($new) && isset($new['api_key']), 'rotate returns key');
$key2 = $new['api_key'];
assert_true($tenant->verifyApiKey('city-a', $key2) === true, 'verify new ok');
assert_true($tenant->verifyApiKey('city-a', $key1) === false, 'old key invalid');

// 5) deactivate
assert_true($tenant->deactivateTenant($id) === true, 'deactivate ok');
assert_true($tenant->verifyApiKey('city-a', $key2) === false, 'inactive verify fails');

// 6) listTenants + filters
for ($i=1; $i<=3; $i++) {
  $tenant->createTenant([
    'name' => "T$i",
    'subdomain' => "t$i",
    'plan_type' => ($i % 2 === 0) ? 'standard' : 'enterprise',
  ]);
}
$all = $tenant->listTenants([], 10, 0);
assert_true(count($all) >= 3, 'listTenants returns rows');
$std = $tenant->listTenants(['plan_type' => 'standard'], 10, 0);
foreach ($std as $t) { assert_equals('standard', $t['plan_type'], 'plan_type filter'); }

// 7) invalid subdomain
assert_true($tenant->createTenant(['name' => 'Bad', 'subdomain' => '-bad-']) === false, 'reject invalid subdomain');

echo "TenantModelMySqlTest: OK\n";
