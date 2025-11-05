<?php
// Expect $user array injected by controller
function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
$user = $user ?? [];
$success = $success ?? false;
$errors = $errors ?? [];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Edit Profile</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white text-slate-800 font-sans">
  <div class="min-h-screen flex items-center justify-center px-4 py-12">
    <div class="max-w-md w-full bg-white border border-slate-200 rounded-2xl p-8 shadow-sm">
      <h1 class="text-2xl font-bold mb-6 text-center">Edit Profile</h1>
      <form method="post" action="/profile/update" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-slate-700" for="name">Full name</label>
          <input id="name" name="name" type="text" required minlength="2" maxlength="80" value="<?= e($user['name'] ?? '') ?>" class="mt-1 block w-full rounded-lg border border-slate-200 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700" for="email">Email</label>
          <input id="email" name="email" type="email" required maxlength="120" value="<?= e($user['email'] ?? '') ?>" class="mt-1 block w-full rounded-lg border border-slate-200 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700" for="phone">Phone</label>
          <input id="phone" name="phone" type="text" maxlength="30" value="<?= e($user['phone'] ?? '') ?>" class="mt-1 block w-full rounded-lg border border-slate-200 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700" for="accessibility_flags">Accessibility</label>
          <input id="accessibility_flags" name="accessibility_flags" type="text" maxlength="255" value="<?= e($user['accessibility_flags'] ?? '') ?>" class="mt-1 block w-full rounded-lg border border-slate-200 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
        </div>
        <div>
          <button type="submit" class="w-full py-2 px-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-75">Save changes</button>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
