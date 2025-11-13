<?php
// Tenant edit form (posts to REST update endpoint)
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(16)); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Tenant</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-page-bg min-h-screen">
  <div class="container mx-auto py-8">
    <a href="/admin/tenants" class="text-blue-600 hover:underline">← Back to Tenants</a>
    <h1 class="text-3xl font-bold my-4">Edit Tenant #<?= htmlspecialchars($tenant['id']) ?></h1>

    <form method="post" action="/admin/tenants/<?= urlencode($tenant['id']) ?>/update" class="bg-white rounded shadow p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

      <div>
        <label class="block text-sm font-medium">Name</label>
        <input class="input mt-1 w-full border border-slate-300 rounded px-3 py-2" type="text" name="name" value="<?= htmlspecialchars($tenant['name'] ?? '') ?>">
      </div>

      <div>
        <label class="block text-sm font-medium">Subdomain</label>
        <input class="input mt-1 w-full border border-slate-300 rounded px-3 py-2" type="text" name="subdomain" value="<?= htmlspecialchars($tenant['subdomain'] ?? '') ?>">
      </div>

      <div>
        <label class="block text-sm font-medium">Plan Type</label>
        <select name="plan_type" class="mt-1 w-full border border-slate-300 rounded px-3 py-2">
          <option value="standard" <?= (($tenant['plan_type'] ?? '')==='standard')?'selected':'' ?>>Standard</option>
          <option value="premium" <?= (($tenant['plan_type'] ?? '')==='premium')?'selected':'' ?>>Premium</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium">Active</label>
        <select name="is_active" class="mt-1 w-full border border-slate-300 rounded px-3 py-2">
          <option value="1" <?= !empty($tenant['is_active'])?'selected':'' ?>>Yes</option>
          <option value="0" <?= empty($tenant['is_active'])?'selected':'' ?>>No</option>
        </select>
      </div>

      <div class="md:col-span-2 flex justify-end gap-3 mt-2">
        <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded">Save</button>
        <a href="/admin/tenants/<?= urlencode($tenant['id']) ?>/view" class="px-6 py-2 rounded border">Cancel</a>
      </div>
    </form>
  </div>
</body>
</html>
