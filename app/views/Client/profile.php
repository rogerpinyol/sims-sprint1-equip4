<?php
// Expect $user array injected by controller
$user = $user ?? [];
$success = $success ?? false;
$errors = $errors ?? [];
?>

<?php include __DIR__ . '/../partials/simpleHeader.php'; ?>
<div class="flex-grow flex items-center justify-center px-4 py-12">
  <div class="max-w-md w-full bg-white border border-slate-200 rounded-2xl p-8 shadow-sm">
    <div class="text-center mb-6">
      <img src="/images/logo.jpg" alt="EcoMotion logo" class="mx-auto w-12 h-12 rounded-full shadow" />
      <h1 class="text-2xl font-extrabold mt-3">Edit Profile</h1>
    </div>

    <?php if (!empty($errors)): ?>
      <div class="mb-4 rounded-md bg-red-50 border border-red-100 p-3 text-red-700 text-sm">
        <ul class="list-disc list-inside">
          <?php foreach ($errors as $error): ?>
            <li><?= e($error) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="mb-4 rounded-md bg-green-50 border border-green-100 p-3 text-green-700 text-sm">
        Profile updated successfully!
      </div>
    <?php endif; ?>

    <?php
      $phone_value = $user['phone'] ?? '';
      if (preg_match('/^\d{9}$/', $phone_value)) {
        $phone_value = substr($phone_value, 0, 3) . ' ' . substr($phone_value, 3, 3) . ' ' . substr($phone_value, 6);
      }
      $acc_value = $user['accessibility_flags'] ?? '';
      if (is_string($acc_value)) {
        $decoded = json_decode($acc_value, true);
        if (is_string($decoded) || is_null($decoded)) { $acc_value = $decoded ?? ''; }
      }
    ?>

    <form method="post" action="/profile" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-slate-700" for="name">Full name</label>
        <input id="name" name="name" type="text" required minlength="2" maxlength="80" value="<?= e($user['name'] ?? '') ?>" class="input mt-1" />
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700" for="email">Email</label>
        <input id="email" name="email" type="email" required maxlength="120" value="<?= e($user['email'] ?? '') ?>" class="input mt-1" />
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700" for="phone">Phone</label>
        <input id="phone" name="phone" type="text" maxlength="11" value="<?= e($phone_value) ?>" class="input mt-1" />
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700" for="accessibility_flags">Accessibility</label>
        <textarea id="accessibility_flags" name="accessibility_flags" maxlength="255" class="input mt-1" rows="3"><?php echo e($acc_value); ?></textarea>
      </div>
      <div>
        <button type="submit" class="btn btn-primary w-full">Guardar cambios</button>
      </div>
    </form>

    <form id="delete-account" method="post" action="/profile/delete" onsubmit="return confirm('¿Seguro que quieres eliminar tu cuenta? Esta acción no se puede deshacer.');" class="mt-4 text-center">
      <a href="#" onclick="document.getElementById('delete-account').submit();" class="text-red-600 hover:text-red-700 text-sm font-medium">Eliminar cuenta</a>
    </form>
  </div>
</div>
