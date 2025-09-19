<!DOCTYPE html>
<html lang="es">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Kalli Jaguar Inventory - Login</title>
	<script src="https://cdn.tailwindcss.com"></script>
	<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
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

		.inventory-gradient {
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
		}

		.input-focus:focus {
			transform: scale(1.02);
			transition: all 0.3s ease;
		}

		/* Mobile optimizations */
		@media (max-width: 640px) {
			body {
				-webkit-text-size-adjust: 100%;
				-webkit-tap-highlight-color: transparent;
			}

			input[type="text"],
			input[type="password"] {
				-webkit-appearance: none;
				-moz-appearance: none;
				appearance: none;
				font-size: 16px !important;
			}

			button {
				min-height: 44px;
				-webkit-tap-highlight-color: rgba(0, 0, 0, 0);
			}
		}

		input:focus {
			-webkit-tap-highlight-color: rgba(0, 0, 0, 0);
			outline: none;
		}

		button {
			-webkit-tap-highlight-color: rgba(0, 0, 0, 0);
			-webkit-appearance: none;
			-moz-appearance: none;
			appearance: none;
			border: none;
		}

		@media (hover: none) and (pointer: coarse) {

			button,
			input {
				min-height: 44px;
				min-width: 44px;
			}

			.input-focus:focus {
				transform: none !important;
			}

			button:hover {
				transform: none !important;
			}
		}

		@supports (-webkit-touch-callout: none) {

			input[type="text"],
			input[type="password"] {
				font-size: 16px;
				-webkit-appearance: none;
				-moz-appearance: none;
				appearance: none;
				border-radius: 0;
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
		$(document).ready(function() {
			$("#togglePassword").click(function() {
				const passwordInput = $("#login_clave")[0];
				const eyeIcon = $("#eyeIcon");
				
				if (passwordInput.type === 'password') {
					passwordInput.type = 'text';
					eyeIcon.removeClass('fa-eye').addClass('fa-eye-slash');
				} else {
					passwordInput.type = 'password';
					eyeIcon.removeClass('fa-eye-slash').addClass('fa-eye');
				}
			});

			$("#loginForm").submit(function(e) {
				e.preventDefault();

				const usuario = $("#login_usuario").val().trim();
				const clave = $("#login_clave").val().trim();

				$("#loginMessage").addClass("hidden").html("");

				if (!usuario || !clave) {
					showMessage("Por favor, completa todos los campos.", "error");
					return;
				}

				const $loginBtn = $("#loginBtn");
				const $loginBtnText = $("#loginBtnText");
				
				$loginBtn.prop('disabled', true).addClass("opacity-75 cursor-not-allowed");
				$loginBtnText.html('<i class="fas fa-spinner fa-spin mr-2"></i>Iniciando sesión...');

				$.ajax({
					url: 'api/loginHandler.php',
					type: 'POST',
					data: {
						login_usuario: usuario,
						login_clave: clave
					},
					dataType: 'json',
					timeout: 15000,
					cache: false,
					success: function(response) {
						console.log('Login response:', response);
						if (response.success) {
							showMessage("¡Bienvenido " + (response.user || '') + "! Redirigiendo...", "success");
							
							setTimeout(function() {
								const redirectUrl = response.redirect || "index.php?page=home";
								console.log('Redirecting to:', redirectUrl);
								
								window.location.replace(redirectUrl);
							}, 2000);
						} else {
							showMessage(response.message || "Credenciales incorrectas.", "error");
							resetButton();
						}
					},
					error: function(xhr, status, error) {
						console.error('Ajax Error:', status, error);
						console.error('Response status:', xhr.status);
						console.error('Response text:', xhr.responseText);
						
						let errorMsg = "Error de conexión.";
						
						if (status === 'timeout') {
							errorMsg = "Tiempo de espera agotado. Inténtalo de nuevo.";
						} else if (xhr.status === 404) {
							errorMsg = "Servicio no encontrado. Verifica la URL.";
						} else if (xhr.status === 500) {
							errorMsg = "Error interno del servidor.";
						} else if (status === 'parsererror') {
							errorMsg = "Error procesando respuesta del servidor.";
						} else if (xhr.status === 0) {
							errorMsg = "No se puede conectar al servidor. Verifica tu conexión.";
						}
						
						showMessage(errorMsg, "error");
						resetButton();
					}
				});
			});

			function showMessage(message, type) {
				const isError = type === "error";
				const $msgDiv = $("#loginMessage");

				$msgDiv.html(`
					<div class="flex items-center p-3 sm:p-4 rounded-xl sm:rounded-2xl ${isError ? 'bg-red-500/20 border border-red-500/50 text-red-200' : 'bg-green-500/20 border border-green-500/50 text-green-200'} backdrop-blur-sm">
						<i class="fas ${isError ? 'fa-exclamation-triangle' : 'fa-check-circle'} mr-2 sm:mr-3 text-base sm:text-lg flex-shrink-0"></i>
						<span class="font-medium text-sm sm:text-base">${message}</span>
					</div>
				`).removeClass("hidden");

				if (!isError) {
					setTimeout(function() {
						$msgDiv.addClass("hidden");
					}, 3000);
				}
			}

			function resetButton() {
				$("#loginBtn")
					.prop('disabled', false)
					.removeClass("opacity-75 cursor-not-allowed");
				$("#loginBtnText").html('Iniciar Sesión');
			}
		});
	</script>
</body>

</html>