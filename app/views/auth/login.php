<?php
function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(16)); }
$errors = $errors ?? [];
$old = $old ?? [];
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<title>Login — EcoMotion</title>
	<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white text-slate-800 font-sans">
	<div class="min-h-screen flex items-center justify-center px-4 py-12">
		<div class="max-w-md w-full bg-white border border-slate-200 rounded-2xl p-8 shadow-sm">
			<div class="text-center mb-6">
				<svg class="mx-auto w-10 h-10 text-blue-600" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l3 6 6 .9-4.5 4.4L17 20l-5-2.7L7 20l1.5-6.7L4 8.9 10 8l2-6z"></path></svg>
				<h1 class="text-2xl font-extrabold mt-3">Sign in</h1>
			</div>

			<?php if (!empty($errors)): ?>
				<div class="mb-4 rounded-md bg-red-50 border border-red-100 p-3 text-red-700 text-sm">
					<ul class="list-disc list-inside">
						<?php foreach($errors as $err) echo '<li>' . e($err) . '</li>'; ?>
					</ul>
				</div>
			<?php endif; ?>

			<form method="post" action="/login" class="space-y-4">
				<input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

				<div>
					<label class="block text-sm font-medium text-slate-700" for="email">Email</label>
					<input id="email" name="email" type="email" value="<?= e($old['email'] ?? '') ?>" required class="mt-1 block w-full rounded-lg border border-slate-200 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
				</div>

				<div>
					<label class="block text-sm font-medium text-slate-700" for="password">Password</label>
					<input id="password" name="password" type="password" required class="mt-1 block w-full rounded-lg border border-slate-200 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
				</div>

				<div>
					<button type="submit" class="w-full inline-flex justify-center items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white font-medium hover:bg-blue-700">Sign in</button>
				</div>
			</form>

			<p class="mt-4 text-center text-sm text-slate-500">
				No account? <a href="/register" class="text-blue-600 hover:underline">Create one</a>
			</p>
		</div>
	</div>
</body>
</html>
