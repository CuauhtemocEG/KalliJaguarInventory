<!DOCTYPE html>
<html lang="es">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
	<meta name="format-detection" content="telephone=no">
	<meta name="mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
	<title>Kalli Jaguar Inventory - Login</title>
	<script src="https://cdn.tailwindcss.com"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
	<link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
	<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
	<script>
		tailwind.config = {
			theme: {
				extend: {
					colors: {
						'inventory-blue': '#1e40af',
						'inventory-dark': '#1e293b',
						'accent-yellow': '#F5C842',
						'accent-yellow-dark': '#E5B832',
					},
					fontFamily: {
						'montserrat': ['Montserrat', 'sans-serif'],
					},
					animation: {
						'fade-in': 'fadeIn 0.6s ease-out',
						'slide-up': 'slideUp 0.5s ease-out',
						'pulse-slow': 'pulse 3s infinite',
					}
				}
			}
		}
	</script>
	<style>
		@keyframes fadeIn {
			from {
				opacity: 0;
				transform: translateY(20px);
			}

			to {
				opacity: 1;
				transform: translateY(0);
			}
		}

		@keyframes slideUp {
			from {
				transform: translateY(30px);
				opacity: 0;
			}

			to {
				transform: translateY(0);
				opacity: 1;
			}
		}

		.glass-effect {
			backdrop-filter: blur(20px);
			background: rgba(255, 255, 255, 0.1);
		}

		/* Fallback para navegadores que no soportan backdrop-filter */
		@supports not (backdrop-filter: blur(20px)) {
			.glass-effect {
				background: rgba(255, 255, 255, 0.15);
				border: 1px solid rgba(255, 255, 255, 0.2);
			}
		}

		.inventory-gradient {
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
		}

		.input-focus:focus {
			transform: scale(1.02);
			transition: all 0.3s ease;
		}

		/* Optimizaciones móviles mejoradas */
		@media (max-width: 640px) {
			.input-focus:focus {
				transform: scale(1.01);
			}

			body {
				-webkit-text-size-adjust: 100%;
				-webkit-tap-highlight-color: transparent;
				font-size: 16px;
				/* Prevenir zoom en iOS */
			}

			input[type="text"],
			input[type="password"] {
				-webkit-appearance: none;
				-moz-appearance: none;
				appearance: none;
				font-size: 16px;
				/* Prevenir zoom en iOS */
			}

			button {
				-webkit-tap-highlight-color: transparent;
			}
		}

		/* Optimizaciones para dispositivos táctiles */
		@media (hover: none) and (pointer: coarse) {

			button,
			input {
				min-height: 44px;
			}

			.input-focus:focus {
				transform: none;
			}
		}

		/* Mejoras para desktop */
		@media (min-width: 1024px) {
			.glass-effect {
				backdrop-filter: blur(25px);
			}
		}
	</style>
</head>

