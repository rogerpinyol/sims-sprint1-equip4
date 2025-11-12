<?php

if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(16)); }
$errors = $errors ?? [];
$old = $old ?? [];
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Manager Login — EcoMotion</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white text-slate-800 font-sans flex flex-col min-h-screen">
    <div class="flex-grow flex items-center justify-center px-4 py-12">
        <div class="max-w-md w-full bg-white border border-slate-200 rounded-2xl p-8 shadow-sm">
            <div class="text-center mb-6">
                   <img src="/images/logo.jpg" alt="EcoMotion logo" class="mx-auto w-12 h-12 rounded-full shadow" />
                <h1 class="text-2xl font-extrabold mt-3">Manager Sign in</h1>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="mb-4 rounded-md bg-red-50 border border-red-100 p-3 text-red-700 text-sm">
                    <ul class="list-disc list-inside">
                        <?php foreach($errors as $err) echo '<li>' . e($err) . '</li>'; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="mb-4 rounded-md bg-green-50 border border-green-100 p-3 text-green-700 text-sm">
                    Account created successfully! You can now sign in.
                </div>
            <?php endif; ?>

            <form id="login-form" method="post" action="/manager/login" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

                <div>
                    <label class="block text-sm font-medium text-slate-700" for="email">Email</label>
                    <input id="email" name="email" type="email" value="<?= e($old['email'] ?? '') ?>" required class="mt-1 block w-full rounded-lg border border-slate-200 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    <p id="email-error" class="mt-1 text-sm text-red-600 hidden"></p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700" for="password">Password</label>
                    <input id="password" name="password" type="password" required class="mt-1 block w-full rounded-lg border border-slate-200 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    <p id="password-error" class="mt-1 text-sm text-red-600 hidden"></p>
                </div>

                <div>
                    <button type="submit" class="w-full inline-flex justify-center items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white font-medium hover:bg-blue-700">Sign in</button>
                </div>
            </form>

        </div>
    </div>
</body>
<script>

// Client-side validation
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('login-form');
    if (!form) return;
    var email = form.querySelector('input[name="email"]');
    var pwd = form.querySelector('input[name="password"]');
    var emailErr = document.getElementById('email-error');
    var pwdErr = document.getElementById('password-error');

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
        if (!pwd.value) {
            pwdErr.textContent = 'Password is required.';
            pwdErr.classList.remove('hidden');
            return false;
        } else {
            pwdErr.textContent = '';
            pwdErr.classList.add('hidden');
            return true;
        }
    }

    email.addEventListener('input', validateEmail);
    email.addEventListener('blur', validateEmail);
    pwd.addEventListener('input', validatePassword);
    pwd.addEventListener('blur', validatePassword);

    form.addEventListener('submit', function(e) {
        var valid = validateEmail() & validatePassword();
        if (!valid) e.preventDefault();
    });
});
</script>
<?php include __DIR__ . '/../footer.php'; ?>
</body>
</html>