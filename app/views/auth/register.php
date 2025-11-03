<?php
// Variables from controller: $errors (array), $created (assoc) optionally
function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$tenantId = (int)($_SESSION['tenant_id'] ?? ($_GET['tenant_id'] ?? 0));
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Register — EcoMotion</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>html{scroll-behavior:smooth}</style>
</head>
<body class="bg-white text-slate-800 font-sans">
  <div class="min-h-screen flex items-center justify-center px-4 py-12">
    <div class="max-w-md w-full bg-white border border-slate-200 rounded-2xl p-8 shadow-sm">
      <div class="text-center mb-6">
        <svg class="mx-auto w-10 h-10 text-brand-600" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l3 6 6 .9-4.5 4.4L17 20l-5-2.7L7 20l1.5-6.7L4 8.9 10 8l2-6z"></path></svg>
        <h1 class="text-2xl font-extrabold mt-3">Create your account</h1>
        <p class="text-sm text-slate-500">Start your free trial — no credit card required</p>
      </div>

      <?php if (!empty($errors)): ?>
        <div class="mb-4 rounded-md bg-red-50 border border-red-100 p-3 text-red-700 text-sm">
          <strong class="font-semibold">Please fix the following:</strong>
          <ul class="mt-2 list-disc list-inside">
            <?php foreach($errors as $err) echo '<li>' . e($err) . '</li>'; ?>
          </ul>
        </div>
      <?php endif; ?>

      <?php if (!empty($created)): ?>
        <div class="mb-4 rounded-md bg-emerald-50 border border-emerald-100 p-3 text-emerald-700 text-sm">
          Account created successfully. ID: <?= e($created['id'] ?? '') ?>
        </div>
      <?php endif; ?>

      <form method="post" action="/register" class="space-y-4">
        <input type="hidden" name="tenant_id" value="<?= e($tenantId) ?>">

        <div>
          <label class="block text-sm font-medium text-slate-700">Full name</label>
          <input name="name" value="<?= e($_POST['name'] ?? '') ?>" required class="mt-1 block w-full rounded-lg border border-slate-200 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500" />
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700">Work email</label>
          <input name="email" value="<?= e($_POST['email'] ?? '') ?>" type="email" required class="mt-1 block w-full rounded-lg border border-slate-200 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500" />
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700">Password</label>
          <input type="password" name="password" required class="mt-1 block w-full rounded-lg border border-slate-200 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500" />
        </div>

        <div>
          <button type="submit" class="w-full inline-flex justify-center items-center gap-2 px-4 py-2 rounded-lg bg-brand-600 text-white font-medium hover:bg-brand-700">Create account</button>
        </div>
      </form>

      <p class="mt-4 text-center text-sm text-slate-500">Already have an account? <a href="/login" class="text-brand-600 hover:underline">Sign in</a></p>
      <p class="mt-3 text-center text-xs text-slate-400"><a href="/privacy" class="hover:underline">Privacy</a> · <a href="/terms" class="hover:underline">Terms</a></p>
    </div>
  </div>
</body>
</html>
