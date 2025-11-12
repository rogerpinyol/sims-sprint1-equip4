<?php
// Variables expected: $title (string), $content (string), optional $user (array)
$title = isset($title) ? (string)$title : 'EcoMotion';
?>
<!doctype html>
<html lang="en" class="h-full">
<head>
  <meta charset="utf-8">
  <title><?= e($title) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="/images/logo.jpg" type="image/jpeg">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
  <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
  <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
  <link rel="stylesheet" href="/css/brand.css" />
</head>
<body class="bg-slate-100 text-[color:var(--brand-text)] font-sans flex flex-col min-h-screen">
  <?= $content ?>
  <?php if (!empty($scripts) && is_array($scripts)): ?>
    <?php foreach ($scripts as $src): ?>
      <script src="<?= e($src) ?>" defer></script>
    <?php endforeach; ?>
  <?php endif; ?>
</body>
</html>
