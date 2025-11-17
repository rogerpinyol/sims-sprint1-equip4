<?php

if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$tenantId = (int)($_SESSION['tenant_id'] ?? ($_GET['tenant_id'] ?? 0));
// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}

?>
<?php include __DIR__ . '/../partials/simpleHeader.php'; ?>
<div class="flex-grow flex items-center justify-center px-4 py-12">
    <div class="max-w-md w-full bg-white border border-slate-200 rounded-2xl p-8 shadow-sm">
      <div class="text-center mb-6">
  <img src="/images/logo.jpg" alt="<?= htmlspecialchars(__('common.logo_alt')) ?>" class="mx-auto w-12 h-12 rounded-full shadow" />
        <h1 class="text-2xl font-extrabold mt-3"><?= htmlspecialchars(__('auth.register.heading')) ?></h1>
        <p class="text-sm text-slate-500"><?= htmlspecialchars(__('auth.register.subtitle')) ?></p>
      </div>

      <?php if (!empty($errors)): ?>
        <div class="mb-4 rounded-md bg-red-50 border border-red-100 p-3 text-red-700 text-sm">
          <strong class="font-semibold"><?= htmlspecialchars(__('auth.register.fix_errors')) ?></strong>
          <ul class="mt-2 list-disc list-inside">
            <?php foreach($errors as $err) echo '<li>' . e($err) . '</li>'; ?>
          </ul>
        </div>
      <?php endif; ?>

  <form id="register-form" method="post" action="/register" class="space-y-4" data-validate="true" novalidate>
        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
        <input type="hidden" name="tenant_id" value="<?= e($tenantId) ?>">

        <div>
          <label class="block text-sm font-medium text-slate-700" for="name"><?= htmlspecialchars(__('form.full_name')) ?></label>
          <input id="name" name="name" value="<?= e(($old['name'] ?? ($_POST['name'] ?? ''))) ?>" required minlength="2" maxlength="80" pattern="^[A-Za-zÀ-ÿ ]+$" title="<?= htmlspecialchars(__('form.full_name_title')) ?>" autocomplete="name" class="input mt-1" placeholder="<?= htmlspecialchars(__('form.full_name_placeholder')) ?>" />
          <p id="name-error" class="invalid-feedback"></p>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700" for="email"><?= htmlspecialchars(__('form.work_email')) ?></label>
          <input id="email" name="email" type="email" value="<?= e(($old['email'] ?? ($_POST['email'] ?? ''))) ?>" required maxlength="120" autocomplete="email" class="input mt-1" placeholder="<?= htmlspecialchars(__('form.work_email_placeholder')) ?>" />
          <p id="email-error" class="invalid-feedback"></p>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700" for="password"><?= htmlspecialchars(__('form.password')) ?></label>
          <input id="password" type="password" name="password" required minlength="6" maxlength="128" autocomplete="new-password" class="input mt-1" placeholder="<?= htmlspecialchars(__('form.password_placeholder')) ?>" />
          <p id="password-error" class="invalid-feedback"></p>
        </div>

        <div>
          <button id="submit-btn" type="submit" class="btn btn-primary w-full disabled:opacity-50 disabled:cursor-not-allowed">
            <?= htmlspecialchars(__('auth.register.submit')) ?>
          </button>
        </div>
      </form>

      <p class="mt-4 text-center text-sm text-slate-500">
  <?= htmlspecialchars(__('auth.register.has_account')) ?> <a href="/auth/login" class="text-blue-600 hover:underline"><?= htmlspecialchars(__('auth.register.sign_in')) ?></a>
      </p>
      <p class="mt-3 text-center text-xs text-slate-400">
        <a href="/privacy" class="hover:underline"><?= htmlspecialchars(__('common.privacy')) ?></a> · <a href="/terms" class="hover:underline"><?= htmlspecialchars(__('common.terms')) ?></a>
      </p>
    </div>
  </div>

  <script src="/assets/js/validations/register.js" defer></script>
