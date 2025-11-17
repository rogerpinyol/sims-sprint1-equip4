<?php
// Tenant Admin Tenants Dashboard
// Requires: tenant_admin role
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(I18n::locale()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(__('admin.tenants.meta_title')) ?></title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="/css/brand.css" />
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen">
        <div class="min-h-screen flex flex-col">
            <main class="flex-1 max-w-6xl mx-auto w-full px-4 py-8 space-y-6">
    <?php if (isset($feedback) && $feedback): ?>
        <div class="mb-4 p-4 rounded-lg border text-sm <?php echo $feedback['success'] ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-red-50 border-red-200 text-red-800'; ?>">
            <strong><?= htmlspecialchars($feedback['message']) ?></strong>
            <?php if (!empty($feedback['api_key'])): ?>
                <div class="mt-2 text-xs text-slate-700"><?= htmlspecialchars(__('admin.tenants.flash.api_key_label')) ?> <span class="font-mono bg-slate-100 px-2 py-1 rounded"><?= htmlspecialchars($feedback['api_key']) ?></span></div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <div class="flex items-center justify-between gap-4">
        <h1 class="text-2xl font-bold"><?= htmlspecialchars(__('admin.tenants.heading')) ?></h1>
        <button type="button" id="btnOpenCreateTenant" class="btn btn-primary">
            <?= htmlspecialchars(__('admin.tenants.modal.title')) ?>
        </button>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl p-4">
        <form method="GET" class="flex flex-wrap items-center gap-2">
            <input type="text" name="search" placeholder="<?= htmlspecialchars(__('admin.tenants.search.placeholder')) ?>" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" class="input w-full sm:w-64" />
            <select name="plan_type" class="input w-full sm:w-40">
                    <option value="" <?= (!isset($_GET['plan_type']) || $_GET['plan_type'] === '') ? 'selected' : '' ?>><?= htmlspecialchars(__('admin.tenants.filter.plan.all')) ?></option>
                    <option value="standard" <?= (isset($_GET['plan_type']) && $_GET['plan_type'] === 'standard') ? 'selected' : '' ?>><?= htmlspecialchars(__('admin.tenants.filter.plan.standard')) ?></option>
                    <option value="premium" <?= (isset($_GET['plan_type']) && $_GET['plan_type'] === 'premium') ? 'selected' : '' ?>><?= htmlspecialchars(__('admin.tenants.filter.plan.premium')) ?></option>
                </select>
            <select name="is_active" class="input w-full sm:w-40">
                    <option value="" <?= (!isset($_GET['is_active']) || $_GET['is_active'] === '') ? 'selected' : '' ?>><?= htmlspecialchars(__('admin.tenants.filter.status.all')) ?></option>
                    <option value="1" <?= (isset($_GET['is_active']) && $_GET['is_active'] === '1') ? 'selected' : '' ?>><?= htmlspecialchars(__('admin.tenants.filter.status.active')) ?></option>
                    <option value="0" <?= (isset($_GET['is_active']) && $_GET['is_active'] === '0') ? 'selected' : '' ?>><?= htmlspecialchars(__('admin.tenants.filter.status.inactive')) ?></option>
                </select>
            <button type="submit" class="btn btn-secondary"><?= htmlspecialchars(__('admin.tenants.filter.submit')) ?></button>
        </form>
    </div>

    <!-- Tenants Table -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr class="text-xs text-slate-500 uppercase">
                        <th class="px-6 py-3 text-left"><?= htmlspecialchars(__('admin.tenants.table.id')) ?></th>
                        <th class="px-6 py-3 text-left"><?= htmlspecialchars(__('admin.tenants.table.name')) ?></th>
                        <th class="px-6 py-3 text-left"><?= htmlspecialchars(__('admin.tenants.table.subdomain')) ?></th>
                        <th class="px-6 py-3 text-left"><?= htmlspecialchars(__('admin.tenants.table.plan')) ?></th>
                        <th class="px-6 py-3 text-left"><?= htmlspecialchars(__('admin.tenants.table.status')) ?></th>
                        <th class="px-6 py-3 text-left"><?= htmlspecialchars(__('admin.tenants.table.actions')) ?></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (!empty($tenants)): ?>
                        <?php foreach ($tenants as $tenant): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-3 whitespace-nowrap text-slate-700 text-sm"><?= htmlspecialchars($tenant['id']) ?></td>
                                <td class="px-6 py-3 whitespace-nowrap text-slate-900 text-sm font-semibold"><?= htmlspecialchars($tenant['name']) ?></td>
                                <td class="px-6 py-3 whitespace-nowrap text-blue-700 text-sm"><?= htmlspecialchars($tenant['subdomain']) ?></td>
                                <td class="px-6 py-3 whitespace-nowrap text-slate-700 text-sm"><?= htmlspecialchars($tenant['plan_type']) ?></td>
                                <td class="px-6 py-3 whitespace-nowrap text-sm">
                                    <?php if ($tenant['is_active']): ?>
                                        <span class="inline-flex px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800"><?= htmlspecialchars(__('admin.tenants.filter.status.active')) ?></span>
                                    <?php else: ?>
                                        <span class="inline-flex px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800"><?= htmlspecialchars(__('admin.tenants.filter.status.inactive')) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap text-sm">
                                    <div class="flex flex-wrap items-center gap-2">
                                    <a href="/admin/tenants/<?= urlencode($tenant['id']) ?>/view" class="text-blue-600 hover:text-blue-800 text-xs font-medium"><?= htmlspecialchars(__('admin.tenants.actions.view')) ?></a>
                                    <a href="/admin/tenants/<?= urlencode($tenant['id']) ?>/edit" class="text-amber-600 hover:text-amber-800 text-xs font-medium"><?= htmlspecialchars(__('admin.tenants.actions.edit')) ?></a>
                                    <?php if ($tenant['is_active']): ?>
                                        <form method="POST" action="/admin/tenants/<?= urlencode($tenant['id']) ?>/deactivate" class="tenant-action inline" data-confirm="<?= htmlspecialchars(__('admin.tenants.confirm.deactivate')) ?>">
                                            <input type="hidden" name="id" value="<?= htmlspecialchars($tenant['id']) ?>">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                            <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium"><?= htmlspecialchars(__('admin.tenants.actions.deactivate')) ?></button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" action="/admin/tenants/<?= urlencode($tenant['id']) ?>/activate" class="tenant-action inline" data-confirm="<?= htmlspecialchars(__('admin.tenants.confirm.activate')) ?>">
                                            <input type="hidden" name="id" value="<?= htmlspecialchars($tenant['id']) ?>">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                            <button type="submit" class="text-emerald-600 hover:text-emerald-800 text-xs font-medium"><?= htmlspecialchars(__('admin.tenants.actions.activate')) ?></button>
                                        </form>
                                    <?php endif; ?>
                                    <form method="POST" action="/admin/tenants/<?= urlencode($tenant['id']) ?>/rotate-api-key" class="tenant-action inline" data-confirm="<?= htmlspecialchars(__('admin.tenants.confirm.rotate_api_key')) ?>">
                                        <input type="hidden" name="id" value="<?= htmlspecialchars($tenant['id']) ?>">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                        <button type="submit" class="text-indigo-600 hover:text-indigo-800 text-xs font-medium"><?= htmlspecialchars(__('admin.tenants.actions.rotate_api_key')) ?></button>
                                    </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-slate-500 text-sm"><?= htmlspecialchars(__('admin.tenants.table.empty')) ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <!-- Pagination (example) -->
        <div class="mt-4 flex justify-end">
            <?php if (isset($pagination)): ?>
                <span class="text-xs text-slate-500"><?= htmlspecialchars(__('admin.tenants.pagination.summary', ['limit' => (string)($pagination['limit'] ?? 0), 'offset' => (string)($pagination['offset'] ?? 0)])) ?></span>
            <?php endif; ?>
        </div>
        <!-- Create Tenant Modal -->
        <div id="createTenantModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 hidden">
            <div class="bg-white rounded-xl shadow-lg w-full max-w-xl mx-4">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
                    <h2 class="text-lg font-semibold text-slate-900"><?= htmlspecialchars(__('admin.tenants.modal.title')) ?></h2>
                    <button type="button" id="btnCloseCreateTenant" class="text-slate-400 hover:text-slate-600" aria-label="Close create tenant form">&times;</button>
                </div>
                <div class="px-6 py-4">
                    <form id="createTenantForm" method="POST" action="/admin/tenants" class="space-y-4">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <div>
                            <label class="block text-sm font-medium text-slate-700"><?= htmlspecialchars(__('admin.tenants.modal.name_label')) ?></label>
                            <input type="text" name="name" required class="input mt-1 w-full" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700"><?= htmlspecialchars(__('admin.tenants.modal.subdomain_label')) ?></label>
                            <input type="text" name="subdomain" required class="input mt-1 w-full" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700"><?= htmlspecialchars(__('admin.tenants.modal.plan_type_label')) ?></label>
                            <select name="plan_type" class="input mt-1 w-full">
                                <option value="standard"><?= htmlspecialchars(__('admin.tenants.modal.plan_standard')) ?></option>
                                <option value="premium"><?= htmlspecialchars(__('admin.tenants.modal.plan_premium')) ?></option>
                            </select>
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" id="btnCancelCreateTenant" class="btn btn-secondary"><?= htmlspecialchars(__('admin.tenants.modal.cancel')) ?></button>
                            <button type="submit" class="btn btn-primary"><?= htmlspecialchars(__('admin.tenants.modal.submit')) ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
      </main>
            <footer class="mt-auto w-full text-center text-slate-500 text-xs py-4 border-t border-slate-100 bg-white"><?= htmlspecialchars(__('admin.tenants.footer', ['year' => (string)date('Y')])) ?></footer>
    </div>
<script src="/assets/js/validations/admin-tenants.js" defer></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const openBtn = document.getElementById('btnOpenCreateTenant');
    const modal = document.getElementById('createTenantModal');
    const closeBtn = document.getElementById('btnCloseCreateTenant');
    const cancelBtn = document.getElementById('btnCancelCreateTenant');

    function openModal() {
        if (!modal) return;
        modal.classList.remove('hidden');
        const firstInput = modal.querySelector('input[name="name"]');
        if (firstInput) {
            setTimeout(() => firstInput.focus(), 50);
        }
    }

    function closeModal() {
        if (!modal) return;
        modal.classList.add('hidden');
    }

    if (openBtn) openBtn.addEventListener('click', openModal);
    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (cancelBtn) cancelBtn.addEventListener('click', closeModal);

    if (modal) {
        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeModal();
            }
        });
    }
});
</script>
</body>
</html>
