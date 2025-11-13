<?php
// Super Admin - View Tenant Details
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Details - Super Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            colors: {
              'page-bg': '#C2B098',
              'login-bg': '#ffffff',
              'input-bg': '#FFE6CC',
              'input-border': '#1F2937',
              'input-text': '#000000',
              'input-focus': '#FFD8A8',
              'navbar-bg': '#191C21',
              'heading': '#FF7043', 
              'heading-hover': '#BF4519',
              'button-text-orange': '#DE541E',
            }
          }
        }
      }
    </script>
</head>
<body class="bg-page-bg min-h-screen">
    <div class="container mx-auto py-8">
        <a href="/admin/tenants" class="text-blue-600 hover:underline mb-4 inline-block">← Back to Tenants</a>
        <div class="bg-login-bg p-6 rounded shadow">
            <h1 class="text-3xl font-bold mb-6 text-heading"><?= htmlspecialchars($tenant['name']) ?></h1>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-input-text">ID</label>
                    <p class="mt-1 text-input-text"><?= htmlspecialchars($tenant['id']) ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-input-text">Name</label>
                    <p class="mt-1 text-input-text"><?= htmlspecialchars($tenant['name']) ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-input-text">Subdomain</label>
                    <p class="mt-1 text-input-text"><?= htmlspecialchars($tenant['subdomain']) ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-input-text">Plan Type</label>
                    <p class="mt-1 text-input-text"><?= htmlspecialchars($tenant['plan_type']) ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-input-text">Status</label>
                    <p class="mt-1">
                        <?php if ($tenant['is_active']): ?>
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">Active</span>
                        <?php else: ?>
                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs">Inactive</span>
                        <?php endif; ?>
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-input-text">Created At</label>
                    <p class="mt-1 text-input-text"><?= htmlspecialchars($tenant['created_at'] ?? 'N/A') ?></p>
                </div>
            </div>

            <div class="flex gap-2">
                <a href="/admin/tenants/edit.php?id=<?= urlencode($tenant['id']) ?>" class="bg-button-text-orange text-white px-4 py-2 rounded">Edit</a>
                <form method="POST" action="/admin/tenants/deactivate.php" style="display:inline;">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($tenant['id']) ?>">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded" onclick="return confirm('Deactivate this tenant?')">Deactivate</button>
                </form>
                <form method="POST" action="/admin/tenants/rotate_api_key.php" style="display:inline;">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($tenant['id']) ?>">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded" onclick="return confirm('Rotate API key for this tenant?')">Rotate API Key</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
