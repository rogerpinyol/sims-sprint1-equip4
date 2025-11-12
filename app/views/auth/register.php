<?php

function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

$tenantId = (int)($_SESSION['tenant_id'] ?? ($_GET['tenant_id'] ?? 0));
// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}

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
<body class="bg-white text-slate-800 font-sans flex flex-col min-h-screen">
  <div class="flex-grow flex items-center justify-center px-4 py-12">
    <div class="max-w-md w-full bg-white border border-slate-200 rounded-2xl p-8 shadow-sm">
      <div class="text-center mb-6">
  <img src="/images/logo.jpg" alt="EcoMotion logo" class="mx-auto w-12 h-12 rounded-full shadow" />
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

  <form id="register-form" method="post" action="/register" class="space-y-4">
        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token'] ?? '') ?>">
        <input type="hidden" name="tenant_id" value="<?= e($tenantId) ?>">

        <div>
          <label class="block text-sm font-medium text-slate-700" for="name">Full name</label>
          <input
            id="name"
            name="name"
            value="<?= e(($old['name'] ?? ($_POST['name'] ?? ''))) ?>"
            required
            minlength="2"
            maxlength="80"
            pattern="^[A-Za-zÀ-ÿ ]+$"
            title="Only letters and spaces"
            autocomplete="name"
            class="mt-1 block w-full rounded-lg border border-slate-200 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
          <p id="name-error" class="mt-1 text-sm text-red-600 hidden"></p>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700" for="email">Work email</label>
          <input
            id="email"
            name="email"
            type="email"
            value="<?= e(($old['email'] ?? ($_POST['email'] ?? ''))) ?>"
            required
            maxlength="120"
            autocomplete="email"
            class="mt-1 block w-full rounded-lg border border-slate-200 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
          <p id="email-error" class="mt-1 text-sm text-red-600 hidden"></p>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700" for="password">Password</label>
          <input
            id="password"
            type="password"
            name="password"
            required
            minlength="6"
            maxlength="128"
            autocomplete="new-password"
            class="mt-1 block w-full rounded-lg border border-slate-200 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
          <p id="password-error" class="mt-1 text-sm text-red-600 hidden"></p>
        </div>

        <div>
          <button id="submit-btn" type="submit" class="w-full inline-flex justify-center items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
            Create account
          </button>
        </div>
      </form>

      <p class="mt-4 text-center text-sm text-slate-500">
  Already have an account? <a href="/auth/login" class="text-blue-600 hover:underline">Sign in</a>
      </p>
      <p class="mt-3 text-center text-xs text-slate-400">
        <a href="/privacy" class="hover:underline">Privacy</a> · <a href="/terms" class="hover:underline">Terms</a>
      </p>
    </div>
  </div>
  
</body>

  <script>
// Client-side validation
document.addEventListener('DOMContentLoaded', function() {
  var form = document.getElementById('register-form');
  if (!form) return;
  var name = form.querySelector('input[name="name"]');
  var email = form.querySelector('input[name="email"]');
  var pwd = form.querySelector('input[name="password"]');
  var nameErr = document.getElementById('name-error');
  var emailErr = document.getElementById('email-error');
  var pwdErr = document.getElementById('password-error');

  function validateName() {
    var nameVal = name.value.trim();
    if (!nameVal || nameVal.length < 2) {
      nameErr.textContent = 'Name must be at least 2 characters.';
      nameErr.classList.remove('hidden');
      return false;
    } else if (!/^[A-Za-zÀ-ÿ ]+$/.test(nameVal)) {
      nameErr.textContent = 'Name must contain only letters and spaces.';
      nameErr.classList.remove('hidden');
      return false;
    } else {
      nameErr.textContent = '';
      nameErr.classList.add('hidden');
      return true;
    }
  }

  function validateEmail() {
    if (!email.value || !email.checkValidity()) {
      emailErr.textContent = 'Enter a valid email address.';
      emailErr.classList.remove('hidden');
      return false;
    } else {
      emailErr.textContent = '';
      emailErr.classList.add('hidden');
      return true;
    }
  }

  function validatePassword() {
    var pwdVal = pwd.value;
    if (!pwdVal || pwdVal.length < 8
      || !/[A-Z]/.test(pwdVal)
      || !/[a-z]/.test(pwdVal)
      || !/\d/.test(pwdVal)
      || !/[^A-Za-z\d]/.test(pwdVal)) {
      pwdErr.textContent = 'Password must be at least 8 characters and include uppercase, lowercase, number, and symbol.';
      pwdErr.classList.remove('hidden');
      return false;
    } else {
      pwdErr.textContent = '';
      pwdErr.classList.add('hidden');
      return true;
    }
  }

  name.addEventListener('input', validateName);
  name.addEventListener('blur', validateName);
  email.addEventListener('input', validateEmail);
  email.addEventListener('blur', validateEmail);
  pwd.addEventListener('input', validatePassword);
  pwd.addEventListener('blur', validatePassword);

  
  form.addEventListener('submit', function(e) {
    var valid = validateName() & validateEmail() & validatePassword();
    if (!valid) e.preventDefault();
  });
});
  </script>
<?php include __DIR__ . '/../footer.php'; ?>
</body>
</html>
