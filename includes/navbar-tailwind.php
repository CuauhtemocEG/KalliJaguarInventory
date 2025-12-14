<?php
if (!isset($_SESSION['id']) || !isset($_SESSION['usuario'])) {
    exit();
}

require_once __DIR__ . '/../config.php';
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

        .nav-item {
            position: relative;
            font-size: 0.875rem;
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

        .nav-item-hover,
        .nav-item-hover:hover,
        .nav-item-hover:focus,
        .nav-item-hover:active,
        .nav-item-hover:visited,
        .nav-item-hover.active,
        .nav-item-hover.selected,
        button.nav-item-hover,
        button.nav-item-hover:hover,
        button.nav-item-hover:focus,
        button.nav-item-hover:active,
        button.nav-item-hover.active,
        .dropdown-item,
        .dropdown-item:hover,
        .dropdown-item:focus,
        .dropdown-item:active,
        a.nav-item-hover,
        a.nav-item-hover:hover,
        a.nav-item-hover:focus,
        a.nav-item-hover:active {
            background: transparent !important;
            background-color: transparent !important;
            box-shadow: none !important;
            outline: none !important;
            border: none !important;
        }

        .nav-item-hover:hover {
            background: rgba(251, 191, 36, 0.08) !important;
            background-color: rgba(251, 191, 36, 0.08) !important;
            border-radius: 8px;
        }

        .dropdown-item:hover {
            background: rgba(251, 191, 36, 0.06) !important;
            background-color: rgba(251, 191, 36, 0.06) !important;
            border-radius: 6px;
        }

        .nav-item.active,
        .nav-item-hover.active {
            background: rgba(251, 191, 36, 0.12) !important;
            background-color: rgba(251, 191, 36, 0.12) !important;
            border-radius: 8px;
        }

        *:focus,
        *:focus-visible,
        *:focus-within {
            outline: none !important;
            box-shadow: none !important;
        }

        .nav-text {
            font-size: 0.875rem;
            font-weight: 500;
        }

        .nav-text-small {
            font-size: 0.8125rem;
            font-weight: 400;
        }

        .dropdown-item {
            font-size: 0.8125rem;
        }

        .hover\:bg-white,
        .hover\:bg-gray-50,
        .focus\:bg-white,
        .focus\:bg-gray-50,
        .active\:bg-white,
        .active\:bg-gray-50 {
            background: transparent !important;
            background-color: transparent !important;
        }
    </style>
</head>

<body class="bg-gray-50 font-montserrat">

    <div id="wrapper" class="min-h-screen w-full">
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-gradient-to-b from-sidebar-dark via-sidebar-darker to-black shadow-2xl transform -translate-x-full transition-transform duration-300 ease-in-out lg:translate-x-0 custom-scrollbar overflow-y-auto">
            <button id="closeSidebar" class="lg:hidden absolute top-4 right-4 text-white hover:text-accent-yellow transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>

            <div class="flex items-center justify-center px-6 py-6 border-b border-white border-opacity-10">
                <a href="index.php?page=home" class="flex items-center space-x-3 text-white hover:text-accent-yellow transition-colors group">
                    <img src="./img/Kalli-Amarillo.png" alt="Kalli Jaguar" class="w-12 h-12 object-contain group-hover:scale-110 transition-transform">
                    <span class="text-xl font-bold">Kalli Jaguar</span>
                </a>
            </div>

            <nav class="mt-6 px-3">
                <a href="index.php?page=home" class="nav-item-hover flex items-center px-4 py-3 text-white text-opacity-80 hover:text-white rounded-lg mb-2 relative group">
                    <i class="fas fa-home mr-3 text-lg group-hover:text-accent-yellow transition-colors"></i>
                    <span class="font-medium">Ir al Inicio</span>
                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-accent-yellow rounded-r-full transform scale-y-0 group-hover:scale-y-100 transition-transform"></div>
                </a>

                <?php if ($_SESSION['id'] == '1') { ?>

                    <a href="index.php?page=dashboardAvanzado" class="nav-item-hover flex items-center px-4 py-3 text-white text-opacity-80 hover:text-white rounded-lg mb-2 relative group">
                        <i class="fas fa-home mr-3 text-lg group-hover:text-accent-yellow transition-colors"></i>
                        <span class="font-medium">Ir al Inicio</span>
                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-accent-yellow rounded-r-full transform scale-y-0 group-hover:scale-y-100 transition-transform"></div>
                    </a>

                    <div class="mt-8 mb-4">
                        <h3 class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Administración</h3>
                    </div>

                    <div class="mb-2">
                        <button onclick="toggleDropdown('usuarios')" class="nav-item nav-item-hover w-full flex items-center justify-between px-4 py-2.5 text-white text-opacity-80 hover:text-white rounded-lg group">
                            <div class="flex items-center">
                                <i class="fas fa-user mr-3 text-base group-hover:text-accent-yellow transition-colors"></i>
                                <span class="nav-text">Usuarios</span>
                            </div>
                            <i id="usuarios-icon" class="fas fa-chevron-right transition-transform group-hover:text-accent-yellow text-sm"></i>
                        </button>
                        <div id="usuarios-dropdown" class="hidden ml-4 mt-2 space-y-1">
                            <a href="index.php?page=userManagement" class="dropdown-item flex items-center px-4 py-2 text-white text-opacity-70 hover:text-accent-yellow transition-all">
                                <i class="fas fa-users-cog mr-2 text-xs"></i>
                                <span class="nav-text-small">Gestión de Usuarios</span>
                            </a>
                            <a href="index.php?page=userActivity" class="dropdown-item flex items-center px-4 py-2 text-white text-opacity-70 hover:text-accent-yellow transition-all">
                                <i class="fas fa-chart-line mr-2 text-xs"></i>
                                <span class="nav-text-small">Actividad de Usuarios</span>
                            </a>
                        </div>
                    </div>

                    <a href="index.php?page=sucursalManagement" class="nav-item nav-item-hover flex items-center px-4 py-2.5 text-white text-opacity-80 hover:text-white rounded-lg group relative">
                        <i class="fas fa-home mr-3 text-base group-hover:text-accent-yellow transition-colors"></i>
                        <span class="nav-text">Sucursales</span>
                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-accent-yellow rounded-r-full transform scale-y-0 group-hover:scale-y-100 transition-transform"></div>
                    </a>

                    <a href="index.php?page=configMantenimiento" class="nav-item nav-item-hover flex items-center px-4 py-2.5 text-white text-opacity-80 hover:text-white rounded-lg group relative">
                        <i class="fas fa-lock mr-3 text-base group-hover:text-accent-yellow transition-colors"></i>
                        <span class="nav-text">Activar Cortinas</span>
                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-accent-yellow rounded-r-full transform scale-y-0 group-hover:scale-y-100 transition-transform"></div>
                    </a>

                <?php } ?>

                <?php if ($_SESSION['id'] == '1' || $_SESSION['id'] == '16' || $_SESSION['id'] == '10') { ?>
                    <div class="mb-2">
                        <button onclick="toggleDropdown('ordenes')" class="nav-item nav-item-hover w-full flex items-center justify-between px-4 py-2.5 text-white text-opacity-80 hover:text-white rounded-lg group">
                            <div class="flex items-center">
                                <i class="fas fa-clipboard-list mr-3 text-base group-hover:text-accent-yellow transition-colors"></i>
                                <span class="nav-text">Órdenes</span>
                            </div>
                            <i id="ordenes-icon" class="fas fa-chevron-right transition-transform group-hover:text-accent-yellow text-sm"></i>
                        </button>
                        <div id="ordenes-dropdown" class="hidden ml-4 mt-2 space-y-1">
                            <div class="px-4 py-1">
                                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Gestión Órdenes:</span>
                            </div>
                            <a href="index.php?page=showAllRequest" class="dropdown-item flex items-center px-4 py-2 text-white text-opacity-70 hover:text-accent-yellow transition-all">
                                <i class="fas fa-list-alt mr-2 text-xs"></i>
                                <span class="nav-text-small">Solicitudes Generales</span>
                            </a>
                            <a href="index.php?page=editarComanda" class="dropdown-item flex items-center px-4 py-2 text-white text-opacity-70 hover:text-accent-yellow transition-all">
                                <i class="fas fa-redo mr-2 text-xs"></i>
                                <span class="nav-text-small">Regenerar Comanda</span>
                            </a>
                            <a href="index.php?page=reportOrders" class="dropdown-item flex items-center px-4 py-2 text-white text-opacity-70 hover:text-accent-yellow transition-all">
                                <i class="fas fa-chart-bar mr-2 text-xs"></i>
                                <span class="nav-text-small">Generar Reporte</span>
                            </a>
                            <a href="index.php?page=logsStock" class="dropdown-item flex items-center px-4 py-2 text-white text-opacity-70 hover:text-accent-yellow transition-all">
                                <i class="fas fa-history mr-2 text-xs"></i>
                                <span class="nav-text-small">Historial de Cambios</span>
                            </a>
                            <a href="index.php?page=pickingOrders" class="dropdown-item flex items-center px-4 py-2 text-white text-opacity-70 hover:text-green-400 transition-all">
                                <i class="fas fa-barcode mr-2 text-xs"></i>
                                <span class="nav-text-small">Picking de Órdenes</span>
                            </a>
                        </div>
                    </div>
                <?php } ?>

                <?php if ($_SESSION['id'] == '1' || $_SESSION['id'] == '16' || $_SESSION['id'] == '10') { ?>
                    <div class="mt-8 mb-4">
                        <h3 class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Catálogo de Almacén</h3>
                    </div>

                    <div class="mb-2">
                        <button onclick="toggleDropdown('productos')" class="nav-item nav-item-hover w-full flex items-center justify-between px-4 py-2.5 text-white text-opacity-80 hover:text-white rounded-lg group">
                            <div class="flex items-center">
                                <i class="fas fa-box mr-3 text-base group-hover:text-accent-yellow transition-colors"></i>
                                <span class="nav-text">Producto</span>
                            </div>
                            <i id="productos-icon" class="fas fa-chevron-right transition-transform group-hover:text-accent-yellow text-sm"></i>
                        </button>
                        <div id="productos-dropdown" class="hidden ml-4 mt-2 space-y-1">
                            <div class="px-4 py-1">
                                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Categorías</span>
                            </div>
                            <a href="index.php?page=gestionCategorias" class="dropdown-item flex items-center px-4 py-2 text-white text-opacity-70 hover:text-accent-yellow transition-all">
                                <i class="fas fa-plus mr-2 text-xs"></i>
                                <span class="nav-text-small">Gestión Categoría</span>
                            </a>

                            <a href="index.php?page=gestionTags" class="dropdown-item flex items-center px-4 py-2 text-white text-opacity-70 hover:text-accent-yellow transition-all">
                                <i class="fas fa-plus mr-2 text-xs"></i>
                                <span class="nav-text-small">Gestión Etiquetas</span>
                            </a>

                            <div class="border-t border-white border-opacity-10 my-2"></div>

                            <div class="px-4 py-1">
                                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Producto</span>
                            </div>
                            <a href="index.php?page=gestionProductos" class="dropdown-item flex items-center px-4 py-2 text-white text-opacity-70 hover:text-accent-yellow transition-all">
                                <i class="fas fa-plus mr-2 text-xs"></i>
                                <span class="nav-text-small">Gestión Productos</span>
                            </a>
                            <a href="index.php?page=scanProducts" class="dropdown-item flex items-center px-4 py-2 text-white text-opacity-70 hover:text-accent-yellow transition-all">
                                <i class="fas fa-qrcode mr-2 text-xs"></i>
                                <span class="nav-text-small">Actualizar Stock</span>
                            </a>
                        </div>
                    </div>
                <?php } ?>

                <div class="mt-8 space-y-2">
                    <a href="index.php?page=requestProducts" class="nav-item nav-item-hover flex items-center px-4 py-2.5 text-white text-opacity-80 hover:text-white rounded-lg group relative">
                        <i class="fas fa-truck mr-3 text-base group-hover:text-accent-yellow transition-colors"></i>
                        <span class="nav-text">Solicitar Insumos</span>
                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-accent-yellow rounded-r-full transform scale-y-0 group-hover:scale-y-100 transition-transform"></div>
                    </a>

                    <a href="index.php?page=showRequest" class="nav-item nav-item-hover flex items-center px-4 py-2.5 text-white text-opacity-80 hover:text-white rounded-lg group relative">
                        <i class="fas fa-list-alt mr-3 text-base group-hover:text-accent-yellow transition-colors"></i>
                        <span class="nav-text">Mis Pedidos</span>
                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-accent-yellow rounded-r-full transform scale-y-0 group-hover:scale-y-100 transition-transform"></div>
                    </a>

                    <?php if ($_SESSION['id'] == '13') { ?>

                        <a href="index.php?page=showAllRequest" class="nav-item nav-item-hover flex items-center px-4 py-2.5 text-white text-opacity-80 hover:text-white rounded-lg group relative">
                            <i class="fas fa-list-alt mr-3 text-base group-hover:text-accent-yellow transition-colors"></i>
                            <span class="nav-text">Todos los Pedidos</span>
                            <div class="absolute left-0 top-0 bottom-0 w-1 bg-accent-yellow rounded-r-full transform scale-y-0 group-hover:scale-y-100 transition-transform"></div>
                        </a>

                    <?php } ?>
                </div>

                <div class="mt-auto pt-8 pb-4 px-3">
                    <div class="border-t border-white border-opacity-10 pt-4">
                        <!-- Información de la App -->
                        <div class="px-4 py-3 bg-gradient-to-r from-accent-yellow/5 to-transparent rounded-lg mb-3">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Sistema</span>
                                <i class="fas fa-info-circle text-accent-yellow text-xs"></i>
                            </div>
                            <p class="text-sm font-bold text-white mb-1"><?php echo APP_SHORT_NAME; ?></p>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-400">Versión</span>
                                <span class="text-xs font-mono text-accent-yellow font-bold"><?php echo APP_VERSION; ?></span>
                            </div>
                            <div class="flex items-center justify-between mt-1">
                                <span class="text-xs text-gray-400">Build</span>
                                <span class="text-xs font-mono text-gray-500"><?php echo APP_BUILD; ?></span>
                            </div>
                        </div>

                        <!-- Módulos Instalados -->
                        <div class="px-4 py-2 mb-3">
                            <p class="text-[10px] text-gray-500 uppercase tracking-wider mb-2">Módulos Activos</p>
                            <div class="grid grid-cols-2 gap-2">
                                <div class="flex items-center">
                                    <div class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5"></div>
                                    <span class="text-[10px] text-gray-400">Productos v<?php echo MODULE_PRODUCTS; ?></span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5"></div>
                                    <span class="text-[10px] text-gray-400">Categorías v<?php echo MODULE_CATEGORIES; ?></span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5"></div>
                                    <span class="text-[10px] text-gray-400">Tags v<?php echo MODULE_TAGS; ?></span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5"></div>
                                    <span class="text-[10px] text-gray-400">Órdenes v<?php echo MODULE_ORDERS; ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Copyright -->
                        <div class="px-4 py-2 text-center border-t border-white border-opacity-5">
                            <p class="text-[10px] text-gray-500">
                                <i class="far fa-copyright mr-1"></i>
                                <?php echo APP_YEAR . ' ' . APP_COMPANY; ?>
                            </p>
                            <p class="text-[9px] text-gray-600 mt-1"><?php echo APP_RELEASE_DATE; ?></p>
                        </div>

                        <!-- Botón de Info del Sistema (opcional) -->
                        <div class="px-4 pt-2">
                            <button onclick="showSystemInfo()" class="w-full py-2 px-3 bg-white bg-opacity-5 hover:bg-opacity-10 rounded-lg transition-all group">
                                <div class="flex items-center justify-center text-xs text-gray-400 group-hover:text-accent-yellow">
                                    <i class="fas fa-server mr-2 text-xs"></i>
                                    <span>Info del Sistema</span>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>

            </nav>
        </aside>

        <div id="sidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden lg:hidden"></div>

        <div class="lg:pl-64 w-full">
            <nav class="bg-white shadow-sm border-b border-gray-200 px-4 lg:px-6 py-4 flex items-center justify-between sticky top-0 z-30 w-full">
                <div class="flex items-center space-x-4">
                    <button id="sidebarToggle" class="lg:hidden p-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-600 hover:text-gray-800 transition-all">
                        <i class="fas fa-bars"></i>
                    </button>

                    <div class="hidden md:block">
                        <nav class="flex" aria-label="Breadcrumb">
                            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                                <li class="inline-flex items-center">
                                    <a href="index.php?page=home" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-accent-yellow-dark transition-colors">
                                        <i class="fas fa-home mr-2"></i>
                                        Dashboard
                                    </a>
                                </li>
                                <li>
                                    <div class="flex items-center">
                                        <i class="fas fa-chevron-right text-gray-400 text-xs mx-2"></i>
                                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Principal</span>
                                    </div>
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>

                <div class="flex items-center space-x-4">

                    <!--<div class="hidden md:block relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" placeholder="Buscar..." class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent-yellow focus:border-transparent bg-gray-50 text-sm w-64">
                </div>-->

                    <button id="cartToggle" class="relative p-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-600 hover:text-gray-800 transition-all" title="Ver carrito">
                        <i class="fas fa-shopping-cart"></i>
                        <span id="cartBadge" class="absolute -top-1 -right-1 bg-accent-yellow text-xs text-black font-bold rounded-full h-5 w-5 flex items-center justify-center hidden">0</span>
                    </button>

                    <div class="relative">
                        <button id="userDropdown" class="flex items-center space-x-3 p-2 rounded-lg bg-gray-100 hover:bg-gray-200 transition-all group">
                            <span class="hidden md:block text-sm font-medium text-gray-700 group-hover:text-gray-900"><?php echo $_SESSION['nombre']; ?></span>
                            <img class="w-8 h-8 rounded-full object-cover" src="img/undraw_profile.svg" alt="Profile">
                            <i class="fas fa-chevron-down text-gray-400 text-xs group-hover:text-gray-600"></i>
                        </button>

                        <div id="userMenu" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 hidden z-50">
                            <a href="index.php?page=updateUser&idUserUpdate=<?php echo $_SESSION['id']; ?>" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                <i class="fas fa-user-circle mr-3 text-gray-400"></i>
                                Mi Cuenta
                            </a>
                            <div class="border-t border-gray-100 my-1"></div>
                            <button onclick="showLogoutModal()" class="w-full flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                <i class="fas fa-sign-out-alt mr-3 text-gray-400"></i>
                                Cerrar Sesión
                            </button>
                        </div>
                    </div>
                </div>
            </nav>

            <div id="content" class="w-full p-4 lg:p-6 bg-gray-50 min-h-screen">
                <style>
                    #content,
                    #content * {
                        color: #1f2937 !important;
                    }

                    #content h1,
                    #content h2,
                    #content h3,
                    #content h4,
                    #content h5,
                    #content h6 {
                        color: #111827 !important;
                        font-weight: 600;
                    }

                    #content p,
                    #content span,
                    #content div,
                    #content td,
                    #content th,
                    #content label {
                        color: #374151 !important;
                    }

                    #content a {
                        color: #1f2937 !important;
                    }

                    #content .text-muted,
                    #content .text-secondary {
                        color: #6b7280 !important;
                    }

                    #content .btn,
                    #content .text-white,
                    #content .bg-blue-500,
                    #content .bg-green-500,
                    #content .bg-red-500,
                    #content .bg-yellow-500,
                    #content .bg-indigo-500,
                    #content .badge,
                    #content .alert {
                        color: white !important;
                    }

                    #content input,
                    #content textarea,
                    #content select {
                        color: #1f2937 !important;
                        background-color: white !important;
                    }

                    #content .container,
                    #content .container-fluid {
                        max-width: none !important;
                        width: 100% !important;
                    }

                    #content .row {
                        margin-left: 0 !important;
                        margin-right: 0 !important;
                    }
                </style>

                <script>
                    // Función para mostrar información del sistema
                    function showSystemInfo() {
                        const systemInfo = {
                            name: '<?php echo APP_NAME; ?>',
                            version: '<?php echo APP_VERSION; ?>',
                            build: '<?php echo APP_BUILD; ?>',
                            release: '<?php echo APP_RELEASE_DATE; ?>',
                            company: '<?php echo APP_COMPANY; ?>',
                            modules: {
                                products: '<?php echo MODULE_PRODUCTS; ?>',
                                categories: '<?php echo MODULE_CATEGORIES; ?>',
                                tags: '<?php echo MODULE_TAGS; ?>',
                                orders: '<?php echo MODULE_ORDERS; ?>',
                                users: '<?php echo MODULE_USERS; ?>'
                            }
                        };

                        // Crear modal
                        const modal = document.createElement('div');
                        modal.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4';
                        modal.innerHTML = `
                            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                                <div class="bg-gradient-to-r from-sidebar-dark to-sidebar-darker text-white p-6 rounded-t-lg">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <i class="fas fa-server text-accent-yellow text-3xl mr-4"></i>
                                            <div>
                                                <h2 class="text-2xl font-bold">Información del Sistema</h2>
                                                <p class="text-gray-300 text-sm mt-1">${systemInfo.name}</p>
                                            </div>
                                        </div>
                                        <button onclick="this.closest('.fixed').remove()" class="text-white hover:text-accent-yellow transition-colors">
                                            <i class="fas fa-times text-2xl"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="p-6">
                                    <!-- Información General -->
                                    <div class="mb-6">
                                        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                                            <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                                            Información General
                                        </h3>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div class="bg-gray-50 p-4 rounded-lg">
                                                <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Versión</p>
                                                <p class="text-xl font-bold text-gray-900">${systemInfo.version}</p>
                                            </div>
                                            <div class="bg-gray-50 p-4 rounded-lg">
                                                <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Build</p>
                                                <p class="text-xl font-mono font-bold text-gray-900">${systemInfo.build}</p>
                                            </div>
                                            <div class="bg-gray-50 p-4 rounded-lg col-span-2">
                                                <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Fecha de Lanzamiento</p>
                                                <p class="text-lg font-semibold text-gray-900">${systemInfo.release}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Módulos -->
                                    <div class="mb-6">
                                        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                                            <i class="fas fa-puzzle-piece text-purple-500 mr-2"></i>
                                            Módulos Instalados
                                        </h3>
                                        <div class="space-y-3">
                                            <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                                                <div class="flex items-center">
                                                    <div class="w-2 h-2 bg-green-500 rounded-full mr-3"></div>
                                                    <span class="font-semibold text-gray-800">📦 Productos</span>
                                                </div>
                                                <span class="text-sm font-mono bg-white px-3 py-1 rounded text-gray-700">v${systemInfo.modules.products}</span>
                                            </div>
                                            <div class="flex items-center justify-between p-3 bg-purple-50 rounded-lg">
                                                <div class="flex items-center">
                                                    <div class="w-2 h-2 bg-green-500 rounded-full mr-3"></div>
                                                    <span class="font-semibold text-gray-800">📁 Categorías</span>
                                                </div>
                                                <span class="text-sm font-mono bg-white px-3 py-1 rounded text-gray-700">v${systemInfo.modules.categories}</span>
                                            </div>
                                            <div class="flex items-center justify-between p-3 bg-pink-50 rounded-lg">
                                                <div class="flex items-center">
                                                    <div class="w-2 h-2 bg-green-500 rounded-full mr-3"></div>
                                                    <span class="font-semibold text-gray-800">🏷️ Tags</span>
                                                </div>
                                                <span class="text-sm font-mono bg-white px-3 py-1 rounded text-gray-700">v${systemInfo.modules.tags}</span>
                                            </div>
                                            <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                                                <div class="flex items-center">
                                                    <div class="w-2 h-2 bg-green-500 rounded-full mr-3"></div>
                                                    <span class="font-semibold text-gray-800">📋 Órdenes</span>
                                                </div>
                                                <span class="text-sm font-mono bg-white px-3 py-1 rounded text-gray-700">v${systemInfo.modules.orders}</span>
                                            </div>
                                            <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg">
                                                <div class="flex items-center">
                                                    <div class="w-2 h-2 bg-green-500 rounded-full mr-3"></div>
                                                    <span class="font-semibold text-gray-800">👥 Usuarios</span>
                                                </div>
                                                <span class="text-sm font-mono bg-white px-3 py-1 rounded text-gray-700">v${systemInfo.modules.users}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Información Adicional -->
                                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 p-4 rounded-lg">
                                        <div class="flex items-center justify-between mb-3">
                                            <div class="flex items-center">
                                                <i class="fas fa-building text-gray-600 mr-2"></i>
                                                <span class="text-sm font-semibold text-gray-700">Desarrollado por:</span>
                                            </div>
                                            <span class="text-sm font-bold text-gray-900">${systemInfo.company}</span>
                                        </div>
                                        <div class="flex items-center justify-center pt-3 border-t border-gray-300">
                                            <p class="text-xs text-gray-600">
                                                <i class="far fa-copyright mr-1"></i>
                                                2025 Kalli Jaguar. Todos los derechos reservados.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-gray-50 p-4 rounded-b-lg flex justify-end">
                                    <button onclick="this.closest('.fixed').remove()" class="px-6 py-2 bg-gradient-to-r from-sidebar-dark to-sidebar-darker text-white rounded-lg hover:from-accent-yellow hover:to-accent-yellow-dark transition-all">
                                        Cerrar
                                    </button>
                                </div>
                            </div>
                        `;

                        document.body.appendChild(modal);

                        // Cerrar con click fuera del modal
                        modal.addEventListener('click', function(e) {
                            if (e.target === modal) {
                                modal.remove();
                            }
                        });
                    }
                </script>