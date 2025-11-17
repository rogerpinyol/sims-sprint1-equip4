<?php
// Initialize i18n for landing page
require_once __DIR__ . '/../app/core/I18n.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$requestedLocale = $_GET['lang'] ?? ($_SESSION['lang'] ?? ($_COOKIE['lang'] ?? null));
$locale = is_string($requestedLocale) && in_array($requestedLocale, I18n::availableLocales(), true) ? $requestedLocale : 'en';
$_SESSION['lang'] = $locale;
if (!headers_sent()) {
    setcookie('lang', $locale, ['expires' => time() + 60 * 60 * 24 * 30, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
}
I18n::init($locale);
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(I18n::locale()) ?>">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title><?= htmlspecialchars(__('landing.meta_title')) ?></title>
	<meta name="description" content="<?= htmlspecialchars(__('landing.meta_description')) ?>" />
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
	<script src="https://cdn.tailwindcss.com"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
	<script>
		tailwind.config = {
			theme: {
				extend: {
					fontFamily: { sans: ['Inter', 'ui-sans-serif', 'system-ui'] },
					colors: {
						brand: {
							50: '#F0FDF4',
							100: '#DCFCE7',
							200: '#BBF7D0',
							300: '#86EFAC',
							400: '#4ADE80',
							500: '#22C55E',
							600: '#16A34A',
							700: '#15803D',
							800: '#166534',
							900: '#14532D',
						},
						dark: '#0B1220',
					}
				}
			}
		}
	</script>
	<style>
		html { scroll-behavior: smooth; }
	</style>
	<link rel="icon" type="image/jpeg" href="/images/logo.jpg">
</head>
<body class="bg-white text-slate-800 font-sans">
	<header class="sticky top-0 z-40 bg-white/80 backdrop-blur border-b border-slate-200">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="flex h-16 items-center justify-between">
				<a href="#" class="flex items-center gap-2">
					<img src="/images/logo.jpg" alt="<?= htmlspecialchars(__('common.logo_alt')) ?>" class="w-8 h-8 rounded-full shadow" />
					<span class="text-lg font-bold"><?= htmlspecialchars(__('app.name')) ?></span>
				</a>
				<div class="hidden md:flex items-center gap-3">
					<?php include __DIR__ . '/../app/views/partials/lang-switcher.php'; ?>
                    <a href="/manager/login" class="px-4 py-2 rounded-lg border border-blue-600 text-blue-700 font-semibold text-sm hover:bg-blue-50 transition"><?= htmlspecialchars(__('landing.header.login')) ?></a>
                    <a href="#contact" class="px-4 py-2 rounded-lg border border-green-600 text-green-700 font-semibold text-sm hover:bg-green-50 transition"><?= htmlspecialchars(__('landing.header.contact')) ?></a>
                </div>
			</div>
		</div>
	</header>
	<section class="relative overflow-hidden">
		<div class="absolute inset-0 bg-gradient-to-br from-brand-50 via-white to-white" aria-hidden="true"></div>
		<div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 sm:py-28">
			<div class="grid lg:grid-cols-2 gap-12 items-center">
				<div>
					<h1 class="text-4xl sm:text-5xl font-extrabold leading-tight text-dark mb-6">
						<?= htmlspecialchars(__('landing.hero.title')) ?>
					</h1>
					<p class="text-lg text-slate-600 max-w-xl mb-8">
						<?= htmlspecialchars(__('landing.hero.description')) ?>
					</p>
					<div class="flex flex-col sm:flex-row gap-5 mb-8">
						<a href="#features" class="flex-1 px-6 py-4 rounded-xl bg-blue-600 text-white font-semibold text-lg hover:bg-blue-700 text-center shadow transition"><?= htmlspecialchars(__('landing.hero.features_btn')) ?></a>
						<a href="#contact" class="flex-1 px-6 py-4 rounded-xl bg-emerald-600 text-white font-semibold text-lg hover:bg-emerald-700 text-center shadow transition"><?= htmlspecialchars(__('landing.hero.contact_btn')) ?></a>
					</div>
				</div>
				<div class="hidden lg:block">
					<div class="relative bg-white rounded-2xl shadow-md">
						<img src="/images/adminDashboard.png" alt="<?= htmlspecialchars(__('landing.hero.dashboard_preview_alt')) ?>" class="rounded-lg">
					</div>
				</div>
			</div>
		</div>
	</section>
	<section id="features" class="py-20 sm:py-24">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="text-center max-w-3xl mx-auto">
				<h2 class="text-3xl sm:text-4xl font-bold text-dark"><?= htmlspecialchars(__('landing.features.title')) ?></h2>
				<p class="mt-4 text-slate-600"><?= htmlspecialchars(__('landing.features.description')) ?></p>
			</div>
			<div class="mt-12 grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
				<div class="p-6 rounded-2xl border border-slate-200">
					<div class="w-12 h-12 rounded-xl bg-brand-100 text-brand-700 flex items-center justify-center mb-4">
						<i class="fas fa-map-marker-alt text-2xl"></i>
					</div>
					<h3 class="font-semibold text-lg"><?= htmlspecialchars(__('landing.features.tracking.title')) ?></h3>
					<p class="mt-2 text-slate-600"><?= htmlspecialchars(__('landing.features.tracking.description')) ?></p>
				</div>
				<div class="p-6 rounded-2xl border border-slate-200">
					<div class="w-12 h-12 rounded-xl bg-brand-100 text-brand-700 flex items-center justify-center mb-4">
						<i class="fas fa-calendar-alt text-2xl"></i>
					</div>
					<h3 class="font-semibold text-lg"><?= htmlspecialchars(__('landing.features.scheduling.title')) ?></h3>
					<p class="mt-2 text-slate-600"><?= htmlspecialchars(__('landing.features.scheduling.description')) ?></p>
				</div>
				<div class="p-6 rounded-2xl border border-slate-200">
					<div class="w-12 h-12 rounded-xl bg-brand-100 text-brand-700 flex items-center justify-center mb-4">
						<i class="fas fa-tools text-2xl"></i>
					</div>
					<h3 class="font-semibold text-lg"><?= htmlspecialchars(__('landing.features.maintenance.title')) ?></h3>
					<p class="mt-2 text-slate-600"><?= htmlspecialchars(__('landing.features.maintenance.description')) ?></p>
				</div>
				<div class="p-6 rounded-2xl border border-slate-200">
					<div class="w-12 h-12 rounded-xl bg-brand-100 text-brand-700 flex items-center justify-center mb-4">
						<i class="fas fa-file-invoice-dollar text-2xl"></i>
					</div>
					<h3 class="font-semibold text-lg"><?= htmlspecialchars(__('landing.features.billing.title')) ?></h3>
					<p class="mt-2 text-slate-600"><?= htmlspecialchars(__('landing.features.billing.description')) ?></p>
				</div>
				<div class="p-6 rounded-2xl border border-slate-200">
					<div class="w-12 h-12 rounded-xl bg-brand-100 text-brand-700 flex items-center justify-center mb-4">
						<i class="fas fa-chart-bar text-2xl"></i>
					</div>
					<h3 class="font-semibold text-lg"><?= htmlspecialchars(__('landing.features.analytics.title')) ?></h3>
					<p class="mt-2 text-slate-600"><?= htmlspecialchars(__('landing.features.analytics.description')) ?></p>
				</div>
				<div class="p-6 rounded-2xl border border-slate-200">
					<div class="w-12 h-12 rounded-xl bg-brand-100 text-brand-700 flex items-center justify-center mb-4">
						<i class="fas fa-code text-2xl"></i>
					</div>
					<h3 class="font-semibold text-lg"><?= htmlspecialchars(__('landing.features.api.title')) ?></h3>
					<p class="mt-2 text-slate-600"><?= htmlspecialchars(__('landing.features.api.description')) ?></p>
				</div>
			</div>
		</div>
	</section>
	<section id="contact" class="py-20 bg-slate-50">
		<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="bg-white rounded-2xl shadow-lg p-8">
				<h2 class="text-2xl font-bold mb-4 text-dark"><?= htmlspecialchars(__('landing.contact.title')) ?></h2>
				<form action="mailto:admin@ecomotion.com" method="POST" enctype="text/plain" class="space-y-4">
					<div>
						<label for="name" class="block text-sm font-medium text-slate-700"><?= htmlspecialchars(__('landing.contact.name_label')) ?></label>
						<input type="text" id="name" name="name" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500" />
					</div>
					<div>
						<label for="email" class="block text-sm font-medium text-slate-700"><?= htmlspecialchars(__('landing.contact.email_label')) ?></label>
						<input type="email" id="email" name="email" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500" />
					</div>
					<div>
						<label for="message" class="block text-sm font-medium text-slate-700"><?= htmlspecialchars(__('landing.contact.message_label')) ?></label>
						<textarea id="message" name="message" rows="4" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500"></textarea>
					</div>
					<button type="submit" class="w-full py-3 px-6 rounded-xl bg-blue-600 text-white font-medium hover:bg-blue-700"><?= htmlspecialchars(__('landing.contact.submit_btn')) ?></button>
				</form>
			</div>
		</div>
	</section>
	<footer class="border-t border-slate-200 py-10">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="flex flex-col md:flex-row items-center justify-between gap-4">
				<div class="flex items-center gap-2">
					<a href="#" class="flex items-center gap-2">
						<img src="/images/logo.jpg" alt="<?= htmlspecialchars(__('common.logo_alt')) ?>" class="w-8 h-8 rounded-full shadow" />
						<span class="text-lg font-bold"><?= htmlspecialchars(__('app.name')) ?></span>
					</a>
				</div>
				<div class="text-sm text-slate-500"><?= htmlspecialchars(__('landing.footer.copyright', ['year' => date('Y')])) ?></div>
				<div class="flex gap-4 text-sm">
					<a href="/privacy" class="hover:text-blue-600"><?= htmlspecialchars(__('landing.footer.privacy')) ?></a>
					<a href="/terms" class="hover:text-blue-600"><?= htmlspecialchars(__('landing.footer.terms')) ?></a>
					<a href="#contact" class="hover:text-blue-600"><?= htmlspecialchars(__('landing.footer.contact')) ?></a>
				</div>
			</div>
		</div>
	</footer>
	<link rel="stylesheet" href="/assets/css/cookie-manager.css" />
	<script src="/assets/js/cookie-manager.js"></script>
	<script>
		const yearEl = document.getElementById('year');
		if (yearEl) yearEl.textContent = new Date().getFullYear();
		const btn = document.getElementById('menuBtn');
		const menu = document.getElementById('mobileMenu');
		if (btn && menu) btn.addEventListener('click', () => menu.classList.toggle('hidden'));
	</script>
</body>
</html>
