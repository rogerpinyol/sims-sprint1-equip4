<?php
// Super Admin - Edit Tenant
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Tenant - Super Admin</title>
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
            <h1 class="text-3xl font-bold mb-6 text-heading">Edit Tenant</h1>
            
            <form method="POST" action="/admin/tenants/update.php" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="hidden" name="id" value="<?= htmlspecialchars($tenant['id']) ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                
                <div>
                    <label class="block text-sm font-medium text-input-text">Name</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($tenant['name']) ?>" class="mt-1 block w-full border border-input-border bg-input-bg text-input-text rounded px-3 py-2 focus:bg-input-focus" />
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-input-text">Subdomain</label>
                    <input type="text" name="subdomain" value="<?= htmlspecialchars($tenant['subdomain']) ?>" class="mt-1 block w-full border border-input-border bg-input-bg text-input-text rounded px-3 py-2 focus:bg-input-focus" />
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-input-text">Plan Type</label>
                    <select name="plan_type" class="mt-1 block w-full border border-input-border bg-input-bg text-input-text rounded px-3 py-2 focus:bg-input-focus">
                        <option value="standard" <?= $tenant['plan_type'] === 'standard' ? 'selected' : '' ?>>Standard</option>
                        <option value="premium" <?= $tenant['plan_type'] === 'premium' ? 'selected' : '' ?>>Premium</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-input-text">Status</label>
                    <select name="is_active" class="mt-1 block w-full border border-input-border bg-input-bg text-input-text rounded px-3 py-2 focus:bg-input-focus">
                        <option value="1" <?= $tenant['is_active'] ? 'selected' : '' ?>>Active</option>
                        <option value="0" <?= !$tenant['is_active'] ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>

                <div class="md:col-span-2 flex gap-2 justify-end mt-4">
                    <a href="/admin/tenants" class="bg-gray-500 text-white px-6 py-2 rounded">Cancel</a>
                    <button type="submit" class="bg-heading text-white px-6 py-2 rounded">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
