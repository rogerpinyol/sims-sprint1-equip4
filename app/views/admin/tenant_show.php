<?php
// Tenant detail view (read-only)
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(I18n::locale()) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars(__('admin.tenant_show.meta_title')) ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-page-bg min-h-screen">
  <div class="container mx-auto py-8">
    <a href="/admin/tenants" class="text-blue-600 hover:underline"><?= htmlspecialchars(__('admin.tenant_show.back_to_tenants')) ?></a>
    <h1 class="text-3xl font-bold my-4"><?= htmlspecialchars(__('admin.tenant_show.heading', ['id' => (string)($tenant['id'] ?? '')])) ?></h1>
    <div class="bg-white rounded shadow p-6 space-y-3">
      <div><span class="font-semibold"><?= htmlspecialchars(__('admin.tenant_show.name')) ?></span> <?= htmlspecialchars($tenant['name'] ?? '') ?></div>
      <div><span class="font-semibold"><?= htmlspecialchars(__('admin.tenant_show.subdomain')) ?></span> <?= htmlspecialchars($tenant['subdomain'] ?? '') ?></div>
      <div><span class="font-semibold"><?= htmlspecialchars(__('admin.tenant_show.plan')) ?></span> <?= htmlspecialchars($tenant['plan_type'] ?? '') ?></div>
      <div><span class="font-semibold"><?= htmlspecialchars(__('admin.tenant_show.active')) ?></span> <?= !empty($tenant['is_active']) ? htmlspecialchars(__('admin.tenant_show.active_yes')) : htmlspecialchars(__('admin.tenant_show.active_no')) ?></div>
      <div><span class="font-semibold"><?= htmlspecialchars(__('admin.tenant_show.created')) ?></span> <?= htmlspecialchars($tenant['created_at'] ?? '') ?></div>
    </div>
    <div class="mt-6 flex gap-3">
      <a class="bg-yellow-600 text-white px-4 py-2 rounded" href="/admin/tenants/<?= urlencode($tenant['id']) ?>/edit"><?= htmlspecialchars(__('admin.tenant_show.edit')) ?></a>
      <form method="post" action="/admin/tenants/<?= urlencode($tenant['id']) ?>/rotate-api-key">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        <button class="bg-indigo-600 text-white px-4 py-2 rounded" type="submit"><?= htmlspecialchars(__('admin.tenant_show.rotate_api_key')) ?></button>
      </form>
    </div>
  </div>
</body>
</html>
