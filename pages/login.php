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

		@media (max-width: 640px) {
			.input-focus:focus {
				transform: scale(1.01);
			}

			body {
				-webkit-text-size-adjust: 100%;
				-webkit-tap-highlight-color: transparent;
				font-size: 16px;
			}

			input[type="text"],
			input[type="password"] {
				-webkit-appearance: none;
				-moz-appearance: none;
				appearance: none;
				font-size: 16px;
			}

			button {
				-webkit-tap-highlight-color: transparent;
			}
		}

		@media (hover: none) and (pointer: coarse) {
			button,
			input {
				min-height: 44px;
			}
			
			.input-focus:focus {
				transform: none;
			}
		}

		@media (min-width: 1024px) {
			.glass-effect {
				backdrop-filter: blur(25px);
			}
		}
	</style>
</head>

<body class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-slate-800 font-montserrat">
	<div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width=" 60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg" %3E%3Cg fill="none" fill-rule="evenodd" %3E%3Cg fill="%23ffffff" fill-opacity="0.03" %3E%3Ccircle cx="30" cy="30" r="1" /%3E%3C/g%3E%3C/g%3E%3C/svg%3E')] opacity-20"></div>

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
		// Detectar tipo de dispositivo y navegador con más detalle
		const userAgent = navigator.userAgent;
		const isAndroid = /Android/i.test(userAgent);
		const isIOS = /iPad|iPhone|iPod/.test(userAgent);
		const isWindows = /Windows/i.test(userAgent);
		const isMac = /Mac/i.test(userAgent);
		const isChrome = /Chrome/i.test(userAgent);
		const isEdge = /Edge|Edg/i.test(userAgent);
		const isSafari = /Safari/i.test(userAgent) && !/Chrome/i.test(userAgent);
		const isFirefox = /Firefox/i.test(userAgent);
		const isMobile = /Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(userAgent);

		// Debug detallado inicial
		console.log('=== ANÁLISIS COMPLETO DEL NAVEGADOR ===');
		console.log('User Agent completo:', userAgent);
		console.log('Detección de plataforma:', {
			isAndroid,
			isIOS, 
			isWindows,
			isMac,
			isMobile
		});
		console.log('Detección de navegador:', {
			isChrome,
			isEdge,
			isSafari,
			isFirefox
		});
		console.log('Capacidades del navegador:', {
			cookiesEnabled: navigator.cookieEnabled,
			localStorage: typeof(Storage) !== "undefined",
			sessionStorage: typeof(Storage) !== "undefined",
			fetch: typeof fetch !== 'undefined',
			promises: typeof Promise !== 'undefined'
		});
		console.log('Información de pantalla:', {
			screen: `${screen.width}x${screen.height}`,
			viewport: `${window.innerWidth}x${window.innerHeight}`,
			devicePixelRatio: window.devicePixelRatio
		});
		console.log('======================================');

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

		// Función mejorada específica para navegadores problemáticos
		async function makeLoginRequest(formData, retryCount = 0) {
			const maxRetries = isAndroid || isWindows ? 5 : 3; // Más reintentos para plataformas problemáticas
			const timeout = isAndroid ? 15000 : (isWindows ? 12000 : 8000); // Timeouts más largos
			
			console.log(`Intento ${retryCount + 1}/${maxRetries} - Timeout: ${timeout}ms`);
			
			const controller = new AbortController();
			const timeoutId = setTimeout(() => {
				console.log('Timeout alcanzado, abortando request');
				controller.abort();
			}, timeout);

			try {
				// Configuración específica por navegador
				const fetchConfig = {
					method: "POST",
					body: formData,
					signal: controller.signal,
					headers: {
						'X-Requested-With': 'XMLHttpRequest',
						'Accept': 'application/json, text/plain, */*',
					}
				};

				// Configuraciones específicas para navegadores problemáticos
				if (isAndroid || isWindows) {
					fetchConfig.credentials = 'same-origin';
					fetchConfig.cache = 'no-store';
					fetchConfig.mode = 'same-origin';
					fetchConfig.headers['Cache-Control'] = 'no-cache, no-store, must-revalidate';
					fetchConfig.headers['Pragma'] = 'no-cache';
					fetchConfig.headers['Expires'] = '0';
				}

				console.log('Configuración de fetch:', fetchConfig);
				console.log('Enviando request a:', "./api/loginHandler.php");

				const response = await fetch("./api/loginHandler.php", fetchConfig);

				clearTimeout(timeoutId);
				console.log('Respuesta recibida:', {
					ok: response.ok,
					status: response.status,
					statusText: response.statusText,
					headers: Object.fromEntries(response.headers.entries())
				});

				if (!response.ok) {
					throw new Error(`HTTP ${response.status}: ${response.statusText}`);
				}

				// Verificar content-type con más flexibilidad
				const contentType = response.headers.get("content-type");
				console.log('Content-Type recibido:', contentType);
				
				const responseText = await response.text();
				console.log('Respuesta como texto:', responseText.substring(0, 200) + (responseText.length > 200 ? '...' : ''));

				// Intentar parsear JSON con manejo de errores
				let data;
				try {
					data = JSON.parse(responseText);
					console.log('JSON parseado correctamente:', data);
				} catch (parseError) {
					console.error('Error parseando JSON:', parseError);
					console.error('Respuesta completa:', responseText);
					throw new Error("Respuesta del servidor no es JSON válido");
				}

				return data;

			} catch (error) {
				clearTimeout(timeoutId);
				console.error(`Error en intento ${retryCount + 1}:`, error);
				console.error('Tipo de error:', error.name);
				console.error('Mensaje:', error.message);

				// Condiciones de reintento específicas por plataforma
				const shouldRetry = retryCount < maxRetries && (
					error.name === 'AbortError' ||
					error.message.includes('Failed to fetch') ||
					error.message.includes('Network request failed') ||
					error.message.includes('NetworkError') ||
					error.message.includes('TypeError') ||
					(isAndroid && error.message.includes('HTTP')) ||
					(isWindows && error.message.includes('JSON'))
				);

				if (shouldRetry) {
					const delay = Math.min(1000 * Math.pow(2, retryCount), 5000); // Backoff exponencial, max 5s
					console.log(`Reintentando en ${delay}ms... (${retryCount + 1}/${maxRetries})`);
					await new Promise(resolve => setTimeout(resolve, delay));
					return makeLoginRequest(formData, retryCount + 1);
				}

				throw error;
			}
		}

		document.getElementById("loginForm").addEventListener("submit", async function(e) {
			e.preventDefault();

			const usuario = document.getElementById("login_usuario").value.trim();
			const clave = document.getElementById("login_clave").value.trim();

			console.log('=== INICIANDO PROCESO DE LOGIN ===');
			console.log('Usuario:', usuario);
			console.log('Contraseña length:', clave.length);

			if (!usuario || !clave) {
				showMessage("Por favor, completa todos los campos.", "error");
				return;
			}

			// Verificar soporte de cookies antes de continuar
			if (!checkCookieSupport()) {
				setLoadingState(false);
				return;
			}

			setLoadingState(true);

			try {
				const formData = new FormData();
				formData.append("login_usuario", usuario);
				formData.append("login_clave", clave);

				// Log específico para debugging
				console.log('FormData creado, enviando request...');
				
				const data = await makeLoginRequest(formData);
				
				console.log('=== RESPUESTA DE LOGIN RECIBIDA ===');
				console.log('Datos completos:', data);

				if (data && data.success) {
					console.log('Login exitoso, información de debug:', data.debug);
					showMessage("¡Bienvenido! Redirigiendo...", "success");
					
					setTimeout(() => {
						redirectToHome();
					}, 1500);
				} else {
					const errorMessage = data?.message || "Credenciales incorrectas.";
					console.error('Login falló:', data);
					showMessage(errorMessage, "error");
					setLoadingState(false);
				}

			} catch (error) {
				console.error('=== ERROR FINAL EN LOGIN ===');
				console.error('Error object:', error);
				console.error('Stack trace:', error.stack);
				
				let errorMessage = "Error de conexión. Inténtalo de nuevo.";
				if (error.name === 'AbortError') {
					errorMessage = "Tiempo de espera agotado. Verifica tu conexión a internet.";
				} else if (error.message.includes('HTTP')) {
					errorMessage = "Error del servidor. Por favor inténtalo más tarde.";
				} else if (error.message.includes('JSON') || error.message.includes('válido')) {
					errorMessage = "Error en la respuesta del servidor. Contacta al administrador.";
				} else if (isAndroid) {
					errorMessage = "Error específico de Android. Intenta con otro navegador.";
				} else if (isWindows) {
					errorMessage = "Error de compatibilidad. Intenta actualizar tu navegador.";
				}

				showMessage(errorMessage, "error");
				setLoadingState(false);
			}
		});

		function redirectToHome() {
			const homeUrl = "index.php?page=home";
			
			console.log('=== INICIANDO REDIRECCIÓN ===');
			console.log('URL objetivo:', homeUrl);
			console.log('URL actual:', window.location.href);
			console.log('Navegador detectado:', {
				isChrome, isEdge, isSafari, isFirefox,
				isAndroid, isWindows, isMac, isIOS
			});

			try {
				// Estrategias específicas por navegador
				if (isAndroid || isWindows) {
					console.log('Usando estrategia para Android/Windows');
					
					// Método 1: location.href directo
					console.log('Método 1: location.href');
					window.location.href = homeUrl;
					
					// Método 2: Fallback con replace
					setTimeout(() => {
						if (window.location.href.includes('login.php')) {
							console.log('Método 2: location.replace');
							window.location.replace(homeUrl);
						}
					}, 1000);
					
					// Método 3: Fallback con assign
					setTimeout(() => {
						if (window.location.href.includes('login.php')) {
							console.log('Método 3: location.assign');
							window.location.assign(homeUrl);
						}
					}, 2000);
					
					// Método 4: Fallback extremo con form submit
					setTimeout(() => {
						if (window.location.href.includes('login.php')) {
							console.log('Método 4: form submit fallback');
							const form = document.createElement('form');
							form.method = 'GET';
							form.action = 'index.php';
							const pageInput = document.createElement('input');
							pageInput.type = 'hidden';
							pageInput.name = 'page';
							pageInput.value = 'home';
							form.appendChild(pageInput);
							document.body.appendChild(form);
							form.submit();
						}
					}, 3000);
					
				} else {
					// Método estándar para Safari/Mac/iOS
					console.log('Usando método estándar para Safari/Mac/iOS');
					window.location.href = homeUrl;
				}
				
			} catch (error) {
				console.error('Error en redirección:', error);
				// Último recurso: recarga forzada
				console.log('Último recurso: recarga con nueva URL');
				document.location = homeUrl;
			}
		}

		// Función para verificar cookies antes del login
		function checkCookieSupport() {
			console.log('=== VERIFICANDO SOPORTE DE COOKIES ===');
			
			// Verificación básica
			if (!navigator.cookieEnabled) {
				console.error('Las cookies están deshabilitadas');
				showMessage("Las cookies están deshabilitadas. Habílitalas para continuar.", "error");
				return false;
			}
			
			// Prueba de cookie de test
			document.cookie = "test_cookie=1; path=/; SameSite=Lax";
			const cookieExists = document.cookie.indexOf("test_cookie=1") !== -1;
			
			if (cookieExists) {
				console.log('Soporte de cookies verificado correctamente');
				// Limpiar cookie de test
				document.cookie = "test_cookie=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
				return true;
			} else {
				console.error('Falla en la escritura de cookies');
				if (isAndroid || isWindows) {
					showMessage("Problema con cookies en este navegador. Intenta en modo incógnito.", "error");
				} else {
					showMessage("Error con cookies. Verifica la configuración del navegador.", "error");
				}
				return false;
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

			msgDiv.innerHTML = `
                <div class="flex items-center p-3 sm:p-4 rounded-xl sm:rounded-2xl ${
					isError 
						? 'bg-red-500/20 border border-red-500/50 text-red-200' 
						: 'bg-green-500/20 border border-green-500/50 text-green-200'
				} backdrop-blur-sm">
                    <i class="fas ${
						isError ? 'fa-exclamation-triangle' : 'fa-check-circle'
					} mr-2 sm:mr-3 text-base sm:text-lg flex-shrink-0"></i>
                    <span class="font-medium text-sm sm:text-base">${message}</span>
                </div>
            `;
			msgDiv.classList.remove("hidden");

			if (!isError) {
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
				}, { passive: true });

				element.addEventListener('touchend', function() {
					this.style.transform = '';
				}, { passive: true });
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

		// Ejecutar verificación de cookies al cargar la página
		document.addEventListener('DOMContentLoaded', function() {
			console.log('DOM cargado, verificando cookies...');
			checkCookieSupport();
		});

		// Si DOMContentLoaded ya pasó, ejecutar inmediatamente
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', function() {
				checkCookieSupport();
			});
		} else {
			checkCookieSupport();
		}

		console.log('Login script inicializado correctamente para navegador:', {
			userAgent: userAgent.substring(0, 100) + '...',
			isProblematic: isAndroid || isWindows,
			platform: isAndroid ? 'Android' : isWindows ? 'Windows' : isMac ? 'Mac' : isIOS ? 'iOS' : 'Unknown'
		});
	</script>
</body>

</html>