<body class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-slate-800 font-montserrat">
	<div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width=" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg" fill="none" fill-rule="evenodd" fill="%23ffffff" fill-opacity="0.03" cx="30" cy="30" r="1"></div>

	<div class="absolute top-20 left-4 sm:left-20 w-16 h-16 sm:w-32 sm:h-32 bg-accent-yellow opacity-10 rounded-full animate-pulse-slow"></div>
	<div class="absolute bottom-20 right-4 sm:right-20 w-12 h-12 sm:w-24 sm:h-24 bg-blue-400 opacity-15 rounded-full animate-pulse-slow" style="animation-delay: 1s;"></div>
	<div class="absolute top-1/2 left-2 sm:left-10 w-8 h-8 sm:w-16 sm:h-16 bg-purple-400 opacity-10 rounded-full animate-pulse-slow" style="animation-delay: 2s;"></div>

	<div class="flex items-center justify-center min-h-screen px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
		<div class="w-full max-w-sm sm:max-w-md animate-fade-in">
			<div class="text-center mb-6 sm:mb-8 animate-slide-up">
				<div class="inline-flex items-center justify-center w-20 h-20 sm:w-24 sm:h-24 bg-gradient-to-br from-gray-900 to-black rounded-2xl shadow-2xl mb-4 sm:mb-6 transform hover:scale-105 transition-all duration-300 border border-gray-700">
					<img src="./img/Kalli-Amarillo.png" alt="Kalli Jaguar" class="w-12 h-12 sm:w-16 sm:h-16 object-contain">
				</div>
				<h1 class="text-3xl sm:text-4xl font-bold text-white mb-2">Kalli Jaguar</h1>
				<p class="text-blue-200 text-base sm:text-lg font-medium">Sistema de Inventario</p>
				<div class="w-20 sm:w-24 h-1 bg-gradient-to-r from-accent-yellow to-transparent mx-auto mt-3 sm:mt-4 rounded-full"></div>
			</div>

			<div class="glass-effect border border-white/20 rounded-2xl sm:rounded-3xl shadow-2xl p-6 sm:p-8 backdrop-blur-xl animate-slide-up" style="animation-delay: 0.2s;">
				<div class="text-center mb-6 sm:mb-8">
					<h2 class="text-xl sm:text-2xl font-bold text-white mb-2">Iniciar Sesión</h2>
					<p class="text-blue-200 text-sm sm:text-base">Accede a tu panel de control</p>
				</div>

				<form id="loginForm" class="space-y-5 sm:space-y-6" autocomplete="off">
					<div class="relative group">
						<div class="absolute inset-y-0 left-0 pl-3 sm:pl-4 flex items-center pointer-events-none">
							<i class="fas fa-user text-blue-300 group-focus-within:text-accent-yellow transition-colors duration-300"></i>
						</div>
						<input
							type="text"
							name="login_usuario"
							id="login_usuario"
							class="input-focus w-full pl-10 sm:pl-12 pr-3 sm:pr-4 py-3 sm:py-4 bg-white/10 border border-white/20 rounded-xl sm:rounded-2xl text-white placeholder-blue-200 focus:bg-white/20 focus:border-accent-yellow focus:outline-none focus:ring-2 focus:ring-accent-yellow/50 transition-all duration-300 text-sm sm:text-base"
							placeholder="Nombre de usuario"
							required>
					</div>

					<div class="relative group">
						<div class="absolute inset-y-0 left-0 pl-3 sm:pl-4 flex items-center pointer-events-none">
							<i class="fas fa-lock text-blue-300 group-focus-within:text-accent-yellow transition-colors duration-300"></i>
						</div>
						<input
							type="password"
							name="login_clave"
							id="login_clave"
							class="input-focus w-full pl-10 sm:pl-12 pr-10 sm:pr-12 py-3 sm:py-4 bg-white/10 border border-white/20 rounded-xl sm:rounded-2xl text-white placeholder-blue-200 focus:bg-white/20 focus:border-accent-yellow focus:outline-none focus:ring-2 focus:ring-accent-yellow/50 transition-all duration-300 text-sm sm:text-base"
							placeholder="Contraseña"
							required>
						<button
							type="button"
							id="togglePassword"
							class="absolute inset-y-0 right-0 pr-3 sm:pr-4 flex items-center text-blue-300 hover:text-accent-yellow transition-colors duration-300">
							<i class="fas fa-eye" id="eyeIcon"></i>
						</button>
					</div>

					<div id="loginMessage" class="hidden"></div>

					<button
						type="submit"
						id="loginBtn"
						class="w-full bg-gradient-to-r from-accent-yellow to-accent-yellow-dark hover:from-accent-yellow-dark hover:to-accent-yellow text-inventory-dark font-bold py-3 sm:py-4 px-4 sm:px-6 rounded-xl sm:rounded-2xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-accent-yellow/50 relative overflow-hidden group text-sm sm:text-base">
						<span class="relative z-10 flex items-center justify-center">
							<i class="fas fa-sign-in-alt mr-2 sm:mr-3 group-hover:rotate-12 transition-transform duration-300"></i>
							<span id="loginBtnText">Iniciar Sesión</span>
						</span>
						<div class="absolute inset-0 bg-white/20 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300 origin-left"></div>
					</button>

					<div class="text-center pt-4 sm:pt-6 border-t border-white/10">
						<p class="text-blue-200 text-xs sm:text-sm flex items-center justify-center">
							<i class="fas fa-shield-alt mr-2 text-accent-yellow"></i>
							Acceso seguro y encriptado
						</p>
					</div>
				</form>
			</div>

			<div class="text-center mt-6 sm:mt-8 animate-slide-up" style="animation-delay: 0.4s;">
				<p class="text-blue-300 text-xs sm:text-sm">
					© 2025 Kalli Jaguar. Sistema de Gestión de Inventario.
				</p>
			</div>
		</div>
	</div>

	<script>
		const isAndroid = /Android/i.test(navigator.userAgent);
		const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
		const isMobile = /Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
		const isChrome = /Chrome/i.test(navigator.userAgent);

		document.getElementById('togglePassword').addEventListener('click', function() {
			const passwordInput = document.getElementById('login_clave');
			const eyeIcon = document.getElementById('eyeIcon');

			if (passwordInput.type === 'password') {
				passwordInput.type = 'text';
				eyeIcon.className = 'fas fa-eye-slash';
			} else {
				passwordInput.type = 'password';
				eyeIcon.className = 'fas fa-eye';
			}
		});

		async function makeAuthRequest(endpoint, formData, retryCount = 0) {
			const maxRetries = 2;
			const timeout = 8000;

			const controller = new AbortController();
			const timeoutId = setTimeout(() => controller.abort(), timeout);

			try {
				const response = await fetch(`api/AuthController/${endpoint}`, {
					method: "POST",
					body: formData,
					credentials: 'same-origin',
					cache: 'no-cache',
					signal: controller.signal,
					headers: {
						'X-Requested-With': 'XMLHttpRequest',
						'Accept': 'application/json'
					}
				});

				clearTimeout(timeoutId);

				if (!response.ok) {
					throw new Error(`HTTP ${response.status}: ${response.statusText}`);
				}

				const contentType = response.headers.get("content-type");
				if (!contentType || !contentType.includes("application/json")) {
					const textResponse = await response.text();
					throw new Error("Respuesta inválida del servidor");
				}

				return await response.json();

			} catch (error) {
				clearTimeout(timeoutId);

				if (retryCount < maxRetries && (
						error.name === 'AbortError' ||
						error.message.includes('Failed to fetch') ||
						error.message.includes('Network request failed')
					)) {
					await new Promise(resolve => setTimeout(resolve, 1000 * (retryCount + 1)));
					return makeAuthRequest(endpoint, formData, retryCount + 1);
				}

				throw error;
			}
		}

		document.getElementById("loginForm").addEventListener("submit", async function(e) {
			e.preventDefault();

			const usuario = document.getElementById("login_usuario").value.trim();
			const password = document.getElementById("login_clave").value.trim();

			if (!usuario || !password) {
				showMessage("Por favor, complete todos los campos.", "error");
				return;
			}

			if (usuario.length < 4 || usuario.length > 20) {
				showMessage('El usuario debe tener entre 4 y 20 caracteres', 'error');
				return;
			}

			if (password.length < 7 || password.length > 100) {
				showMessage('La contraseña debe tener entre 7 y 100 caracteres', 'error');
				return;
			}

			setLoadingState(true);

			try {
				const formData = new FormData();
				formData.append("usuario", usuario);
				formData.append("password", password);

				const data = await makeAuthRequest('login.php', formData);

				if (data && data.success) {
					showMessage("¡Bienvenido! Redirigiendo...", "success");

					if (data.user) {
						sessionStorage.setItem('user_info', JSON.stringify(data.user));
					}

					setTimeout(() => {
						window.location.href = data.redirect || 'index.php?page=home';
					}, 1500);
				} else {
					let errorMessage = data?.message || "Credenciales incorrectas.";

					switch (data?.code) {
						case 'ACCOUNT_SUSPENDED':
							errorMessage = '🚫 ' + errorMessage;
							break;
						case 'ACCOUNT_INACTIVE':
							errorMessage = '⚠️ ' + errorMessage;
							break;
						case 'ACCOUNT_LOCKED':
							errorMessage = '🔒 ' + errorMessage;
							break;
						case 'INVALID_CREDENTIALS':
							errorMessage = '🔑 ' + errorMessage;
							break;
						default:
							errorMessage = '❌ ' + errorMessage;
					}

					showMessage(errorMessage, "error");

					if (data?.attempts_remaining !== undefined && data.attempts_remaining > 0) {
						setTimeout(() => {
							showMessage(`Le quedan ${data.attempts_remaining} intentos antes del bloqueo`, 'warning');
						}, 3000);
					}

					setLoadingState(false);
				}

			} catch (error) {
				let errorMessage = "Error de conexión. Inténtalo de nuevo.";
				if (error.name === 'AbortError') {
					errorMessage = "Tiempo de espera agotado. Verifica tu conexión.";
				} else if (error.message.includes('HTTP')) {
					errorMessage = "Error del servidor. Inténtalo más tarde.";
				} else if (error.message.includes('JSON') || error.message.includes('inválida')) {
					errorMessage = "Error en la respuesta del servidor.";
				}

				showMessage(errorMessage, "error");
				setLoadingState(false);
			}
		});

		function redirectToHome() {
			const homeUrl = "index.php?page=home";

			try {
				if (isAndroid || isMobile) {
					window.location.replace(homeUrl);

					setTimeout(() => {
						if (window.location.href.includes('login.php')) {
							window.location.href = homeUrl;
						}
					}, 1000);
				} else {
					window.location.href = homeUrl;
				}
			} catch (error) {
				document.location = homeUrl;
			}
		}

		function setLoadingState(isLoading) {
			const loginBtn = document.getElementById("loginBtn");
			const loginBtnText = document.getElementById("loginBtnText");

			if (isLoading) {
				loginBtn.disabled = true;
				loginBtn.classList.add("opacity-75", "cursor-not-allowed");
				loginBtnText.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Iniciando sesión...';
			} else {
				loginBtn.disabled = false;
				loginBtn.classList.remove("opacity-75", "cursor-not-allowed");
				loginBtnText.textContent = 'Iniciar Sesión';
			}
		}

		function showMessage(message, type) {
			const msgDiv = document.getElementById("loginMessage");
			const isError = type === "error";
			const isWarning = type === "warning";
			const isSuccess = type === "success";

			let bgClass, textClass, borderClass, iconClass;

			if (isError) {
				bgClass = 'bg-red-500/20';
				borderClass = 'border-red-500/50';
				textClass = 'text-red-200';
				iconClass = 'fa-exclamation-triangle';
			} else if (isWarning) {
				bgClass = 'bg-yellow-500/20';
				borderClass = 'border-yellow-500/50';
				textClass = 'text-yellow-200';
				iconClass = 'fa-exclamation-circle';
			} else {
				bgClass = 'bg-green-500/20';
				borderClass = 'border-green-500/50';
				textClass = 'text-green-200';
				iconClass = 'fa-check-circle';
			}

			msgDiv.innerHTML = `
                <div class="flex items-center p-3 sm:p-4 rounded-xl sm:rounded-2xl ${bgClass} border ${borderClass} ${textClass} backdrop-blur-sm">
                    <i class="fas ${iconClass} mr-2 sm:mr-3 text-base sm:text-lg flex-shrink-0"></i>
                    <span class="font-medium text-sm sm:text-base">${message}</span>
                </div>
            `;
			msgDiv.classList.remove("hidden");

			if (isSuccess) {
				setTimeout(() => {
					msgDiv.classList.add("hidden");
				}, 3000);
			}
		}

		if (isMobile) {
			document.querySelectorAll('input[type="text"], input[type="password"]').forEach(input => {
				input.addEventListener('focus', function() {
					if (isIOS) {
						this.style.fontSize = '16px';
					}
				});
			});

			document.querySelectorAll('button, input').forEach(element => {
				element.addEventListener('touchstart', function() {
					this.style.transform = 'scale(0.98)';
				}, {
					passive: true
				});

				element.addEventListener('touchend', function() {
					this.style.transform = '';
				}, {
					passive: true
				});
			});
		}

		document.querySelectorAll('input').forEach(input => {
			input.addEventListener('focus', function() {
				this.classList.add('animate-pulse');
			});

			input.addEventListener('blur', function() {
				this.classList.remove('animate-pulse');
			});
		});

		document.addEventListener('keydown', function(e) {
			if (e.key === 'Enter' && e.target.tagName !== 'BUTTON') {
				e.preventDefault();
				const form = document.getElementById('loginForm');
				if (form && !document.getElementById("loginBtn").disabled) {
					form.dispatchEvent(new Event('submit'));
				}
			}
		});

		document.getElementById('loginForm').addEventListener('submit', function() {
			if (document.activeElement) {
				document.activeElement.blur();
			}
		});
	</script>
</body>

</html>