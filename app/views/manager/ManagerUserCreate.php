<?php
// Simple create user form (manager scope). Assumes CSRF token already in session.
$csrf = $_SESSION['csrf_token'] ?? '';
?>
<div class="p-4 space-y-4">
  <h1 class="text-lg font-semibold">Create User</h1>
  <?php if (!empty($_SESSION['flash_errors'])): ?>
    <div class="text-red-600 text-sm">
      <ul class="list-disc pl-5">
        <?php foreach ($_SESSION['flash_errors'] as $err): ?>
          <li><?= e($err) ?></li>
        <?php endforeach; unset($_SESSION['flash_errors']); ?>
      </ul>
    </div>
  <?php endif; ?>
  <form method="post" action="/manager/users">
    <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>" />
    <div class="grid gap-4 max-w-md">
      <label class="flex flex-col text-sm">
        <span class="font-medium mb-1">Nombre</span>
        <input name="name" class="input" required minlength="2" />
      </label>
      <label class="flex flex-col text-sm">
        <span class="font-medium mb-1">Email</span>
        <input type="email" name="email" class="input" required />
      </label>
      <label class="flex flex-col text-sm">
        <span class="font-medium mb-1">Contraseña</span>
        <input type="password" name="password" class="input" required minlength="6" />
      </label>
      <label class="flex flex-col text-sm">
        <span class="font-medium mb-1">Teléfono</span>
        <input name="phone" class="input" />
      </label>
      <label class="flex flex-col text-sm">
        <span class="font-medium mb-1">Rol</span>
        <select name="role" class="input">
          <option value="client">Client</option>
          <option value="manager">Manager</option>
        </select>
      </label>
      <button class="btn-primary">Crear</button>
    </div>
  </form>
</div>
