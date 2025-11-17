<header class="bg-white border-b border-slate-200">
  <div class="max-w-5xl mx-auto px-4 h-14 flex items-center justify-between gap-2">
    <a href="/" class="flex items-center gap-2">
      <img src="/images/logo.jpg" alt="<?= htmlspecialchars(__('common.logo_alt')) ?>" class="w-8 h-8 rounded-full shadow" />
      <span class="font-semibold text-slate-800"><?= htmlspecialchars(__('app.name')) ?></span>
    </a>
    <div>
      <?php include __DIR__ . '/lang-switcher.php'; ?>
    </div>
  </div>
</header>
