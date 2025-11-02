<?php
// Expect $users array injected by controller
$users = $users ?? [];

// Simple escape helper
function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Users</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body { font-family: system-ui, Arial, sans-serif; margin: 16px; }
    .toolbar { margin-bottom: 12px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 8px; border-bottom: 1px solid #ddd; text-align: left; }
    th { background: #f6f6f6; }
    #q { padding: 6px 8px; width: 280px; max-width: 100%; }
    .muted { color: #666; }
  </style>
</head>
<body>
  <main>
    <h1>Users</h1>

    <div class="toolbar">
      <input type="search" id="q" placeholder="Search by name or email">
    </div>

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
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u): ?>
            <tr>
              <td><?php echo e($u['id'] ?? ''); ?></td>
              <td><?php echo e($u['name'] ?? ''); ?></td>
              <td><?php echo e($u['email'] ?? ''); ?></td>
              <td><?php echo e($u['role'] ?? ''); ?></td>
              <td><?php echo e($u['phone'] ?? ''); ?></td>
              <td><?php echo e($u['accessibility_flags'] ?? ''); ?></td>
              <td><?php echo e($u['created_at'] ?? ''); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </main>

  <script>
    // Basic client-side filter
    const q = document.getElementById('q');
    const tbody = document.querySelector('#usersTable tbody');
    if (q && tbody) {
      q.addEventListener('input', () => {
        const term = q.value.toLowerCase();
        for (const row of tbody.rows) {
          const name = (row.cells[1]?.textContent || '').toLowerCase();
          const email = (row.cells[2]?.textContent || '').toLowerCase();
          row.style.display = (name.includes(term) || email.includes(term)) ? '' : 'none';
        }
      });
    }
  </script>
</body>
</html>