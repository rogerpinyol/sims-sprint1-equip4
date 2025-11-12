<?php
// Expect $user array injected by controller
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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-white text-slate-800 font-sans flex flex-col min-h-screen">
  <div class="flex-grow flex items-center justify-center px-4 py-12">
    <div class="max-w-md w-full bg-white border border-slate-200 rounded-2xl p-8 shadow-sm">
      <a href="/client/dashboard" class="inline-block mb-4 text-blue-600 hover:text-blue-800 font-semibold">
        <i class="fas fa-arrow-left mr-2"></i>
      </a>
      <h1 class="text-2xl font-bold mb-6 text-center">Edit Profile</h1>
      <?php if (!empty($errors)): ?>
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
          <ul class="list-disc list-inside">
            <?php foreach ($errors as $error): ?>
              <li><?= e($error) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
          Profile updated successfully!
        </div>
      <?php endif; ?>
  <form method="post" action="/profile" class="space-y-4">
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
          <?php
          $phone_value = $user['phone'] ?? '';
          if (preg_match('/^\d{9}$/', $phone_value)) {
              $phone_value = substr($phone_value, 0, 3) . ' ' . substr($phone_value, 3, 3) . ' ' . substr($phone_value, 6);
          }
          ?>
          <input id="phone" name="phone" type="text" maxlength="11" value="<?= e($phone_value) ?>" class="mt-1 block w-full rounded-lg border border-slate-200 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
          <div id="phone-error" class="text-red-500 text-sm hidden mt-1"></div>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700" for="accessibility_flags">Accessibility</label>
          <?php
          $acc_value = $user['accessibility_flags'] ?? '';
          if (is_string($acc_value)) {
              $decoded = json_decode($acc_value, true);
              if (is_string($decoded) || is_null($decoded)) {
                  $acc_value = $decoded ?? '';
              }
          }
          ?>
          <textarea id="accessibility_flags" name="accessibility_flags" maxlength="255" class="mt-1 block w-full rounded-lg border border-slate-200 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" rows="3"><?php echo e($acc_value); ?></textarea>
        </div>
        <div class="flex items-center justify-between gap-3">
          <button type="submit" class="flex-1 py-2 px-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-75">Guardar cambios</button>
        </div>
      </form>
      <form id="delete-account" method="post" action="/profile/delete" onsubmit="return confirm('¿Seguro que quieres eliminar tu cuenta? Esta acción no se puede deshacer.');" class="mt-4">
        <a href="#" onclick="document.getElementById('delete-account').submit();" class="text-red-600 hover:text-red-800 font-semibold">Eliminar cuenta</a>
      </form>
    </div>
  </div>
  <?php include __DIR__ . '/../footer.php'; ?>
</body>
</html>
