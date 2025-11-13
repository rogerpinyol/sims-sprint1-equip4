<?php
// Super Admin Tenants Dashboard
// Requires: super_admin role
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenants Management - Super Admin</title>
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
    <?php if (isset($feedback) && $feedback): ?>
        <div class="mb-6 p-4 rounded shadow <?php echo $feedback['success'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
            <strong><?= htmlspecialchars($feedback['message']) ?></strong>
            <?php if (!empty($feedback['api_key'])): ?>
                <div class="mt-2 text-xs text-gray-700">API Key: <span class="font-mono bg-gray-200 px-2 py-1 rounded"><?= htmlspecialchars($feedback['api_key']) ?></span></div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <h1 class="text-3xl font-bold mb-6 text-heading hover:text-heading-hover">Tenants Management</h1>
        <div class="mb-8 flex justify-between items-center">
            <form method="GET" class="flex gap-2">
                <input type="text" name="search" placeholder="Search by name or subdomain" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" class="border border-input-border bg-input-bg text-input-text rounded px-3 py-2 focus:bg-input-focus" />
                <select name="plan_type" class="border border-input-border bg-input-bg text-input-text rounded px-3 py-2 focus:bg-input-focus">
                    <option value="" <?= (!isset($_GET['plan_type']) || $_GET['plan_type'] === '') ? 'selected' : '' ?>>All Plans</option>
                    <option value="standard" <?= (isset($_GET['plan_type']) && $_GET['plan_type'] === 'standard') ? 'selected' : '' ?>>Standard</option>
                    <option value="premium" <?= (isset($_GET['plan_type']) && $_GET['plan_type'] === 'premium') ? 'selected' : '' ?>>Premium</option>
                </select>
                <select name="is_active" class="border border-input-border bg-input-bg text-input-text rounded px-3 py-2 focus:bg-input-focus">
                    <option value="" <?= (!isset($_GET['is_active']) || $_GET['is_active'] === '') ? 'selected' : '' ?>>All Status</option>
                    <option value="1" <?= (isset($_GET['is_active']) && $_GET['is_active'] === '1') ? 'selected' : '' ?>>Active</option>
                    <option value="0" <?= (isset($_GET['is_active']) && $_GET['is_active'] === '0') ? 'selected' : '' ?>>Inactive</option>
                </select>
                <button type="submit" class="bg-button-text-orange text-white px-4 py-2 rounded">Filter</button>
            </form>
            <a href="#create" class="bg-heading text-white px-4 py-2 rounded">+ Create Tenant</a>
        </div>
        <!-- Tenants Table -->
    <div class="bg-login-bg shadow rounded-lg overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-input-focus">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subdomain</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Plan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($tenants)): ?>
                        <?php foreach ($tenants as $tenant): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-input-text"><?= htmlspecialchars($tenant['id']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-heading font-semibold"><?= htmlspecialchars($tenant['name']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-700"><?= htmlspecialchars($tenant['subdomain']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-input-text"><?= htmlspecialchars($tenant['plan_type']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <?php if ($tenant['is_active']): ?>
                                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">Active</span>
                                    <?php else: ?>
                                        <span class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm flex gap-2">
                                    <a href="/admin/tenants/show.php?id=<?= urlencode($tenant['id']) ?>" class="text-blue-600 hover:underline">View</a>
                                    <a href="/admin/tenants/edit.php?id=<?= urlencode($tenant['id']) ?>" class="text-yellow-600 hover:underline">Edit</a>
                                    <?php if ($tenant['is_active']): ?>
                                        <form method="POST" action="/admin/tenants/deactivate.php" style="display:inline;">
                                            <input type="hidden" name="id" value="<?= htmlspecialchars($tenant['id']) ?>">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                            <button type="submit" class="text-red-600 hover:underline" onclick="return confirm('Deactivate this tenant?')">Deactivate</button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" action="/admin/tenants/activate.php" style="display:inline;">
                                            <input type="hidden" name="id" value="<?= htmlspecialchars($tenant['id']) ?>">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                            <button type="submit" class="text-green-600 hover:underline" onclick="return confirm('Activate this tenant?')">Activate</button>
                                        </form>
                                    <?php endif; ?>
                                    <form method="POST" action="/admin/tenants/rotate_api_key.php" style="display:inline;">
                                        <input type="hidden" name="id" value="<?= htmlspecialchars($tenant['id']) ?>">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                        <button type="submit" class="text-indigo-600 hover:underline" onclick="return confirm('Rotate API key for this tenant?')">Rotate API Key</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">No tenants found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <!-- Pagination (example) -->
        <div class="mt-6 flex justify-end">
            <?php if (isset($pagination)): ?>
                <span class="text-sm text-gray-600">Showing <?= $pagination['limit'] ?> per page, offset <?= $pagination['offset'] ?></span>
            <?php endif; ?>
        </div>
        <!-- Create Tenant Modal/Section (placeholder) -->
    <div id="create" class="mt-10 bg-login-bg p-6 rounded shadow">
            <h2 class="text-xl font-bold mb-4 text-heading">Create New Tenant</h2>
            <form method="POST" action="/admin/tenants/create.php" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <div>
                    <label class="block text-sm font-medium text-input-text">Name</label>
                    <input type="text" name="name" required class="mt-1 block w-full border border-input-border bg-input-bg text-input-text rounded px-3 py-2 focus:bg-input-focus" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-input-text">Subdomain</label>
                    <input type="text" name="subdomain" required class="mt-1 block w-full border border-input-border bg-input-bg text-input-text rounded px-3 py-2 focus:bg-input-focus" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-input-text">Plan Type</label>
                    <select name="plan_type" class="mt-1 block w-full border border-input-border bg-input-bg text-input-text rounded px-3 py-2 focus:bg-input-focus">
                        <option value="standard">Standard</option>
                        <option value="premium">Premium</option>
                    </select>
                </div>
                <div class="md:col-span-2 flex justify-end items-center mt-4">
                    <button type="submit" class="bg-heading text-white px-6 py-2 rounded">Create Tenant</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
