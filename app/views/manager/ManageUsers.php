<?php
$users = $users ?? [];
function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
$currentRole = $_SESSION['role'] ?? 'client';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Manager Dashboard - User Management</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Inter', Arial, sans-serif; background: #f4f6fa; margin: 0; padding: 0; }
    .container { max-width: 1100px; margin: 32px auto; background: #fff; border-radius: 12px; box-shadow: 0 2px 16px rgba(0,0,0,0.07); padding: 32px 28px 24px 28px; }
    h1 { font-size: 2.1rem; font-weight: 600; margin-bottom: 18px; color: #2d3748; }
    .toolbar { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 18px; align-items: center; }
    #q { padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; width: 260px; font-size: 1rem; background: #f9fafb; }
    .user-form { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 18px; background: #f9fafb; padding: 14px 16px; border-radius: 8px; align-items: flex-end; }
    .user-form input, .user-form select { padding: 7px 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 1rem; background: #fff; }
    .user-form label { font-size: 0.97rem; color: #374151; margin-bottom: 2px; font-weight: 500; }
    .user-form .form-group { display: flex; flex-direction: column; min-width: 160px; }
    .user-form button { background: #2563eb; color: #fff; border: none; border-radius: 5px; padding: 8px 18px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: background 0.18s; }
    .user-form button:hover { background: #1d4ed8; }
    table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,0.04); }
    th, td { padding: 10px 8px; border-bottom: 1px solid #e5e7eb; text-align: left; font-size: 1rem; }
    th { background: #f1f5f9; font-weight: 600; color: #374151; }
    tr:last-child td { border-bottom: none; }
    .actions { display: flex; gap: 8px; }
    .btn-edit, .btn-delete { border: none; border-radius: 4px; padding: 5px 12px; font-size: 0.97rem; cursor: pointer; transition: background 0.15s; }
    .btn-edit { background: #fbbf24; color: #fff; }
    .btn-edit:hover { background: #f59e1b; }
    .btn-delete { background: #ef4444; color: #fff; }
    .btn-delete:hover { background: #dc2626; }
    .muted { color: #888; font-size: 1.05rem; margin-top: 18px; }
    @media (max-width: 800px) { .container { padding: 12px 2vw; } table, thead, tbody, th, td, tr { font-size: 0.97rem; } .user-form .form-group { min-width: 120px; } }
    @media (max-width: 600px) { .user-form { flex-direction: column; gap: 6px; } .user-form .form-group { width: 100%; } table, thead, tbody, th, td, tr { font-size: 0.93rem; } }
  </style>
</head>
<body>
  <div class="container">
    <h1>Manage Users</h1>

    <div class="toolbar">
      <input type="search" id="q" placeholder="Search by name or email">
    </div>

    <!-- User creation form (Managers can only create clients) -->
    <form class="user-form" method="post" action="/manager/users">
      <div class="form-group">
        <label for="name">Name</label>
        <input type="text" id="name" name="name" required>
      </div>
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required minlength="6">
      </div>
      <div class="form-group">
        <label for="phone">Phone</label>
        <input type="text" id="phone" name="phone">
      </div>
      <div class="form-group">
        <label for="accessibility_flags">Accessibility</label>
        <input type="text" id="accessibility_flags" name="accessibility_flags" placeholder="JSON or text">
      </div>
      <button type="submit">Add Client</button>
    </form>

    <?php if (empty($users)): ?>
      <p class="muted">No users found.</p>
    <?php else: ?>
      <table id="usersTable">
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Phone</th>
            <th>Accessibility</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u): ?>
            <tr data-user-id="<?php echo e($u['id'] ?? ''); ?>">
              <td><?php echo e($u['id'] ?? ''); ?></td>
              <td><?php echo e($u['name'] ?? ''); ?></td>
              <td><?php echo e($u['email'] ?? ''); ?></td>
              <td><?php echo e($u['role'] ?? ''); ?></td>
              <td><?php echo e($u['phone'] ?? ''); ?></td>
              <td><?php echo e(is_string($u['accessibility_flags'] ?? '') ? ($u['accessibility_flags'] ?? '') : json_encode($u['accessibility_flags'] ?? '')); ?></td>
              <td><?php echo e($u['created_at'] ?? ''); ?></td>
              <td class="actions">
                <button class="btn-edit" type="button" disabled>Edit</button>
                <?php if (($u['role'] ?? '') === 'client'): ?>
                  <button class="btn-delete" type="button" data-id="<?php echo e($u['id'] ?? ''); ?>">Delete</button>
                <?php else: ?>
                  <span class="muted">—</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
  <script>
    (function() {
      function $(sel, root){ return (root||document).querySelector(sel); }
      function $all(sel, root){ return Array.prototype.slice.call((root||document).querySelectorAll(sel)); }

      function findRow(el){
        while (el && el.tagName !== 'TR') el = el.parentElement;
        return el;
      }

      $all('.btn-delete').forEach(function(btn){
        btn.addEventListener('click', async function(){
          var id = this.getAttribute('data-id');
          if (!id) return;
          if (!confirm('¿Eliminar este usuario?')) return;
          try {
            const res = await fetch('/manager/users/' + encodeURIComponent(id) + '/delete', {
              method: 'POST',
              headers: {
                'Accept': 'application/json'
              }
            });
            let ok = false;
            if (res.headers.get('content-type') && res.headers.get('content-type').includes('application/json')) {
              const data = await res.json();
              ok = !!(data && data.deleted);
            } else {
              // If server redirected, treat as failure in AJAX flow
              ok = res.ok && res.redirected === false;
            }
            if (ok) {
              var row = findRow(this);
              if (row && row.parentElement) row.parentElement.removeChild(row);
            } else {
              alert('No se pudo eliminar el usuario.');
            }
          } catch (e) {
            console.error(e);
            alert('Error de red: no se pudo completar la operación.');
          }
        });
      });
    })();
  </script>
</body>
</html>