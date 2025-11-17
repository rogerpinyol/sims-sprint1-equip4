<!DOCTYPE html>
<html lang="<?= htmlspecialchars(I18n::locale()) ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title><?= htmlspecialchars(__('home.title')) ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>body{font-family:Arial,sans-serif}</style>
</head>
<body class="max-w-3xl mx-auto my-12 p-6">
  <div class="flex justify-end mb-4">
    <?php include __DIR__ . '/partials/lang-switcher.php'; ?>
  </div>
  <h1 class="text-3xl font-bold mb-4"><?= htmlspecialchars(__('home.heading')) ?></h1>
  <p class="mb-4"><?= htmlspecialchars(__('home.description')) ?></p>
  <ul class="list-disc pl-5">
    <li><a class="text-indigo-600 hover:underline" href="/index.php/admin/tenants"><?= htmlspecialchars(__('home.link.super_admin_dashboard')) ?></a></li>
  </ul>
</body>
</html>
