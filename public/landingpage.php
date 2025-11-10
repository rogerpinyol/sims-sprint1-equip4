<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>EcoMotion | Fleet Management SaaS</title>
	<meta name="description" content="EcoMotion is a modern fleet management platform to monitor, optimize, and scale your vehicles with ease." />
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
	<!-- Optional: Basic Open Graph -->
	<meta property="og:title" content="EcoMotion | Fleet Management SaaS">
	<meta property="og:description" content="Monitor, optimize, and scale your electric fleet.">
	<meta property="og:type" content="website">
	<meta property="og:image" content="/og-image.png">
		<!-- Favicon -->
		<link rel="icon" type="image/svg+xml" href="/images/logo.jpg">
		<link rel="apple-touch-icon" href="/apple-touch-icon.png">
</head>
<body class="bg-white text-slate-800 font-sans">
	<!-- Navbar -->
	<header class="sticky top-0 z-40 bg-white/80 backdrop-blur border-b border-slate-200">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="flex h-16 items-center justify-between">
				<a href="#" class="flex items-center gap-2">
					<img src="/images/logo.jpg" alt="EcoMotion logo" class="w-8 h-8 rounded-full shadow" />
					<span class="text-lg font-bold">EcoMotion</span>
				</a>
				<nav class="hidden md:flex items-center gap-8 text-sm">
					<a href="#features" class="hover:text-blue-600">Features</a>
					<a href="#integrations" class="hover:text-blue-600">Integrations</a>
					<a href="#pricing" class="hover:text-blue-600">Pricing</a>
					<a href="#testimonials" class="hover:text-blue-600">Customers</a>
				</nav>
				<div class="hidden md:flex items-center gap-3">
					<a href="/client/register" class="px-4 py-2 rounded-lg border border-blue-600 text-blue-700 font-semibold text-sm hover:bg-blue-50 transition">Register</a>
					<a href="/client/login" class="px-4 py-2 rounded-lg border border-emerald-600 text-emerald-700 font-semibold text-sm hover:bg-emerald-50 transition">Login</a>
				</div>
				<!-- Mobile menu button -->
				<button id="menuBtn" class="md:hidden p-2 rounded-lg border border-slate-300" aria-label="Open menu">
					<svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
				</button>
			</div>
			<!-- Mobile menu -->
			<div id="mobileMenu" class="md:hidden hidden py-4 space-y-2">
				<a href="#features" class="block px-2 py-2 rounded hover:bg-slate-50">Features</a>
				<a href="#integrations" class="block px-2 py-2 rounded hover:bg-slate-50">Integrations</a>
				<a href="#pricing" class="block px-2 py-2 rounded hover:bg-slate-50">Pricing</a>
				<a href="#testimonials" class="block px-2 py-2 rounded hover:bg-slate-50">Customers</a>
				<div class="pt-2 flex gap-2">
					<a href="/admin/login" class="flex-1 px-4 py-2 text-center rounded-lg border border-blue-600 text-blue-700 font-semibold text-sm hover:bg-blue-50 transition">Admin login</a>
				</div>
			</div>
		</div>
	</header>

	<!-- Hero -->



	<section class="relative overflow-hidden">
		<div class="absolute inset-0 bg-gradient-to-br from-brand-50 via-white to-white" aria-hidden="true"></div>
		<div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 sm:py-28">
			<div class="grid lg:grid-cols-2 gap-12 items-center">
				<div>
					<h1 class="text-4xl sm:text-5xl font-extrabold leading-tight text-dark mb-6">
						Welcome to EcoMotion
					</h1>
					<p class="text-lg text-slate-600 max-w-xl mb-8">
						The platform for companies to manage, optimize, and scale their vehicle fleets, and for customers to easily rent and enjoy electric mobility.
					</p>
					<div class="flex flex-col sm:flex-row gap-5 mb-8">
						<a href="/admin/login" class="flex-1 px-6 py-4 rounded-xl bg-blue-600 text-white font-semibold text-lg hover:bg-blue-700 text-center shadow transition">I'm an Administrator</a>
					</div>
					<div class="mt-8 flex items-center gap-6 text-sm text-slate-500">
						<div class="flex items-center gap-2"><span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>99.95% uptime</div>
						<div>ISO27001-ready</div>
						<div>GDPR compliant</div>
					</div>
				</div>
				<div class="hidden lg:block">
					<div class="relative bg-white rounded-2xl shadow-md">
						<img src="/images/adminDashboard.png" alt="Dashboard preview" class="rounded-lg">
						<div class="absolute -bottom-6 -right-6 w-40 h-40 bg-brand-100 rounded-full blur-2xl opacity-70" aria-hidden="true"></div>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- Features -->
	<section id="features" class="py-20 sm:py-24">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="text-center max-w-3xl mx-auto">
				<h2 class="text-3xl sm:text-4xl font-bold text-dark">Everything you need to run a modern fleet</h2>
				<p class="mt-4 text-slate-600">From real-time tracking to automated maintenance and billing—EcoMotion is your single source of truth.</p>
			</div>
			<div class="mt-12 grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
				<div class="p-6 rounded-2xl border border-slate-200">
					<div class="w-12 h-12 rounded-xl bg-brand-100 text-brand-700 flex items-center justify-center mb-4">
						<svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor"><path d="M12 3l9 4-9 4-9-4 9-4zm0 7l9 4-9 4-9-4 9-4z"/></svg>
					</div>
					<h3 class="font-semibold text-lg">Live vehicle tracking</h3>
					<p class="mt-2 text-slate-600">Monitor location, battery health, and status in real time across your entire fleet.</p>
				</div>
				<div class="p-6 rounded-2xl border border-slate-200">
					<div class="w-12 h-12 rounded-xl bg-brand-100 text-brand-700 flex items-center justify-center mb-4">
						<svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor"><path d="M4 6h16M4 12h10M4 18h7"/></svg>
					</div>
					<h3 class="font-semibold text-lg">Smart scheduling</h3>
					<p class="mt-2 text-slate-600">Optimize bookings and dispatch with rules that balance utilization and battery charging.</p>
				</div>
				<div class="p-6 rounded-2xl border border-slate-200">
					<div class="w-12 h-12 rounded-xl bg-brand-100 text-brand-700 flex items-center justify-center mb-4">
						<svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor"><path d="M12 8a4 4 0 100 8 4 4 0 000-8z"/></svg>
					</div>
					<h3 class="font-semibold text-lg">Maintenance automation</h3>
					<p class="mt-2 text-slate-600">Forecast service intervals and trigger workflows before issues impact operations.</p>
				</div>
				<div class="p-6 rounded-2xl border border-slate-200">
					<div class="w-12 h-12 rounded-xl bg-brand-100 text-brand-700 flex items-center justify-center mb-4">
						<svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor"><path d="M20 7H4v10h16V7zM4 5a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V7a2 2 0 00-2-2H4z"/></svg>
					</div>
					<h3 class="font-semibold text-lg">Unified billing</h3>
					<p class="mt-2 text-slate-600">Consolidate pay-per-use, subscriptions, and partner invoices in one clean ledger.</p>
				</div>
				<div class="p-6 rounded-2xl border border-slate-200">
					<div class="w-12 h-12 rounded-xl bg-brand-100 text-brand-700 flex items-center justify-center mb-4">
						<svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor"><path d="M3 3h18v2H3zM3 19h18v2H3zM3 9h18v6H3z"/></svg>
					</div>
					<h3 class="font-semibold text-lg">Analytics & reporting</h3>
					<p class="mt-2 text-slate-600">Understand costs, utilization, and performance with prebuilt and custom reports.</p>
				</div>
				<div class="p-6 rounded-2xl border border-slate-200">
					<div class="w-12 h-12 rounded-xl bg-brand-100 text-brand-700 flex items-center justify-center mb-4">
						<svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 100 20 10 10 0 000-20z"/></svg>
					</div>
					<h3 class="font-semibold text-lg">Developer-friendly</h3>
					<p class="mt-2 text-slate-600">Robust API and webhooks to integrate EcoMotion into your existing stack.</p>
				</div>
			</div>
		</div>
	</section>

	<!-- Integrations -->
	<section id="integrations" class="py-16 bg-slate-50">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="grid md:grid-cols-2 gap-10 items-center">
				<div>
					<h2 class="text-2xl sm:text-3xl font-bold text-dark">Plug into your ecosystem</h2>
					<p class="mt-4 text-slate-600">Connect ERPs, accounting tools, telematics providers, and identity platforms in minutes.</p>
					<ul class="mt-6 space-y-3 text-slate-700">
						<li class="flex items-center gap-3"><span class="w-2 h-2 bg-brand-500 rounded-full"></span>REST API and webhooks</li>
						<li class="flex items-center gap-3"><span class="w-2 h-2 bg-brand-500 rounded-full"></span>SSO (SAML, OAuth)</li>
						<li class="flex items-center gap-3"><span class="w-2 h-2 bg-brand-500 rounded-full"></span>CSV import/export</li>
					</ul>
				</div>
				<div class="grid grid-cols-3 gap-4">
					<div class="p-6 rounded-xl bg-white border border-slate-200 text-center">SAP</div>
					<div class="p-6 rounded-xl bg-white border border-slate-200 text-center">Oracle</div>
					<div class="p-6 rounded-xl bg-white border border-slate-200 text-center">Stripe</div>
					<div class="p-6 rounded-xl bg-white border border-slate-200 text-center">Salesforce</div>
					<div class="p-6 rounded-xl bg-white border border-slate-200 text-center">Okta</div>
					<div class="p-6 rounded-xl bg-white border border-slate-200 text-center">Twilio</div>
				</div>
			</div>
		</div>
	</section>

	<!-- Pricing -->
	<section id="pricing" class="py-20 sm:py-24">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="text-center">
				<h2 class="text-3xl sm:text-4xl font-bold text-dark">Simple, transparent pricing</h2>
				<p class="mt-4 text-slate-600">Start free and scale with usage. No hidden fees.</p>
			</div>
			<div class="mt-12 grid gap-6 md:grid-cols-3">
				<!-- Starter -->
				<div class="rounded-2xl border border-slate-200 p-6">
					<h3 class="text-lg font-semibold">Starter</h3>
					<p class="mt-2 text-slate-600">For small teams getting started.</p>
					<div class="mt-6 text-4xl font-extrabold">$0<span class="text-base font-medium text-slate-500">/mo</span></div>
					<ul class="mt-6 space-y-2 text-sm text-slate-700">
						<li>Up to 10 vehicles</li>
						<li>Basic reports</li>
						<li>Email support</li>
					</ul>
					<a href="/register" class="mt-6 block px-5 py-3 text-center rounded-xl bg-slate-900 text-white font-medium hover:bg-slate-800">Get started</a>
				</div>
				<!-- Growth -->
				<div class="rounded-2xl border-2 border-brand-500 p-6 bg-brand-50">
					<h3 class="text-lg font-semibold flex items-center gap-2">Growth <span class="text-xs px-2 py-1 rounded-full bg-blue-600 text-white">Popular</span></h3>
					<p class="mt-2 text-slate-700">For growing fleets that need automation.</p>
					<div class="mt-6 text-4xl font-extrabold">$299<span class="text-base font-medium text-slate-500">/mo</span></div>
					<ul class="mt-6 space-y-2 text-sm text-slate-800">
						<li>Up to 200 vehicles</li>
						<li>Scheduling & maintenance automation</li>
						<li>API access</li>
						<li>Priority support</li>
					</ul>
					<a href="/register" class="mt-6 block px-5 py-3 text-center rounded-xl bg-blue-600 text-white font-medium hover:bg-blue-700">Try Growth</a>
				</div>
				<!-- Enterprise -->
				<div class="rounded-2xl border border-slate-200 p-6">
					<h3 class="text-lg font-semibold">Enterprise</h3>
					<p class="mt-2 text-slate-600">For complex fleets and compliance needs.</p>
					<div class="mt-6 text-4xl font-extrabold">Custom</div>
					<ul class="mt-6 space-y-2 text-sm text-slate-700">
						<li>Unlimited vehicles</li>
						<li>SSO, audit logs, custom SLAs</li>
						<li>Dedicated manager</li>
					</ul>
					<a href="/register" class="mt-6 block px-5 py-3 text-center rounded-xl border border-slate-300 font-medium hover:bg-slate-50">Contact sales</a>
				</div>
			</div>
		</div>
	</section>

	<!-- Testimonials -->
	<section id="testimonials" class="py-16 bg-slate-50">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="grid md:grid-cols-3 gap-6">
				<div class="p-6 rounded-2xl border border-slate-200 bg-white">
					<p class="text-slate-700">“EcoMotion cut our downtime by 27% in the first quarter. The scheduling engine is a game changer.”</p>
					<div class="mt-4 text-sm font-semibold">Alex Johnson, Ops Lead</div>
					<div class="text-xs text-slate-500">VoltRide</div>
				</div>
				<div class="p-6 rounded-2xl border border-slate-200 bg-white">
					<p class="text-slate-700">“The dashboards finally give finance and operations a shared view. Our forecasts are much tighter.”</p>
					<div class="mt-4 text-sm font-semibold">Priya Das, CFO</div>
					<div class="text-xs text-slate-500">UrbanMove</div>
				</div>
				<div class="p-6 rounded-2xl border border-slate-200 bg-white">
					<p class="text-slate-700">“Integration took a week, not months. The API and webhooks are solid.”</p>
					<div class="mt-4 text-sm font-semibold">Marco Ruiz, CTO</div>
					<div class="text-xs text-slate-500">FleetX</div>
				</div>
			</div>
		</div>
	</section>

	<!-- CTA -->
	<section id="cta" class="py-20">
		<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
			<h2 class="text-3xl sm:text-4xl font-bold text-dark">Ready to electrify your operations?</h2>
			<p class="mt-4 text-slate-600">Start your 14‑day free trial. No credit card required.</p>
			<form class="mt-8 max-w-xl mx-auto flex flex-col sm:flex-row gap-3">
				<input type="email" required placeholder="Enter your work email" class="flex-1 px-4 py-3 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500" />
				<button type="submit" class="px-6 py-3 rounded-xl bg-blue-600 text-white font-medium hover:bg-blue-700">Get started</button>
			</form>
			<p class="mt-3 text-xs text-slate-500">By signing up, you agree to our Terms and Privacy Policy.</p>
		</div>
	</section>

	<!-- Footer -->
	<footer class="border-t border-slate-200 py-10">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="flex flex-col md:flex-row items-center justify-between gap-4">
				<div class="flex items-center gap-2">
						<svg class="w-6 h-6 text-blue-600" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
						<path d="M12 2l3 6 6 .9-4.5 4.4L17 20l-5-2.7L7 20l1.5-6.7L4 8.9 10 8l2-6z"></path>
					</svg>
					<span class="font-semibold">EcoMotion</span>
				</div>
				<div class="text-sm text-slate-500">© <span id="year"></span> EcoMotion. All rights reserved.</div>
				<div class="flex gap-4 text-sm">
								<a href="/privacy" class="hover:text-blue-600">Privacy</a>
								<a href="/terms" class="hover:text-blue-600">Terms</a>
					<a href="#" class="hover:text-blue-600">Security</a>
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
