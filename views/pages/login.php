<?php /** Pantalla de acceso. */ ?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<meta name="csrf-token" content="<?= e(csrf_token()) ?>">
	<meta name="theme-color" content="#0f172a">
	<meta name="mobile-web-app-capable" content="yes">
	<title>Iniciar sesión · <?= e(config('app.short_name')) ?></title>
	<link rel="icon" href="<?= asset('img/icons/icon-96x96.png') ?>" type="image/png">
	<link rel="manifest" href="<?= app_url('manifest.json') ?>">
	<script src="https://cdn.tailwindcss.com"></script>
	<script>
		tailwind.config = {
			theme: {
				extend: {
					colors: { brand: { 50: '#fffbeb', 100: '#fef3c7', 400: '#fbbf24', 500: '#f59e0b', 600: '#d97706' }, ink: '#0f172a' },
					fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] },
				},
			},
		};
	</script>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
	<script src="<?= asset('js/csrf.js') ?>"></script>
	<style>
		@keyframes rise { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: none; } }
		.rise { animation: rise .5s ease-out both; }
		.hero-bg {
			background:
				radial-gradient(60% 50% at 85% 10%, rgba(245,200,66,.28), transparent 70%),
				radial-gradient(50% 50% at 0% 100%, rgba(245,158,11,.18), transparent 70%),
				#0f172a;
		}
		@media (max-width: 639px) { input { font-size: 16px !important; } }
	</style>
</head>
<body class="min-h-screen bg-slate-100 text-slate-800 font-sans antialiased">
<div class="min-h-screen lg:grid lg:grid-cols-[1.05fr_1fr]">

	<aside class="hero-bg hidden lg:flex flex-col justify-between p-12 text-white">
		<div class="flex items-center gap-3">
			<img src="<?= asset('img/Kalli-Amarillo.png') ?>" alt="" class="h-10">
			<span class="font-bold tracking-wide"><?= e(config('app.short_name')) ?></span>
		</div>
		<div class="max-w-md">
			<h1 class="text-4xl font-extrabold leading-tight">Todo tu inventario,<br><span class="text-brand-400">bajo control.</span></h1>
			<p class="mt-4 text-slate-300">Solicita insumos, surte pedidos y da seguimiento a cada sucursal desde un solo lugar.</p>
			<ul class="mt-8 space-y-3 text-sm text-slate-300">
				<li class="flex items-center gap-3"><span class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-brand-400"><i class="fa-solid fa-cart-shopping"></i></span>Pedidos con stock validado en tiempo real</li>
				<li class="flex items-center gap-3"><span class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-brand-400"><i class="fa-solid fa-scale-balanced"></i></span>Surtido y recepción por peso real</li>
				<li class="flex items-center gap-3"><span class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-brand-400"><i class="fa-solid fa-clipboard-list"></i></span>Requisiciones de compra con PDF</li>
			</ul>
		</div>
		<p class="text-xs text-slate-500">© <?= e(config('app.year')) ?> <?= e(config('app.company')) ?></p>
	</aside>

	<main class="flex min-h-screen items-center justify-center px-5 py-10 sm:px-8">
		<div class="w-full max-w-sm rise">
			<div class="lg:hidden text-center mb-8">
				<div class="inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-ink shadow-lg">
					<img src="<?= asset('img/Kalli-Amarillo.png') ?>" alt="Kalli Jaguar" class="h-10">
				</div>
				<p class="mt-3 font-bold text-ink"><?= e(config('app.short_name')) ?></p>
			</div>

			<h2 class="text-2xl font-extrabold text-ink">Bienvenido</h2>
			<p class="mt-1 text-sm text-slate-500">Ingresa tus credenciales para continuar.</p>

			<form id="loginForm" class="mt-8 space-y-5" autocomplete="on" novalidate>
				<div>
					<label for="login_usuario" class="block text-sm font-semibold text-slate-700 mb-1.5">Usuario</label>
					<div class="relative">
						<i class="fa-solid fa-user absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
						<input type="text" id="login_usuario" name="usuario" autocomplete="username" autocapitalize="none" autocorrect="off" spellcheck="false" maxlength="20" required autofocus
							class="w-full rounded-xl border border-slate-300 bg-white py-3 pl-11 pr-4 text-sm shadow-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-500/20"
							placeholder="Tu usuario">
					</div>
				</div>

				<div>
					<label for="login_clave" class="block text-sm font-semibold text-slate-700 mb-1.5">Contraseña</label>
					<div class="relative">
						<i class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
						<input type="password" id="login_clave" name="password" autocomplete="current-password" maxlength="100" required
							class="w-full rounded-xl border border-slate-300 bg-white py-3 pl-11 pr-12 text-sm shadow-sm outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-500/20"
							placeholder="••••••••">
						<button type="button" id="togglePassword" aria-label="Mostrar contraseña" class="absolute right-0 top-0 h-full w-12 text-slate-400 hover:text-brand-600 transition">
							<i class="fa-solid fa-eye" id="eyeIcon"></i>
						</button>
					</div>
					<p id="capsWarn" class="hidden mt-1.5 text-xs text-amber-600"><i class="fa-solid fa-triangle-exclamation mr-1"></i>Bloq Mayús está activado</p>
				</div>

				<div id="loginMessage" class="hidden" role="alert" aria-live="polite"></div>

				<button type="submit" id="loginBtn"
					class="w-full rounded-xl bg-brand-500 px-4 py-3 text-sm font-bold text-ink shadow-lg shadow-brand-500/30 transition hover:bg-brand-400 focus:outline-none focus:ring-4 focus:ring-brand-500/30 active:scale-[.99] disabled:cursor-not-allowed disabled:opacity-70">
					<span id="loginBtnText"><i class="fa-solid fa-arrow-right-to-bracket mr-2"></i>Iniciar sesión</span>
				</button>
			</form>

			<p class="mt-8 flex items-center justify-center gap-2 text-xs text-slate-400">
				<i class="fa-solid fa-shield-halved text-brand-500"></i>Conexión segura · Acceso restringido
			</p>
			<p class="lg:hidden mt-4 text-center text-[11px] text-slate-400">© <?= e(config('app.year')) ?> <?= e(config('app.company')) ?></p>
		</div>
	</main>
