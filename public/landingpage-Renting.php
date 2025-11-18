<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>EcoMotion | Interurban Vehicle Rentals</title>
    <meta name="description" content="EcoMotion: Rent electric vehicles for interurban travel quickly, easily, and sustainably." />
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
                    <img src="/images/logo.jpg" alt="EcoMotion logo" class="w-8 h-8 rounded-full shadow" />
                    <span class="text-lg font-bold">EcoMotion</span>
                </a>
                <nav class="hidden md:flex items-center gap-8 text-sm">
                    <a href="#how" class="hover:text-blue-600 flex items-center gap-2">
                        <i class="fas fa-calendar-alt"></i>
                        How It Works
                    </a>
                    <a href="#benefits" class="hover:text-blue-600 flex items-center gap-2">
                        <i class="fas fa-leaf"></i>
                        Why Choose Us
                    </a>
                    <a href="#fleet" class="hover:text-blue-600 flex items-center gap-2">
                        <i class="fas fa-car"></i>
                        Our Fleet
                    </a>
                </nav>
                <div class="hidden md:flex items-center gap-3">
                    <a href="/auth/login" class="px-4 py-2 rounded-lg border border-blue-600 text-blue-700 font-semibold text-sm hover:bg-blue-50 transition">Login</a>
                    <a href="/register" class="px-4 py-2 rounded-lg border border-green-600 text-green-700 font-semibold text-sm hover:bg-green-50 transition">Register</a>
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
                        Interurban Electric Vehicle Rentals
                    </h1>
                    <p class="text-lg text-slate-600 max-w-xl mb-8">
                        Move between cities with comfort, flexibility, and sustainability. Rent your electric vehicle for interurban travel in just a few clicks.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-5 mb-8">
                        <a href="/auth/login" class="flex-1 px-6 py-4 rounded-xl bg-blue-600 text-white font-semibold text-lg hover:bg-blue-700 text-center shadow transition">Start Renting</a>
                    </div>
                </div>
                <div class="hidden lg:block">
					<div class="relative">
						<img src="/images/phoneDashboard.png" alt="Dashboard preview" class="w-80 h-auto object-contain rounded-lg mx-auto shadow-2xl" style="box-shadow:0 8px 32px 0 rgba(31, 38, 135, 0.25);">
					</div>
				</div>
            </div>
        </div>
    </section>
    <section id="how" class="py-16 bg-slate-50">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-dark text-center mb-8">How It Works</h2>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
                    <div class="w-24 h-24 flex items-center justify-center rounded-full bg-slate-200 mb-4">
                        <i class="fas fa-calendar-alt text-blue-600 text-4xl"></i>
                    </div>
                    <h3 class="font-semibold text-lg mb-2">Book Online</h3>
                    <p class="text-slate-600 text-center">Choose your route, date, and vehicle. Reserve in minutes from any device.</p>
                </div>
                <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
                    <div class="w-24 h-24 flex items-center justify-center rounded-full bg-slate-200 mb-4">
                        <i class="fas fa-car text-green-600 text-4xl"></i>
                    </div>
                    <h3 class="font-semibold text-lg mb-2">Pick Up & Go</h3>
                    <p class="text-slate-600 text-center">Pick up your electric vehicle at the selected location and start your interurban journey.</p>
                </div>
                <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
                    <div class="w-24 h-24 flex items-center justify-center rounded-full bg-slate-200 mb-4">
                        <i class="fas fa-map-marker-alt text-red-600 text-4xl"></i>
                    </div>
                    <h3 class="font-semibold text-lg mb-2">Return Easily</h3>
                    <p class="text-slate-600 text-center">Return the vehicle at your destination or at any of our partner locations.</p>
                </div>
            </div>
        </div>
    </section>
    <section id="benefits" class="py-16">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-dark text-center mb-8">Why Choose EcoMotion?</h2>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="flex flex-col items-center">
                    <div class="w-20 h-20 flex items-center justify-center rounded-full bg-slate-200 mb-4">
                        <i class="fas fa-leaf text-green-600 text-4xl"></i>
                    </div>
                    <h3 class="font-semibold text-lg mb-2">Sustainable Mobility</h3>
                    <p class="text-slate-600 text-center">All-electric fleet for zero-emission travel between cities.</p>
                </div>
                <div class="flex flex-col items-center">
                    <div class="w-20 h-20 flex items-center justify-center rounded-full bg-slate-200 mb-4">
                        <i class="fas fa-clock text-blue-600 text-4xl"></i>
                    </div>
                    <h3 class="font-semibold text-lg mb-2">Flexible & Convenient</h3>
                    <p class="text-slate-600 text-center">Book, pick up, and return at multiple locations. No hidden fees.</p>
                </div>
                <div class="flex flex-col items-center">
                    <div class="w-20 h-20 flex items-center justify-center rounded-full bg-slate-200 mb-4">
                        <i class="fas fa-car text-emerald-600 text-4xl"></i>
                    </div>
                    <h3 class="font-semibold text-lg mb-2">Modern Vehicles</h3>
                    <p class="text-slate-600 text-center">Enjoy the latest electric vehicles with advanced features and comfort.</p>
                </div>
            </div>
        </div>
    </section>
    <section id="fleet" class="py-16 bg-slate-50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-dark text-center mb-8">Our Fleet</h2>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
                    <div class="w-32 h-20 flex items-center justify-center rounded-lg bg-slate-200 mb-4">
                        <i class="fas fa-car-side text-blue-600 text-5xl"></i>
                    </div>
                    <h3 class="font-semibold text-lg mb-2">EcoMotion City EV</h3>
                    <p class="text-slate-600 text-center">Perfect for short and medium interurban trips. Range: 250km.</p>
                </div>
                <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
                    <div class="w-32 h-20 flex items-center justify-center rounded-lg bg-slate-200 mb-4">
                        <i class="fas fa-shuttle-van text-green-600 text-5xl"></i>
                    </div>
                    <h3 class="font-semibold text-lg mb-2">EcoMotion Family EV</h3>
                    <p class="text-slate-600 text-center">Spacious and comfortable for families or groups. Range: 350km.</p>
                </div>
                <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
                    <div class="w-32 h-20 flex items-center justify-center rounded-lg bg-slate-200 mb-4">
                        <i class="fas fa-car-alt text-emerald-600 text-5xl"></i>
                    </div>
                    <h3 class="font-semibold text-lg mb-2">EcoMotion Premium EV</h3>
                    <p class="text-slate-600 text-center">Luxury and performance for long interurban journeys. Range: 450km.</p>
                </div>
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
				</div>
			</div>
		</div>
	</footer>
</body>
</html>
