<?php
$users = $users ?? [];
?>
<div class="bg-slate-50 text-slate-900">
  <?php $active='users'; include __DIR__ . '/../partials/manager/mobileSidebar.php'; ?>

  <div class="min-h-screen grid grid-cols-1 md:grid-cols-[240px_1fr]">
    <?php $active='users'; include __DIR__ . '/../partials/manager/sidebar.php'; ?>

    <!-- Main -->
    <main class="flex flex-col">
      <?php include __DIR__ . '/../partials/manager/header.php'; ?>

      <div class="p-4 space-y-4">
        <h2 class="text-base font-semibold"><?= htmlspecialchars(__('manager.users.heading')) ?></h2>

        <?php if (!empty($_SESSION['flash_errors'])): $errs = $_SESSION['flash_errors']; unset($_SESSION['flash_errors']); ?>
          <div class="mb-3 rounded-md border border-red-200 bg-red-50 text-red-700 px-3 py-2 text-sm">
            <?php foreach ($errs as $er) echo '<div>'.e($er).'</div>'; ?>
          </div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['flash_success'])): unset($_SESSION['flash_success']); ?>
          <div class="mb-3 rounded-md border border-green-200 bg-green-50 text-green-700 px-3 py-2 text-sm">
            <?= htmlspecialchars(__('manager.users.flash.success')) ?>
          </div>
        <?php endif; ?>

        <form method="post" action="/manager/users" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 mb-3 items-start">
          <input required name="name" placeholder="<?= htmlspecialchars(__('manager.users.form.name_placeholder')) ?>" class="input">
          <input required type="email" name="email" placeholder="<?= htmlspecialchars(__('manager.users.form.email_placeholder')) ?>" class="input">
          <input required minlength="6" type="password" name="password" placeholder="<?= htmlspecialchars(__('manager.users.form.password_placeholder')) ?>" class="input">
          <input name="phone" placeholder="<?= htmlspecialchars(__('manager.users.form.phone_placeholder')) ?>" class="input">
          <input name="accessibility_flags" placeholder="<?= htmlspecialchars(__('manager.users.form.accessibility_placeholder')) ?>" class="input">
          <select name="role" class="input">
            <option value="client"><?= htmlspecialchars(__('manager.users.form.role.client')) ?></option>
            <option value="manager"><?= htmlspecialchars(__('manager.users.form.role.manager')) ?></option>
          </select>
          <div class="sm:col-span-2 lg:col-span-1">
            <button type="submit" class="btn btn-primary w-full sm:w-auto"><?= htmlspecialchars(__('manager.users.form.submit')) ?></button>
          </div>
        </form>

        <?php if (empty($users)): ?>
          <div class="text-slate-500 text-sm"><?= htmlspecialchars(__('manager.users.table.empty')) ?></div>
        <?php else: ?>
          <div class="overflow-x-auto">
            <table class="table text-sm">
              <thead>
                <tr>
                  <th class="hidden md:table-cell"><?= htmlspecialchars(__('manager.users.table.id')) ?></th>
                  <th><?= htmlspecialchars(__('manager.users.table.name')) ?></th>
                  <th class="hidden md:table-cell"><?= htmlspecialchars(__('manager.users.table.email')) ?></th>
                  <th><?= htmlspecialchars(__('manager.users.table.role')) ?></th>
                  <th class="hidden md:table-cell"><?= htmlspecialchars(__('manager.users.table.phone')) ?></th>
                  <th class="hidden md:table-cell"><?= htmlspecialchars(__('manager.users.table.accessibility')) ?></th>
                  <th><?= htmlspecialchars(__('manager.users.table.actions')) ?></th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($users as $u): ?>
                  <?php $uid = e($u['id'] ?? ''); ?>
                  <tr data-row-id="<?= $uid ?>">
                    <td class="align-top hidden md:table-cell"><?= $uid ?></td>
                    <td class="align-top">
                      <input form="upd-<?= $uid ?>" name="name" value="<?= e($u['name'] ?? '') ?>" class="border border-slate-200 rounded-md px-2 py-1 min-w-[140px]">
                    </td>
                    <td class="align-top hidden md:table-cell">
                      <input form="upd-<?= $uid ?>" type="email" name="email" value="<?= e($u['email'] ?? '') ?>" class="border border-slate-200 rounded-md px-2 py-1 min-w-[200px]">
                    </td>
                    <td class="align-top">
                      <?php if (($u['role'] ?? '') !== 'tenant_admin'): ?>
                        <select form="upd-<?= $uid ?>" name="role" class="border border-slate-200 rounded-md px-2 py-1 min-w-[130px]">
                          <option value="client" <?= (($u['role'] ?? '')==='client')?'selected':'' ?>>client</option>
                          <option value="manager" <?= (($u['role'] ?? '')==='manager')?'selected':'' ?>>manager</option>
                        </select>
                      <?php else: ?>
                        <span class="text-slate-400">tenant_admin</span>
                      <?php endif; ?>
                    </td>
                    <td class="align-top hidden md:table-cell">
                      <input form="upd-<?= $uid ?>" name="phone" value="<?= e($u['phone'] ?? '') ?>" class="border border-slate-200 rounded-md px-2 py-1 min-w-[120px]">
                    </td>
                    <td class="align-top hidden md:table-cell">
                      <input form="upd-<?= $uid ?>" name="accessibility_flags" value="<?= e(is_string($u['accessibility_flags'] ?? '') ? ($u['accessibility_flags'] ?? '') : json_encode($u['accessibility_flags'] ?? '')) ?>" class="border border-slate-200 rounded-md px-2 py-1 min-w-[140px]">
                    </td>
                    <td class="align-top whitespace-nowrap">
                      <?php if (($u['role'] ?? '') !== 'tenant_admin'): ?>
                        <form id="upd-<?= $uid ?>" method="post" action="/manager/users/<?= $uid ?>/update" class="inline"></form>
                        <button form="upd-<?= $uid ?>" type="submit" class="bg-amber-500 hover:bg-amber-600 text-white rounded-md px-3 py-1"><?= htmlspecialchars(__('manager.users.table.actions.save')) ?></button>
                        <?php if (in_array(($u['role'] ?? ''), ['client', 'manager'], true)): ?>
                            <form class="js-delete-user inline" method="post" action="/manager/users/<?= $uid ?>/delete" data-id="<?= $uid ?>">
                                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white rounded-md px-3 py-1"><?= htmlspecialchars(__('manager.users.table.actions.delete')) ?></button>
                            </form>
                        <?php endif; ?>
                      <?php else: ?>
                        <span class="text-slate-400"><?= htmlspecialchars(__('manager.users.table.actions.none')) ?></span>
                      <?php endif; ?>
                    </td>
                  </tr>
                  <!-- Compact details for mobile -->
                  <tr class="md:hidden">
                    <td class="align-top" colspan="7">
                      <div class="text-xs text-slate-600 space-y-1">
                        <div><span class="font-medium text-slate-700"><?= htmlspecialchars(__('manager.users.table.email')) ?>:</span> <?= e($u['email'] ?? '') ?></div>
                        <div><span class="font-medium text-slate-700"><?= htmlspecialchars(__('manager.users.table.phone')) ?>:</span> <?= e($u['phone'] ?? '') ?></div>
                        <div><span class="font-medium text-slate-700"><?= htmlspecialchars(__('manager.users.table.accessibility')) ?>:</span> <?= e(is_string($u['accessibility_flags'] ?? '') ? ($u['accessibility_flags'] ?? '') : json_encode($u['accessibility_flags'] ?? '')) ?></div>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>

      </div>
  <footer class="mt-auto w-full text-center text-slate-500 text-xs py-4 border-t border-slate-100 bg-white"><?= htmlspecialchars(__('manager.footer.version', ['year' => (string)date('Y'), 'version' => '1.0'])) ?></footer>
    </main>
  </div>

  
</div>
