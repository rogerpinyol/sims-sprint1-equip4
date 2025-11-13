<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(16)); }
$errors = $errors ?? [];
$old = $old ?? [];
?>
<?php include __DIR__ . '/../partials/simpleHeader.php'; ?>
<div class="flex-grow flex items-center justify-center px-4 py-12">
    <div class="max-w-md w-full bg-white border border-slate-200 rounded-2xl p-8 shadow-sm">
        <div class="text-center mb-6">
            <img src="/images/logo.jpg" alt="EcoMotion logo" class="mx-auto w-12 h-12 rounded-full shadow" />
            <h1 class="text-2xl font-extrabold mt-3">Tenant Admin Sign in</h1>
        </div>
        <?php if (!empty($errors)): ?>
            <div class="mb-4 rounded-md bg-red-50 border border-red-100 p-3 text-red-700 text-sm">
                <ul class="list-disc list-inside">
                    <?php foreach($errors as $err) echo '<li>' . e($err) . '</li>'; ?>
                </ul>
            </div>
        <?php endif; ?>
        <form id="sa-login-form" method="post" action="/admin/login" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
            <div>
                <label class="block text-sm font-medium text-slate-700" for="email">Email</label>
                <input id="email" name="email" type="email" value="<?= e($old['email'] ?? '') ?>" required class="input mt-1" />
                <p id="email-error" class="mt-1 text-sm text-red-600 hidden"></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700" for="password">Password</label>
                <input id="password" name="password" type="password" required class="input mt-1" />
                <p id="password-error" class="mt-1 text-sm text-red-600 hidden"></p>
            </div>
            <div>
                <button type="submit" class="btn btn-primary w-full">Sign in</button>
            </div>
        </form>
    </div>
</div>
<script>
(function(){
  var form = document.getElementById('sa-login-form');
  if(!form) return;
  var email = form.querySelector('input[name="email"]');
  var pwd = form.querySelector('input[name="password"]');
  var emailErr = document.getElementById('email-error');
  var pwdErr = document.getElementById('password-error');
  function ve(){
    if(!email.value || !email.checkValidity()) { emailErr.textContent='Enter a valid email.'; emailErr.classList.remove('hidden'); return false; }
    emailErr.textContent=''; emailErr.classList.add('hidden'); return true;
  }
  function vp(){
    if(!pwd.value){ pwdErr.textContent='Password required.'; pwdErr.classList.remove('hidden'); return false; }
    pwdErr.textContent=''; pwdErr.classList.add('hidden'); return true;
  }
  email.addEventListener('input', ve); email.addEventListener('blur', ve);
  pwd.addEventListener('input', vp); pwd.addEventListener('blur', vp);
  form.addEventListener('submit', function(e){ if(!(ve() & vp())) e.preventDefault(); });
})();
</script>
