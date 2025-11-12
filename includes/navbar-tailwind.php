<?php
if (!isset($_SESSION['id']) || !isset($_SESSION['usuario'])) {
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

                    <div class="mt-8 mb-4">
                        <h3 class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Administración</h3>
                    </div>

                    <a href="index.php?page=sucursalManagement" class="nav-item-hover flex items-center px-4 py-3 text-white text-opacity-80 hover:text-white rounded-lg mb-2 relative group">
                        <i class="fas fa-home mr-3 text-lg group-hover:text-accent-yellow transition-colors"></i>
                        <span class="nav-text-small">Gestión de Sucursales</span>
                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-accent-yellow rounded-r-full transform scale-y-0 group-hover:scale-y-100 transition-transform"></div>
                    </a>

                <?php } ?>

                <div class="mb-2">
                    <button onclick="toggleDropdown('usuarios')" class="nav-item nav-item-hover w-full flex items-center justify-between px-4 py-2.5 text-white text-opacity-80 hover:text-white rounded-lg group">
                        <div class="flex items-center">
                            <i class="fas fa-user mr-3 text-base group-hover:text-accent-yellow transition-colors"></i>
                            <span class="nav-text">Usuarios</span>
                        </div>
                        <i id="usuarios-icon" class="fas fa-chevron-right transition-transform group-hover:text-accent-yellow text-sm"></i>
                    </button>
                    <div id="usuarios-dropdown" class="hidden ml-4 mt-2 space-y-1">
                        <a href="index.php?page=addUser" class="dropdown-item flex items-center px-4 py-2 text-white text-opacity-70 hover:text-accent-yellow transition-all">
                            <i class="fas fa-user-plus mr-2 text-xs"></i>
                            <span class="nav-text-small">Agregar Usuario</span>
                        </a>
                        <a href="index.php?page=showUser" class="dropdown-item flex items-center px-4 py-2 text-white text-opacity-70 hover:text-accent-yellow transition-all">
                            <i class="fas fa-users mr-2 text-xs"></i>
                            <span class="nav-text-small">Lista de Usuarios</span>
                        </a>
                        <a href="index.php?page=searchUser" class="dropdown-item flex items-center px-4 py-2 text-white text-opacity-70 hover:text-accent-yellow transition-all">
                            <i class="fas fa-search mr-2 text-xs"></i>
                            <span class="nav-text-small">Buscar Usuarios</span>
                        </a>

                        <?php if ($_SESSION['id'] == '1') { ?>
                            <div class="border-t border-white border-opacity-10 my-2"></div>

                            <div class="px-4 py-1">
                                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Gestión Avanzada</span>
                            </div>
                            <a href="index.php?page=userManagement" class="dropdown-item flex items-center px-4 py-2 text-white text-opacity-70 hover:text-accent-yellow transition-all">
                                <i class="fas fa-users-cog mr-2 text-xs"></i>
                                <span class="nav-text-small">Gestión de Usuarios</span>
                            </a>
                            <a href="index.php?page=userActivity" class="dropdown-item flex items-center px-4 py-2 text-white text-opacity-70 hover:text-accent-yellow transition-all">
                                <i class="fas fa-chart-line mr-2 text-xs"></i>
                                <span class="nav-text-small">Actividad de Usuarios</span>
                            </a>
                        <?php } ?>
                    </div>
                </div>

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
                        <a href="index.php?page=addCategory" class="dropdown-item flex items-center px-4 py-2 text-white text-opacity-70 hover:text-accent-yellow transition-all">
                            <i class="fas fa-plus mr-2 text-xs"></i>
                            <span class="nav-text-small">Agregar Categoría</span>
                        </a>
                        <a href="index.php?page=showCategory" class="dropdown-item flex items-center px-4 py-2 text-white text-opacity-70 hover:text-accent-yellow transition-all">
                            <i class="fas fa-list mr-2 text-xs"></i>
                            <span class="nav-text-small">Lista de Categoría</span>
                        </a>
                        <a href="index.php?page=searchCategory" class="dropdown-item flex items-center px-4 py-2 text-white text-opacity-70 hover:text-accent-yellow transition-all">
                            <i class="fas fa-search mr-2 text-xs"></i>
                            <span class="nav-text-small">Buscar Categoría</span>
                        </a>

                        <div class="border-t border-white border-opacity-10 my-2"></div>

                        <div class="px-4 py-1">
                            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Producto</span>
                        </div>
                        <a href="index.php?page=addProduct" class="dropdown-item flex items-center px-4 py-2 text-white text-opacity-70 hover:text-accent-yellow transition-all">
                            <i class="fas fa-plus mr-2 text-xs"></i>
                            <span class="nav-text-small">Agregar Productos</span>
                        </a>
                        <a href="index.php?page=showProduct" class="dropdown-item flex items-center px-4 py-2 text-white text-opacity-70 hover:text-accent-yellow transition-all">
                            <i class="fas fa-boxes mr-2 text-xs"></i>
                            <span class="nav-text-small">Lista de Productos</span>
                        </a>
                        <a href="index.php?page=productsByCategory" class="dropdown-item flex items-center px-4 py-2 text-white text-opacity-70 hover:text-accent-yellow transition-all">
                            <i class="fas fa-tags mr-2 text-xs"></i>
                            <span class="nav-text-small">Productos por Categoría</span>
                        </a>
                        <a href="index.php?page=searchProduct" class="dropdown-item flex items-center px-4 py-2 text-white text-opacity-70 hover:text-accent-yellow transition-all">
                            <i class="fas fa-search mr-2 text-xs"></i>
                            <span class="nav-text-small">Buscar Producto</span>
                        </a>
                        <a href="index.php?page=scanProducts" class="dropdown-item flex items-center px-4 py-2 text-white text-opacity-70 hover:text-accent-yellow transition-all">
                            <i class="fas fa-qrcode mr-2 text-xs"></i>
                            <span class="nav-text-small">Actualizar Stock</span>
                        </a>
                    </div>
                </div>

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