<?php
// Variables from controller: $errors (array), $created (assoc) optionally
function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$tenantId = (int)($_SESSION['tenant_id'] ?? ($_GET['tenant_id'] ?? 0));
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Create user</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>body{font-family:system-ui,Arial,sans-serif;padding:18px} .err{color:#b91c1c}</style>
</head>
<body>
  <h1>Create user</h1>

  <?php if (!empty($errors)): ?>
    <div class="err"><strong>Errors:</strong>
      <ul><?php foreach($errors as $err) echo '<li>' . e($err) . '</li>'; ?></ul>
    </div>
  <?php endif; ?>

  <form method="post" action="/users">
    <input type="hidden" name="tenant_id" value="<?= e($tenantId) ?>">
    <label>Name<br><input name="name" value="<?= e($_POST['name'] ?? '') ?>" required></label><br><br>
    <label>Email<br><input name="email" value="<?= e($_POST['email'] ?? '') ?>" required></label><br><br>
    <label>Password<br><input type="password" name="password" required></label><br><br>
    <label>Role (optional)<br><input name="role" value="<?= e($_POST['role'] ?? '') ?>"></label><br><br>
    <button type="submit">Create</button>
  </form>

  <?php if (!empty($created)): ?>
    <hr style="margin:20px 0">
    <h2>Created user</h2>
    <table border="0" cellpadding="6">
      <tr><th>ID</th><td><?= e($created['id'] ?? '') ?></td></tr>
      <tr><th>Name</th><td><?= e($created['name'] ?? '') ?></td></tr>
      <tr><th>Email</th><td><?= e($created['email'] ?? '') ?></td></tr>
      <tr><th>Role</th><td><?= e($created['role'] ?? '') ?></td></tr>
      <tr><th>Created at</th><td><?= e($created['created_at'] ?? '') ?></td></tr>
    </table>
  <?php endif; ?>

  <p><a href="/app/views/users/index.php">Back to list</a></p>
</body>
</html>
