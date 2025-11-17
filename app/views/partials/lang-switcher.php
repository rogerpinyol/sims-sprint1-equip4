<?php
$locales = I18n::availableLocales();
$currentLocale = I18n::locale();
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

$baseQuery = $_GET ?? [];
unset($baseQuery['lang']);
?>
<div class="flex items-center gap-2">
  <?php foreach ($locales as $index => $localeCode): ?>
    <?php
      $query = $baseQuery;
      $query['lang'] = $localeCode;
      $href = $path;
      if ($query !== []) {
          $href .= '?' . http_build_query($query);
      }
  $isCurrent = $localeCode === $currentLocale;
  $label = __('locale.' . $localeCode);
    ?>
    <a
      href="<?= htmlspecialchars($href) ?>"
      data-lang="<?= htmlspecialchars($localeCode) ?>"
      class="text-xs font-medium <?= $isCurrent ? 'text-indigo-600 font-semibold underline' : 'text-slate-600 hover:text-indigo-600' ?>"
    ><?= htmlspecialchars($label) ?></a>
    <?php if ($index < count($locales) - 1): ?>
      <span class="text-slate-400">|</span>
    <?php endif; ?>
  <?php endforeach; ?>
</div>
