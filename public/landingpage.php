<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>EcoMotion | Fleet Management SaaS for Companies</title>
	<meta name="description" content="EcoMotion is the professional SaaS platform for companies to monitor, optimize, and scale their vehicle fleets with ease." />
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
	<script src="https://cdn.tailwindcss.com"></script>
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
					<img src="/images/logo.jpg" alt="EcoMotion logo" class="w-8 h-8 rounded-full shadow" />
					<span class="text-lg font-bold">EcoMotion</span>
				</a>
				<div class="hidden md:flex items-center gap-3">
                    <a href="/manager/login" class="px-4 py-2 rounded-lg border border-blue-600 text-blue-700 font-semibold text-sm hover:bg-blue-50 transition">Login</a>
                    <a href="#contact" class="px-4 py-2 rounded-lg border border-green-600 text-green-700 font-semibold text-sm hover:bg-green-50 transition">Contact Us</a>
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
						Fleet Management SaaS for Companies
					</h1>
					<p class="text-lg text-slate-600 max-w-xl mb-8">
						EcoMotion is the all-in-one platform for businesses to manage, optimize, and scale their vehicle fleets. Access your company dashboard or contact our team to get started.
					</p>
					<div class="flex flex-col sm:flex-row gap-5 mb-8">
						<a href="#features" class="flex-1 px-6 py-4 rounded-xl bg-blue-600 text-white font-semibold text-lg hover:bg-blue-700 text-center shadow transition">Features</a>
						<a href="#contact" class="flex-1 px-6 py-4 rounded-xl bg-emerald-600 text-white font-semibold text-lg hover:bg-emerald-700 text-center shadow transition">Contact Admins</a>
					</div>
				</div>
				<div class="hidden lg:block">
					<div class="relative bg-white rounded-2xl shadow-md">
						<img src="/images/adminDashboard.png" alt="Dashboard preview" class="rounded-lg">
					</div>
				</div>
			</div>
		</div>
	</section>
	<section id="features" class="py-20 sm:py-24">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="text-center max-w-3xl mx-auto">
				<h2 class="text-3xl sm:text-4xl font-bold text-dark">Everything your company needs to run a modern fleet</h2>
				<p class="mt-4 text-slate-600">From real-time tracking to automated maintenance and unified billing—EcoMotion is your single source of truth for fleet operations.</p>
			</div>
			<div class="mt-12 grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
				<div class="p-6 rounded-2xl border border-slate-200">
					<div class="w-12 h-12 rounded-xl bg-brand-100 text-brand-700 flex items-center justify-center mb-4">
						<i class="fas fa-map-marker-alt text-2xl"></i>
					</div>
					<h3 class="font-semibold text-lg">Live vehicle tracking</h3>
					<p class="mt-2 text-slate-600">Monitor location, battery health, and status in real time across your entire fleet.</p>
				</div>
				<div class="p-6 rounded-2xl border border-slate-200">
					<div class="w-12 h-12 rounded-xl bg-brand-100 text-brand-700 flex items-center justify-center mb-4">
						<i class="fas fa-calendar-alt text-2xl"></i>
					</div>
					<h3 class="font-semibold text-lg">Smart scheduling</h3>
					<p class="mt-2 text-slate-600">Optimize bookings and dispatch with rules that balance utilization and battery charging.</p>
				</div>
				<div class="p-6 rounded-2xl border border-slate-200">
					<div class="w-12 h-12 rounded-xl bg-brand-100 text-brand-700 flex items-center justify-center mb-4">
						<i class="fas fa-tools text-2xl"></i>
					</div>
					<h3 class="font-semibold text-lg">Maintenance automation</h3>
					<p class="mt-2 text-slate-600">Forecast service intervals and trigger workflows before issues impact operations.</p>
				</div>
				<div class="p-6 rounded-2xl border border-slate-200">
					<div class="w-12 h-12 rounded-xl bg-brand-100 text-brand-700 flex items-center justify-center mb-4">
						<i class="fas fa-file-invoice-dollar text-2xl"></i>
					</div>
					<h3 class="font-semibold text-lg">Unified billing</h3>
					<p class="mt-2 text-slate-600">Consolidate pay-per-use, subscriptions, and partner invoices in one clean ledger.</p>
				</div>
				<div class="p-6 rounded-2xl border border-slate-200">
					<div class="w-12 h-12 rounded-xl bg-brand-100 text-brand-700 flex items-center justify-center mb-4">
						<i class="fas fa-chart-bar text-2xl"></i>
					</div>
					<h3 class="font-semibold text-lg">Analytics & reporting</h3>
					<p class="mt-2 text-slate-600">Understand costs, utilization, and performance with prebuilt and custom reports.</p>
				</div>
				<div class="p-6 rounded-2xl border border-slate-200">
					<div class="w-12 h-12 rounded-xl bg-brand-100 text-brand-700 flex items-center justify-center mb-4">
						<i class="fas fa-code text-2xl"></i>
					</div>
					<h3 class="font-semibold text-lg">Developer-friendly</h3>
					<p class="mt-2 text-slate-600">Robust API and webhooks to integrate EcoMotion into your existing stack.</p>
				</div>
			</div>
		</div>
	</section>
	<section id="contact" class="py-20 bg-slate-50">
		<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="bg-white rounded-2xl shadow-lg p-8">
				<h2 class="text-2xl font-bold mb-4 text-dark">Contact Administrators</h2>
				<form action="mailto:admin@ecomotion.com" method="POST" enctype="text/plain" class="space-y-4">
					<div>
						<label for="name" class="block text-sm font-medium text-slate-700">Name</label>
						<input type="text" id="name" name="name" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500" />
					</div>
					<div>
						<label for="email" class="block text-sm font-medium text-slate-700">Email</label>
						<input type="email" id="email" name="email" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500" />
					</div>
					<div>
						<label for="message" class="block text-sm font-medium text-slate-700">Message</label>
						<textarea id="message" name="message" rows="4" required class="mt-1 block w-full rounded-md border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500"></textarea>
					</div>
					<button type="submit" class="w-full py-3 px-6 rounded-xl bg-blue-600 text-white font-medium hover:bg-blue-700">Send Message</button>
				</form>
			</div>
		</div>
	</section>
	<footer class="border-t border-slate-200 py-10">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="flex flex-col md:flex-row items-center justify-between gap-4">
				<div class="flex items-center gap-2">
					<a href="#" class="flex items-center gap-2">
						<img src="/images/logo.jpg" alt="EcoMotion logo" class="w-8 h-8 rounded-full shadow" />
						<span class="text-lg font-bold">EcoMotion</span>
					</a>
				</div>
				<div class="text-sm text-slate-500">© <span id="year"></span> EcoMotion. All rights reserved.</div>
				<div class="flex gap-4 text-sm">
					<a href="/privacy" class="hover:text-blue-600">Privacy</a>
					<a href="/terms" class="hover:text-blue-600">Terms</a>
					<a href="#contact" class="hover:text-blue-600">Contact</a>
				</div>
			</div>
		</div>
	</footer>
	<script>
		const yearEl = document.getElementById('year');
		if (yearEl) yearEl.textContent = new Date().getFullYear();
		const btn = document.getElementById('menuBtn');
		const menu = document.getElementById('mobileMenu');
		if (btn && menu) btn.addEventListener('click', () => menu.classList.toggle('hidden'));
	</script>
</body>
</html>
