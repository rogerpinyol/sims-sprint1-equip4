<?php
// Simple create user form (manager scope). Assumes CSRF token already in session.
$csrf = $_SESSION['csrf_token'] ?? '';
?>
<div class="p-4 space-y-4">
  <h1 class="text-lg font-semibold"><?= htmlspecialchars(__('manager.users.create.heading')) ?></h1>
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
        <span class="font-medium mb-1"><?= htmlspecialchars(__('manager.users.create.name_label')) ?></span>
        <input name="name" class="input" required minlength="2" />
      </label>
      <label class="flex flex-col text-sm">
        <span class="font-medium mb-1"><?= htmlspecialchars(__('manager.users.create.email_label')) ?></span>
        <input type="email" name="email" class="input" required />
      </label>
      <label class="flex flex-col text-sm">
        <span class="font-medium mb-1"><?= htmlspecialchars(__('manager.users.create.password_label')) ?></span>
        <input type="password" name="password" class="input" required minlength="6" />
      </label>
      <label class="flex flex-col text-sm">
        <span class="font-medium mb-1"><?= htmlspecialchars(__('manager.users.create.phone_label')) ?></span>
        <input name="phone" class="input" />
      </label>
      <label class="flex flex-col text-sm">
        <span class="font-medium mb-1"><?= htmlspecialchars(__('manager.users.create.role_label')) ?></span>
        <select name="role" class="input">
          <option value="client"><?= htmlspecialchars(__('manager.users.form.role.client')) ?></option>
          <option value="manager"><?= htmlspecialchars(__('manager.users.form.role.manager')) ?></option>
        </select>
      </label>
      <button class="btn-primary"><?= htmlspecialchars(__('manager.users.create.submit')) ?></button>
    </div>
  </form>
</div>
