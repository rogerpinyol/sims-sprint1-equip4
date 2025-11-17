<?php
// Generic error view
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(I18n::locale()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars(__('error.title')) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            colors: {
              'page-bg': '#C2B098',
              'login-bg': '#ffffff',
              'heading': '#FF7043',
              'button-text-orange': '#DE541E',
            }
          }
        }
      }
    </script>
</head>
<body class="bg-page-bg min-h-screen">
    <div class="container mx-auto py-8">
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded shadow">
      <div class="flex justify-end mb-4">
        <?php include __DIR__ . '/../partials/lang-switcher.php'; ?>
      </div>
      <h1 class="text-2xl font-bold mb-2"><?= htmlspecialchars(__('error.title')) ?></h1>
      <p class="mb-4"><?= htmlspecialchars($errorMessage ?? __('error.default_message')) ?></p>
      <a href="/admin/tenants" class="bg-heading text-white px-4 py-2 rounded"><?= htmlspecialchars(__('error.back_to_tenants')) ?></a>
        </div>
    </div>
</body>
</html>
