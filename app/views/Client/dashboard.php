<?php
// dashboard.php - Dashboard temporal para usuarios finales
// Asume que $user está disponible en el contexto (nombre, email, etc.)
function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
$user = $user ?? ['name' => 'Usuario', 'email' => 'usuario@email.com'];

// Ejemplo de vehículos disponibles (en una app real vendrían de la BD)
$vehicles = [
    [
        'id' => 1,
        'model' => 'Tesla Model 3',
        'status' => 'available',
        'battery' => '85%',
        'location' => 'Calle Mayor 10',
        'img' => 'https://tesla-cdn.thron.com/delivery/public/image/tesla/6e7e2e2b-7e7e-4e2e-8e2e-7e7e2e2b7e7e/bvlatuR/std/2880x1800/_25-Hero-D',
    ],
    [
        'id' => 2,
        'model' => 'Renault Zoe',
        'status' => 'booked',
        'battery' => '60%',
        'location' => 'Av. Libertad 22',
        'img' => 'https://www.renault.es/content/dam/Renault/ES/vehicles/zoe-ph2/bcm/renault-zoe-bcm-ph2.jpg',
    ],
    [
        'id' => 3,
        'model' => 'Nissan Leaf',
        'status' => 'available',
        'battery' => '72%',
        'location' => 'Plaza España',
        'img' => 'https://www.nissan.es/content/dam/Nissan/es/vehicles/leaf/leaf-2022/overview/leaf-2022-overview-hero.jpg',
    ],
];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>EcoMotion - Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-slate-800 font-sans flex flex-col min-h-screen">
  <header class="bg-blue-700 text-white py-4 shadow">
    <div class="max-w-4xl mx-auto flex justify-between items-center px-4">
      <h1 class="text-2xl font-bold">EcoMotion</h1>
      <div>
        <span class="mr-4">Hola, <?= e($user['name']) ?></span>
        <a href="/logout" class="bg-blue-900 hover:bg-blue-800 px-3 py-1 rounded text-sm">Cerrar sesión</a>
      </div>
    </div>
  </header>
  <main class="flex-grow max-w-4xl mx-auto px-4 py-8">
    <h2 class="text-xl font-semibold mb-6">Vehículos disponibles</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <?php foreach ($vehicles as $v): ?>
        <div class="bg-white rounded-xl shadow p-4 flex flex-col md:flex-row items-center">
          <img src="<?= e($v['img']) ?>" alt="<?= e($v['model']) ?>" class="w-32 h-20 object-cover rounded mb-2 md:mb-0 md:mr-4">
          <div class="flex-1">
            <div class="font-bold text-lg mb-1"><?= e($v['model']) ?></div>
            <div class="text-sm text-slate-600 mb-1">Ubicación: <?= e($v['location']) ?></div>
            <div class="text-sm text-slate-600 mb-1">Batería: <?= e($v['battery']) ?></div>
            <div class="text-sm mb-2">
              Estado: <span class="inline-block px-2 py-1 rounded text-xs <?= $v['status']==='available' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' ?>">
                <?= $v['status']==='available' ? 'Disponible' : 'Reservado' ?>
              </span>
            </div>
            <?php if ($v['status'] === 'available'): ?>
              <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow text-sm">Reservar</button>
            <?php else: ?>
              <button class="bg-gray-400 text-white px-4 py-2 rounded shadow text-sm cursor-not-allowed" disabled>No disponible</button>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </main>
  <?php include __DIR__ . '/../footer.php'; ?>
</body>
</html>
