<?php
// Navbar para Logística - versión TailwindCSS
if (!isset($_SESSION['id']) || !isset($_SESSION['usuario'])) {
    header('Location: index.php?page=login');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'sidebar-dark': '#1a1a1a',
                        'sidebar-darker': '#0d0d0d',
                        'accent-yellow': '#fbbf24',
                        'accent-yellow-dark': '#f59e0b'
                    },
                    fontFamily: {
                        'montserrat': ['Montserrat', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <style>
        /* Estilos personalizados para el navbar */
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(251, 191, 36, 0.5);
            border-radius: 2px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(251, 191, 36, 0.8);
        }
        
        /* Estilos para navegación con puntos activos */
        .nav-item {
            position: relative;
            font-size: 0.875rem; /* Texto más pequeño (14px) */
        }
        
        .nav-item::before {
            content: '';
            position: absolute;
            left: -8px;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 4px;
            background: #fbbf24;
            border-radius: 50%;
            opacity: 0;
            transition: opacity 0.2s ease;
        }
        
        .nav-item.active::before {
            opacity: 1;
        }
        
        .nav-item-hover:hover::before {
            opacity: 0.6;
        }
        
        /* Hacer texto del menú más pequeño */
        .nav-text {
            font-size: 0.875rem; /* 14px */
            font-weight: 500;
        }
        
        .nav-text-small {
            font-size: 0.8125rem; /* 13px */
            font-weight: 400;
        }
        
        /* Estilo para elementos de dropdown */
        .dropdown-item {
            font-size: 0.8125rem; /* 13px */
        }
    </style>
</head>
<body class="bg-gray-50 font-montserrat">

<div id="wrapper" class="flex min-h-screen">
    <!-- Sidebar para Logística -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-gradient-to-b from-sidebar-dark via-sidebar-darker to-black shadow-2xl transform -translate-x-full transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0 overflow-y-auto">
        
        <!-- Logo/Brand -->
        <div class="flex items-center justify-center px-6 py-6 border-b border-white border-opacity-10">
            <a href="index.php?page=home" class="flex items-center space-x-3 text-white hover:text-accent-yellow transition-colors group">
                <img src="./img/Kalli-Amarillo.png" alt="Kalli Jaguar" class="w-12 h-12 object-contain group-hover:scale-110 transition-transform">
                <span class="text-xl font-bold">Kalli Jaguar</span>
            </a>
        </div>

        <!-- Navigation Menu para Logística -->
        <nav class="mt-6 px-3">
            <!-- Home -->
            <a href="index.php?page=home" class="flex items-center px-4 py-3 text-white text-opacity-80 hover:text-white hover:bg-white hover:bg-opacity-10 rounded-lg mb-2 transition-all group">
                <i class="fas fa-home mr-3 text-lg group-hover:text-accent-yellow"></i>
                <span class="font-medium">Ir al Inicio</span>
            </a>

            <!-- Gestión de Pedidos -->
            <div class="mt-8 mb-4">
                <h3 class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Gestión de Pedidos</h3>
            </div>

            <div class="space-y-2">
                <a href="index.php?page=showAllRequest" class="flex items-center px-4 py-3 text-white text-opacity-80 hover:text-white hover:bg-white hover:bg-opacity-10 rounded-lg transition-all group">
                    <i class="fas fa-clipboard-list mr-3 text-lg group-hover:text-accent-yellow"></i>
                    <span class="font-medium">Todas las Solicitudes</span>
                </a>
                
                <a href="index.php?page=changeToTransit" class="flex items-center px-4 py-3 text-white text-opacity-80 hover:text-white hover:bg-white hover:bg-opacity-10 rounded-lg transition-all group">
                    <i class="fas fa-truck mr-3 text-lg group-hover:text-accent-yellow"></i>
                    <span class="font-medium">Marcar En Tránsito</span>
                </a>
                
                <a href="index.php?page=changeToDelivered" class="flex items-center px-4 py-3 text-white text-opacity-80 hover:text-white hover:bg-white hover:bg-opacity-10 rounded-lg transition-all group">
                    <i class="fas fa-check-circle mr-3 text-lg group-hover:text-accent-yellow"></i>
                    <span class="font-medium">Marcar Entregado</span>
                </a>
            </div>

            <!-- Inventario -->
            <div class="mt-8 mb-4">
                <h3 class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Inventario</h3>
            </div>

            <div class="space-y-2">
                <a href="index.php?page=showProduct" class="flex items-center px-4 py-3 text-white text-opacity-80 hover:text-white hover:bg-white hover:bg-opacity-10 rounded-lg transition-all group">
                    <i class="fas fa-box mr-3 text-lg group-hover:text-accent-yellow"></i>
                    <span class="font-medium">Lista de Productos</span>
                </a>
                
                <a href="index.php?page=scanProducts" class="flex items-center px-4 py-3 text-white text-opacity-80 hover:text-white hover:bg-white hover:bg-opacity-10 rounded-lg transition-all group">
                    <i class="fas fa-qrcode mr-3 text-lg group-hover:text-accent-yellow"></i>
                    <span class="font-medium">Actualizar Stock</span>
                </a>
                
                <a href="index.php?page=requestProducts" class="flex items-center px-4 py-3 text-white text-opacity-80 hover:text-white hover:bg-white hover:bg-opacity-10 rounded-lg transition-all group">
                    <i class="fas fa-plus-circle mr-3 text-lg group-hover:text-accent-yellow"></i>
                    <span class="font-medium">Nueva Solicitud</span>
                </a>
                
                <a href="index.php?page=showRequest" class="flex items-center px-4 py-3 text-white text-opacity-80 hover:text-white hover:bg-white hover:bg-opacity-10 rounded-lg transition-all group">
                    <i class="fas fa-list-alt mr-3 text-lg group-hover:text-accent-yellow"></i>
                    <span class="font-medium">Mis Pedidos</span>
                </a>
            </div>
        </nav>
    </aside>

    <!-- Overlay para móvil -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden lg:hidden"></div>

    <!-- Content Wrapper -->
    <div class="lg:pl-64 w-full">
        <!-- Topbar -->
        <nav class="bg-white shadow-sm border-b border-gray-200 px-4 lg:px-6 py-4 flex items-center justify-between sticky top-0 z-30 w-full">
            <!-- Left side -->
            <div class="flex items-center space-x-4">
                <button id="sidebarToggle" class="lg:hidden p-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-600 hover:text-gray-800 transition-all">
                    <i class="fas fa-bars"></i>
                </button>
                
                <div class="hidden md:block">
                    <h1 class="text-xl font-semibold text-gray-800">Panel Logística</h1>
                </div>
            </div>

            <!-- Right side -->
            <div class="flex items-center space-x-4">
                <!-- Notifications -->
                <button class="relative p-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-600 hover:text-gray-800 transition-all">
                    <i class="fas fa-bell"></i>
                    <span class="absolute -top-1 -right-1 bg-red-500 text-xs text-white font-bold rounded-full h-4 w-4 flex items-center justify-center text-xs">3</span>
                </button>

                <!-- User Dropdown -->
                <div class="relative">
                    <button id="userDropdown" class="flex items-center space-x-3 p-2 rounded-lg bg-gray-100 hover:bg-gray-200 transition-all group">
                        <span class="hidden md:block text-sm font-medium text-gray-700"><?php echo $_SESSION['nombre']; ?></span>
                        <img class="w-8 h-8 rounded-full object-cover" src="img/undraw_profile.svg" alt="Profile">
                    </button>
                    
                    <div id="userMenu" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 hidden z-50">
                        <a href="index.php?page=updateUser&idUserUpdate=<?php echo $_SESSION['id']; ?>" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                            <i class="fas fa-user-circle mr-3 text-gray-400"></i>
                            Mi Cuenta
                        </a>
                        <div class="border-t border-gray-100 my-1"></div>
                        <button onclick="showLogoutModal()" class="w-full flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                            <i class="fas fa-sign-out-alt mr-3 text-gray-400"></i>
                            Cerrar Sesión
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div id="content" class="w-full p-4 lg:p-6 bg-gray-50 min-h-screen">
            <style>
                /* Asegurar texto negro en todo el contenido */
                #content, #content * {
                    color: #1f2937 !important;
                }
                #content h1, #content h2, #content h3, #content h4, #content h5, #content h6 {
                    color: #111827 !important;
                    font-weight: 600;
                }
                #content p, #content span, #content div, #content td, #content th, #content label {
                    color: #374151 !important;
                }
                #content a {
                    color: #1f2937 !important;
                }
                #content .text-muted, #content .text-secondary {
                    color: #6b7280 !important;
                }
                /* Preservar botones y elementos especiales */
                #content .btn, #content .text-white, #content .bg-blue-500, #content .bg-green-500, 
                #content .bg-red-500, #content .bg-yellow-500, #content .bg-indigo-500,
                #content .badge, #content .alert {
                    color: white !important;
                }
                /* Asegurar que los inputs tengan texto negro */
                #content input, #content textarea, #content select {
                    color: #1f2937 !important;
                    background-color: white !important;
                }
                /* Asegurar que el contenido use todo el ancho disponible */
                #content .container, #content .container-fluid {
                    max-width: none !important;
                    width: 100% !important;
                }
                #content .row {
                    margin-left: 0 !important;
                    margin-right: 0 !important;
                }
            </style>
            <!-- Contenido de las páginas se cargará aquí -->
        </div>