</div>

<script>
	const $ = (id) => document.getElementById(id);
	const AUTH_URL = <?= json_encode(app_url('api/AuthController/login.php')) ?>;
	const ICONS = {
		error: ['bg-red-50 border-red-200 text-red-700', 'fa-circle-exclamation'],
		warning: ['bg-amber-50 border-amber-200 text-amber-700', 'fa-triangle-exclamation'],
		success: ['bg-emerald-50 border-emerald-200 text-emerald-700', 'fa-circle-check'],
	};

	function showMessage(text, type) {
		const [cls, icon] = ICONS[type] || ICONS.error;
		const box = $('loginMessage');
		box.innerHTML = '<div class="flex items-start gap-2 rounded-xl border px-3 py-2.5 text-sm ' + cls + '"><i class="fa-solid ' + icon + ' mt-0.5"></i><span></span></div>';
		box.querySelector('span').textContent = text;
		box.classList.remove('hidden');
	}

	function setLoading(on) {
		$('loginBtn').disabled = on;
		$('loginBtnText').innerHTML = on
			? '<i class="fa-solid fa-spinner fa-spin mr-2"></i>Verificando...'
			: '<i class="fa-solid fa-arrow-right-to-bracket mr-2"></i>Iniciar sesión';
	}

	$('togglePassword').addEventListener('click', () => {
		const input = $('login_clave');
		const show = input.type === 'password';
		input.type = show ? 'text' : 'password';
		$('eyeIcon').className = show ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
	});

	$('login_clave').addEventListener('keyup', (e) => {
		$('capsWarn').classList.toggle('hidden', !(e.getModifierState && e.getModifierState('CapsLock')));
	});

	$('loginForm').addEventListener('submit', async (e) => {
		e.preventDefault();
		if ($('loginBtn').disabled) return;

		const usuario = $('login_usuario').value.trim();
		const password = $('login_clave').value;

		if (!usuario || !password) return showMessage('Completa usuario y contraseña.', 'error');
		if (!/^[a-zA-Z0-9]{4,20}$/.test(usuario)) return showMessage('El usuario debe tener de 4 a 20 caracteres alfanuméricos.', 'error');
		if (password.length < 7 || password.length > 100) return showMessage('La contraseña debe tener entre 7 y 100 caracteres.', 'error');

		setLoading(true);
		const ctrl = new AbortController();
		const timer = setTimeout(() => ctrl.abort(), 12000);
		try {
			const body = new FormData();
			body.append('usuario', usuario);
			body.append('password', password);
			const res = await fetch(AUTH_URL, {
				method: 'POST', body, credentials: 'same-origin', signal: ctrl.signal,
				headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
			});
			let data = null;
			try { data = await res.json(); } catch (_) {}

			if (data && data.success) {
				showMessage('Acceso correcto. Redirigiendo...', 'success');
				window.location.href = data.redirect || <?= json_encode(app_url()) ?>;
				return;
			}
			if (res.status === 419) {
				showMessage('La sesión caducó. Recarga la página e inténtalo de nuevo.', 'error');
			} else {
				showMessage((data && data.message) || 'Credenciales incorrectas.', 'error');
				if (data && data.attempts_remaining > 0) {
					setTimeout(() => showMessage('Te quedan ' + data.attempts_remaining + ' intentos antes del bloqueo.', 'warning'), 2500);
				}
			}
			$('login_clave').select();
			setLoading(false);
		} catch (err) {
			showMessage(err.name === 'AbortError' ? 'Tiempo de espera agotado. Revisa tu conexión.' : 'Error de conexión. Inténtalo de nuevo.', 'error');
			setLoading(false);
		} finally {
			clearTimeout(timer);
		}
	});
</script>
</body>
</html>
