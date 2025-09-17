<?php
require_once('./controllers/mainController.php');
$pdo = conexion();

$sql = "SELECT 
    ls.id, 
    ls.UPC, 
    COALESCE(p.Nombre, 'Producto no encontrado') AS NombreProducto,
    ls.StockBefore, 
    ls.StockAfter, 
    ls.Fecha, 
    COALESCE(u.Nombre, 'Sistema') AS Usuario
FROM Logs_stock ls
LEFT JOIN Usuarios u ON ls.UsuarioID = u.UsuarioID
LEFT JOIN Productos p ON ls.UPC = p.UPC
ORDER BY ls.Fecha DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error en consulta SQL: " . $e->getMessage());
    $logs = [];
}

$users = array_unique(array_column($logs, 'Usuario'));
$products = array_unique(array_column($logs, 'NombreProducto'));
sort($users);
sort($products);
?>

<head>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .btn-primary {
            background-color: #3b82f6;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            transition: background-color 0.2s;
        }

        .btn-primary:hover {
            background-color: #2563eb;
        }

        .btn-secondary {
            background-color: #6b7280;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            transition: background-color 0.2s;
        }

        .btn-secondary:hover {
            background-color: #4b5563;
        }

        .btn-success {
            background-color: #059669;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            transition: background-color 0.2s;
        }

        .btn-success:hover {
            background-color: #047857;
        }

        @media (max-width: 640px) {
            .container {
                padding-left: 1rem;
                padding-right: 1rem;
            }

            .btn-primary,
            .btn-secondary,
            .btn-success {
                padding: 0.375rem 0.75rem;
                font-size: 0.875rem;
            }

            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .mobile-table-card {
                display: block;
                background: white;
                border: 1px solid #e5e7eb;
                border-radius: 0.5rem;
                margin-bottom: 1rem;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            }

            .mobile-table-header {
                background: #f9fafb;
                padding: 0.75rem;
                border-bottom: 1px solid #e5e7eb;
                font-weight: 600;
                font-size: 0.875rem;
            }

            .mobile-table-body {
                padding: 0.75rem;
            }

            .mobile-table-row {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 0.375rem 0;
                border-bottom: 1px solid #f3f4f6;
            }

            .mobile-table-label {
                font-weight: 500;
                color: #6b7280;
                font-size: 0.75rem;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                flex-shrink: 0;
                width: 35%;
            }

            .mobile-table-value {
                text-align: right;
                flex-grow: 1;
                font-size: 0.875rem;
            }
        }

        @media (min-width: 641px) and (max-width: 1024px) {

            .tablet-compact {
                font-size: 0.875rem;
            }

            .tablet-padding {
                padding: 0.5rem;
            }
        }

        @media (max-width: 1024px) {

            .hide-on-mobile {
                display: none !important;
            }

            .show-on-mobile {
                display: block !important;
            }

            .stack-on-mobile {
                flex-direction: column !important;
                gap: 1rem !important;
            }

            .full-width-on-mobile {
                width: 100% !important;
            }
        }

        @media (hover: none) and (pointer: coarse) {
            button {
                min-height: 44px;
                min-width: 44px;
            }

            .pagination-btn {
                padding: 0.75rem !important;
            }
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-2 sm:px-4 py-4 sm:py-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-4 sm:mb-6">
            <div class="px-3 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
                    <div class="flex items-center space-x-3 sm:space-x-4">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-history text-blue-600 text-lg sm:text-xl"></i>
                        </div>
                        <div>
                            <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Historial de Stock</h1>
                            <p class="text-sm sm:text-base text-gray-600 hide-on-mobile">Seguimiento de movimientos de inventario</p>
                        </div>
                    </div>
                    <div class="bg-blue-50 px-3 sm:px-4 py-2 rounded-lg flex-shrink-0">
                        <div class="text-xs sm:text-sm text-blue-600 font-medium">Total Registros</div>
                        <div class="text-xl sm:text-2xl font-bold text-blue-700" id="totalRecords"><?= count($logs) ?></div>
                    </div>
                </div>
            </div>

            <div class="px-3 sm:px-6 py-3 sm:py-4">
                <div class="flex flex-col space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1 sm:hidden">Usuario</label>
                            <select id="userFilter" class="w-full px-2 sm:px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Todos los usuarios</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?= htmlspecialchars($user) ?>"><?= htmlspecialchars($user) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1 sm:hidden">Producto</label>
                            <select id="productFilter" class="w-full px-2 sm:px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Todos los productos</option>
                                <?php foreach ($products as $product): ?>
                                    <option value="<?= htmlspecialchars($product) ?>"><?= htmlspecialchars($product) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-span-1 sm:col-span-2 lg:col-span-1">
                            <label class="block text-xs font-medium text-gray-700 mb-1 sm:hidden">Fechas</label>
                            <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                                <input type="date" id="dateFrom" class="flex-1 px-2 sm:px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Desde">
                                <input type="date" id="dateTo" class="flex-1 px-2 sm:px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Hasta">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1 sm:hidden">Tipo de Cambio</label>
                            <select id="changeTypeFilter" class="w-full px-2 sm:px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Todos los cambios</option>
                                <option value="increase">Solo incrementos</option>
                                <option value="decrease">Solo decrementos</option>
                                <option value="equal">Sin cambios</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
                        <button id="clearFilters" class="btn-secondary flex items-center justify-center space-x-2 full-width-on-mobile">
                            <i class="fas fa-eraser text-sm"></i>
                            <span>Limpiar</span>
                        </button>
                        <button id="exportData" class="btn-success flex items-center justify-center space-x-2 full-width-on-mobile">
                            <i class="fas fa-download text-sm"></i>
                            <span>Exportar</span>
                        </button>
                        <button id="refreshData" class="btn-primary flex items-center justify-center space-x-2 full-width-on-mobile">
                            <i class="fas fa-sync-alt text-sm" id="refreshIcon"></i>
                            <span>Actualizar</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 hidden sm:block">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Movimientos de Stock</h3>
            </div>

            <div class="overflow-x-auto table-responsive">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-cube text-gray-400"></i>
                                    <span class="hidden sm:inline">Producto</span>
                                    <span class="sm:hidden">Prod.</span>
                                </div>
                            </th>
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hide-on-mobile">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-barcode text-gray-400"></i>
                                    <span>UPC</span>
                                </div>
                            </th>
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-arrow-left text-red-400"></i>
                                    <span class="hidden sm:inline">Stock Anterior</span>
                                    <span class="sm:hidden">Ant.</span>
                                </div>
                            </th>
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-arrow-right text-green-400"></i>
                                    <span class="hidden sm:inline">Stock Actual</span>
                                    <span class="sm:hidden">Act.</span>
                                </div>
                            </th>
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-calendar text-gray-400"></i>
                                    <span>Fecha</span>
                                </div>
                            </th>
                            <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hide-on-mobile">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-user text-gray-400"></i>
                                    <span>Usuario</span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody id="tableBody" class="bg-white divide-y divide-gray-200">
                    </tbody>
                </table>
            </div>

            <div class="px-3 sm:px-6 py-3 sm:py-4 border-t border-gray-200 bg-gray-50">
                <div class="flex flex-col sm:flex-row justify-between items-center space-y-3 sm:space-y-0">
                    <div class="flex flex-col sm:flex-row items-center sm:space-x-4 text-sm text-gray-700 space-y-2 sm:space-y-0">
                        <div class="text-center sm:text-left">
                            Mostrando <span id="showingFrom" class="font-medium">1</span> a
                            <span id="showingTo" class="font-medium">25</span> de
                            <span id="showingTotal" class="font-medium">0</span> registros
                        </div>
                        <div>
                            <select id="pageSize" class="px-2 py-1 border border-gray-300 rounded text-sm">
                                <option value="10">10</option>
                                <option value="25" selected>25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center space-x-1">
                        <button id="firstPage" class="pagination-btn px-2 sm:px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-l-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-angle-double-left"></i>
                        </button>
                        <button id="prevPage" class="pagination-btn px-2 sm:px-3 py-2 text-sm font-medium text-gray-500 bg-white border-t border-b border-gray-300 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-angle-left"></i>
                        </button>

                        <div id="pageNumbers" class="flex items-center">
                        </div>

                        <button id="nextPage" class="pagination-btn px-2 sm:px-3 py-2 text-sm font-medium text-gray-500 bg-white border-t border-b border-gray-300 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-angle-right"></i>
                        </button>
                        <button id="lastPage" class="pagination-btn px-2 sm:px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-r-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-angle-double-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="sm:hidden space-y-4" id="mobileView">
        </div>

        <div class="sm:hidden bg-white rounded-lg shadow-sm border border-gray-200 mt-4">
            <div class="px-4 py-3 border-b border-gray-200">
                <div class="flex justify-between items-center text-sm text-gray-700">
                    <span>Mostrando <span id="mobileShowingFrom">1</span> a <span id="mobileShowingTo">25</span> de <span id="mobileShowingTotal">0</span></span>
                    <select id="mobilePageSize" class="px-2 py-1 border border-gray-300 rounded text-sm">
                        <option value="10">10</option>
                        <option value="25" selected>25</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>
            <div class="px-4 py-3">
                <div class="flex justify-between items-center">
                    <button id="mobilePrevPage" class="flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-chevron-left mr-2"></i>
                        Anterior
                    </button>
                    <span id="mobilePageInfo" class="text-sm text-gray-700 font-medium">
                        Página 1 de 1
                    </span>
                    <button id="mobileNextPage" class="flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                        Siguiente
                        <i class="fas fa-chevron-right ml-2"></i>
                    </button>
                </div>
            </div>
        </div>

        <div id="emptyState" class="text-center py-8 sm:py-12 hidden">
            <div class="w-12 h-12 sm:w-16 sm:h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-inbox text-gray-400 text-xl sm:text-2xl"></i>
            </div>
            <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-2">No hay registros</h3>
            <p class="text-gray-500 text-sm sm:text-base">No se encontraron movimientos que coincidan con los filtros aplicados.</p>
        </div>
    </div>

    <div id="toastContainer" class="fixed top-4 right-4 z-50 space-y-2"></div>

    <script>
        $(document).ready(function() {
            let allData = <?= json_encode($logs) ?>;
            let filteredData = [...allData];
            let currentPage = 1;
            let pageSize = 25;

            function isMobile() {
                return window.innerWidth < 640;
            }

            renderTable();
            updatePagination();

            $('#userFilter').on('change', applyFilters);
            $('#productFilter').on('change', applyFilters);
            $('#dateFrom').on('change', applyFilters);
            $('#dateTo').on('change', applyFilters);
            $('#changeTypeFilter').on('change', applyFilters);

            $('#pageSize, #mobilePageSize').on('change', function() {
                pageSize = parseInt($(this).val());
                $('#pageSize, #mobilePageSize').val(pageSize);
                currentPage = 1;
                renderTable();
                updatePagination();
            });

            $('#clearFilters').on('click', clearAllFilters);
            $('#exportData').on('click', exportToCSV);
            $('#refreshData').on('click', refreshPage);

            $('#firstPage').on('click', () => goToPage(1));
            $('#prevPage').on('click', () => goToPage(currentPage - 1));
            $('#nextPage').on('click', () => goToPage(currentPage + 1));
            $('#lastPage').on('click', () => goToPage(Math.ceil(filteredData.length / pageSize)));

            $('#mobilePrevPage').on('click', () => goToPage(currentPage - 1));
            $('#mobileNextPage').on('click', () => goToPage(currentPage + 1));

            $(document).on('click', '.page-btn', function() {
                goToPage(parseInt($(this).data('page')));
            });

            let resizeTimeout;
            $(window).on('resize', function() {
                clearTimeout(resizeTimeout);
                resizeTimeout = setTimeout(function() {
                    renderTable();
                    updatePagination();
                }, 100);
            });

            function applyFilters() {
                console.log('Aplicando filtros...');

                filteredData = allData.filter(record => {
                    const userFilter = $('#userFilter').val();
                    if (userFilter && record.Usuario !== userFilter) return false;

                    const productFilter = $('#productFilter').val();
                    if (productFilter && record.NombreProducto !== productFilter) return false;

                    const dateFrom = $('#dateFrom').val();
                    const dateTo = $('#dateTo').val();

                    if (dateFrom || dateTo) {
                        const recordDate = new Date(record.Fecha).toISOString().split('T')[0];
                        if (dateFrom && recordDate < dateFrom) return false;
                        if (dateTo && recordDate > dateTo) return false;
                    }

                    const changeType = $('#changeTypeFilter').val();
                    if (changeType) {
                        const before = parseInt(record.StockBefore);
                        const after = parseInt(record.StockAfter);
                        const diff = after - before;

                        if (changeType === 'increase' && diff <= 0) return false;
                        if (changeType === 'decrease' && diff >= 0) return false;
                        if (changeType === 'equal' && diff !== 0) return false;
                    }

                    return true;
                });

                console.log('Registros filtrados:', filteredData.length);
                currentPage = 1;
                renderTable();
                updatePagination();
            }

            function renderTable() {
                if (isMobile()) {
                    $('#mobileView').show();
                    $('.sm\\:block').hide();
                    renderMobileCards();
                } else {
                    $('#mobileView').hide();
                    $('.sm\\:block').show();
                    renderDesktopTable();
                }
            }

            function renderDesktopTable() {
                const tbody = $('#tableBody');
                tbody.empty();

                if (filteredData.length === 0) {
                    $('#emptyState').removeClass('hidden');
                    updateCounters(0, 0, 0);
                    return;
                }

                $('#emptyState').addClass('hidden');

                const startIndex = (currentPage - 1) * pageSize;
                const endIndex = Math.min(startIndex + pageSize, filteredData.length);
                const pageData = filteredData.slice(startIndex, endIndex);

                pageData.forEach(record => {
                    const stockBefore = parseInt(record.StockBefore);
                    const stockAfter = parseInt(record.StockAfter);
                    const difference = stockAfter - stockBefore;

                    let arrowIcon = 'fa-equals text-gray-500';
                    let diffText = '0';
                    let diffColor = 'text-gray-600';

                    if (difference > 0) {
                        arrowIcon = 'fa-arrow-up text-green-500';
                        diffText = `+${difference}`;
                        diffColor = 'text-green-600';
                    } else if (difference < 0) {
                        arrowIcon = 'fa-arrow-down text-red-500';
                        diffText = difference.toString();
                        diffColor = 'text-red-600';
                    }

                    const date = new Date(record.Fecha);
                    const formattedDate = date.toLocaleDateString('es-ES');
                    const formattedTime = date.toLocaleTimeString('es-ES', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });

                    const row = `
                        <tr class="hover:bg-gray-50 transition-colors duration-150 fade-in">
                            <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 sm:w-10 sm:h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-2 sm:mr-3 flex-shrink-0">
                                        <i class="fas fa-cube text-blue-600 text-xs sm:text-sm"></i>
                                    </div>
                                    <div class="text-xs sm:text-sm font-medium text-gray-900 truncate">
                                        ${record.NombreProducto}
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap hide-on-mobile">
                                <div class="text-xs sm:text-sm text-gray-900 font-mono bg-gray-100 px-2 py-1 rounded">
                                    ${record.UPC}
                                </div>
                            </td>
                            <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    ${stockBefore}
                                </span>
                            </td>
                            <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-1 sm:space-x-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        ${stockAfter}
                                    </span>
                                    <div class="flex items-center space-x-1 hide-on-mobile">
                                        <i class="fas ${arrowIcon} text-xs"></i>
                                        <span class="text-xs ${diffColor} font-medium">${diffText}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                                <div class="text-xs sm:text-sm text-gray-900">${formattedDate}</div>
                                <div class="text-xs text-gray-500 hide-on-mobile">${formattedTime}</div>
                            </td>
                            <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap hide-on-mobile">
                                <div class="flex items-center">
                                    <div class="w-6 h-6 sm:w-8 sm:h-8 bg-gray-200 rounded-full flex items-center justify-center mr-2 sm:mr-3 flex-shrink-0">
                                        <span class="text-xs font-medium text-gray-600">
                                            ${record.Usuario.charAt(0).toUpperCase()}
                                        </span>
                                    </div>
                                    <div class="text-xs sm:text-sm font-medium text-gray-900 truncate">
                                        ${record.Usuario}
                                    </div>
                                </div>
                            </td>
                        </tr>
                    `;

                    tbody.append(row);
                });

                updateCounters(startIndex + 1, endIndex, filteredData.length);
            }

            function renderMobileCards() {
                const container = $('#mobileView');
                container.empty();

                if (filteredData.length === 0) {
                    $('#emptyState').removeClass('hidden');
                    updateMobileCounters(0, 0, 0);
                    return;
                }

                $('#emptyState').addClass('hidden');

                const startIndex = (currentPage - 1) * pageSize;
                const endIndex = Math.min(startIndex + pageSize, filteredData.length);
                const pageData = filteredData.slice(startIndex, endIndex);

                pageData.forEach(record => {
                    const stockBefore = parseInt(record.StockBefore);
                    const stockAfter = parseInt(record.StockAfter);
                    const difference = stockAfter - stockBefore;

                    let arrowIcon = 'fa-equals text-gray-500';
                    let diffText = '0';
                    let diffColor = 'text-gray-600';

                    if (difference > 0) {
                        arrowIcon = 'fa-arrow-up text-green-500';
                        diffText = `+${difference}`;
                        diffColor = 'text-green-600';
                    } else if (difference < 0) {
                        arrowIcon = 'fa-arrow-down text-red-500';
                        diffText = difference.toString();
                        diffColor = 'text-red-600';
                    }

                    const date = new Date(record.Fecha);
                    const formattedDate = date.toLocaleDateString('es-ES');
                    const formattedTime = date.toLocaleTimeString('es-ES', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });

                    const card = `
                        <div class="mobile-table-card fade-in">
                            <div class="mobile-table-header">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                        <i class="fas fa-cube text-blue-600"></i>
                                    </div>
                                    <div class="font-medium text-gray-900 truncate">
                                        ${record.NombreProducto}
                                    </div>
                                </div>
                            </div>
                            <div class="mobile-table-body space-y-2">
                                <div class="mobile-table-row">
                                    <div class="mobile-table-label">UPC</div>
                                    <div class="mobile-table-value">
                                        <span class="font-mono bg-gray-100 px-2 py-1 rounded text-xs">
                                            ${record.UPC}
                                        </span>
                                    </div>
                                </div>
                                <div class="mobile-table-row">
                                    <div class="mobile-table-label">Stock</div>
                                    <div class="mobile-table-value">
                                        <div class="flex items-center space-x-2">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                ${stockBefore}
                                            </span>
                                            <i class="fas ${arrowIcon} text-xs"></i>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                ${stockAfter}
                                            </span>
                                            <span class="text-xs ${diffColor} font-medium">(${diffText})</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="mobile-table-row">
                                    <div class="mobile-table-label">Fecha</div>
                                    <div class="mobile-table-value">
                                        <div>${formattedDate}</div>
                                        <div class="text-xs text-gray-500">${formattedTime}</div>
                                    </div>
                                </div>
                                <div class="mobile-table-row">
                                    <div class="mobile-table-label">Usuario</div>
                                    <div class="mobile-table-value">
                                        <div class="flex items-center justify-end">
                                            <div class="w-6 h-6 bg-gray-200 rounded-full flex items-center justify-center mr-2">
                                                <span class="text-xs font-medium text-gray-600">
                                                    ${record.Usuario.charAt(0).toUpperCase()}
                                                </span>
                                            </div>
                                            <span class="text-sm font-medium">${record.Usuario}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;

                    container.append(card);
                });

                updateMobileCounters(startIndex + 1, endIndex, filteredData.length);
            }

            function updatePagination() {
                const totalPages = Math.ceil(filteredData.length / pageSize);

                $('#firstPage, #prevPage').prop('disabled', currentPage === 1);
                $('#lastPage, #nextPage').prop('disabled', currentPage === totalPages || totalPages === 0);

                $('#mobilePrevPage').prop('disabled', currentPage === 1);
                $('#mobileNextPage').prop('disabled', currentPage === totalPages || totalPages === 0);
                $('#mobilePageInfo').text(`Página ${currentPage} de ${Math.max(1, totalPages)}`);

                if (!isMobile()) {
                    const pageNumbers = $('#pageNumbers');
                    pageNumbers.empty();

                    if (totalPages <= 1) return;

                    const maxVisible = window.innerWidth < 768 ? 3 : 5;
                    let startPage = Math.max(1, currentPage - Math.floor(maxVisible / 2));
                    let endPage = Math.min(totalPages, startPage + maxVisible - 1);

                    if (endPage - startPage + 1 < maxVisible) {
                        startPage = Math.max(1, endPage - maxVisible + 1);
                    }

                    if (startPage > 1) {
                        pageNumbers.append(createPageButton(1, false));
                        if (startPage > 2) {
                            pageNumbers.append('<span class="px-1 sm:px-2 py-2 text-gray-500 text-xs sm:text-sm">...</span>');
                        }
                    }

                    for (let i = startPage; i <= endPage; i++) {
                        pageNumbers.append(createPageButton(i, i === currentPage));
                    }

                    if (endPage < totalPages) {
                        if (endPage < totalPages - 1) {
                            pageNumbers.append('<span class="px-1 sm:px-2 py-2 text-gray-500 text-xs sm:text-sm">...</span>');
                        }
                        pageNumbers.append(createPageButton(totalPages, false));
                    }
                }
            }

            function createPageButton(pageNum, isActive) {
                const activeClasses = isActive ?
                    'bg-blue-600 text-white border-blue-600' :
                    'bg-white text-gray-500 border-gray-300 hover:bg-gray-50';

                return `<button class="page-btn px-2 sm:px-3 py-2 text-xs sm:text-sm font-medium border ${activeClasses} transition-colors duration-200" data-page="${pageNum}">${pageNum}</button>`;
            }

            function goToPage(page) {
                const totalPages = Math.ceil(filteredData.length / pageSize);
                
                if (page < 1) page = 1;
                if (page > totalPages) page = totalPages;
                if (totalPages === 0) page = 1;

                if (currentPage !== page) {
                    currentPage = page;
                    renderTable();
                    updatePagination();
                }
            }

            function updateCounters(from, to, total) {
                from = Math.max(1, from);
                to = Math.max(from, to);
                total = Math.max(0, total);
                
                if (total === 0) {
                    from = 0;
                    to = 0;
                }
                
                $('#showingFrom').text(from);
                $('#showingTo').text(to);
                $('#showingTotal').text(total);
                $('#totalRecords').text(total);
            }

            function updateMobileCounters(from, to, total) {
                from = Math.max(1, from);
                to = Math.max(from, to);
                total = Math.max(0, total);
                
                if (total === 0) {
                    from = 0;
                    to = 0;
                }
                
                $('#mobileShowingFrom').text(from);
                $('#mobileShowingTo').text(to);
                $('#mobileShowingTotal').text(total);
                $('#totalRecords').text(total);
            }

            function clearAllFilters() {
                $('#userFilter, #productFilter, #changeTypeFilter').val('');
                $('#dateFrom, #dateTo').val('');

                filteredData = [...allData];
                currentPage = 1;
                renderTable();
                updatePagination();

                showToast('Filtros limpiados correctamente', 'success');
            }

            function exportToCSV() {
                if (filteredData.length === 0) {
                    showToast('No hay datos para exportar', 'warning');
                    return;
                }

                let csvContent = "data:text/csv;charset=utf-8,";
                csvContent += "Producto,UPC,Stock Anterior,Stock Actual,Diferencia,Fecha,Hora,Usuario\n";

                filteredData.forEach(record => {
                    const stockBefore = parseInt(record.StockBefore);
                    const stockAfter = parseInt(record.StockAfter);
                    const difference = stockAfter - stockBefore;

                    const date = new Date(record.Fecha);
                    const formattedDate = date.toLocaleDateString('es-ES');
                    const formattedTime = date.toLocaleTimeString('es-ES');

                    csvContent += `"${record.NombreProducto}","${record.UPC}","${stockBefore}","${stockAfter}","${difference}","${formattedDate}","${formattedTime}","${record.Usuario}"\n`;
                });

                const encodedUri = encodeURI(csvContent);
                const link = document.createElement("a");
                link.setAttribute("href", encodedUri);
                link.setAttribute("download", `historial_stock_${new Date().toISOString().split('T')[0]}.csv`);
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                showToast('Datos exportados correctamente', 'success');
            }

            function refreshPage() {
                const icon = $('#refreshIcon');
                icon.addClass('fa-spin');
                $('#refreshData').prop('disabled', true);

                setTimeout(() => {
                    location.reload();
                }, 1000);
            }

            function showToast(message, type = 'info') {
                const colors = {
                    success: 'bg-green-100 text-green-800 border-green-200',
                    error: 'bg-red-100 text-red-800 border-red-200',
                    warning: 'bg-yellow-100 text-yellow-800 border-yellow-200',
                    info: 'bg-blue-100 text-blue-800 border-blue-200'
                };

                const icons = {
                    success: 'fa-check-circle',
                    error: 'fa-exclamation-circle',
                    warning: 'fa-exclamation-triangle',
                    info: 'fa-info-circle'
                };

                const toast = $(`
                    <div class="max-w-sm w-full ${colors[type]} border rounded-lg p-4 shadow-lg transform translate-x-full transition-transform duration-300">
                        <div class="flex items-center">
                            <i class="fas ${icons[type]} mr-3"></i>
                            <span class="font-medium text-sm">${message}</span>
                        </div>
                    </div>
                `);

                $('#toastContainer').append(toast);

                setTimeout(() => toast.removeClass('translate-x-full'), 100);

                setTimeout(() => {
                    toast.addClass('translate-x-full');
                    setTimeout(() => toast.remove(), 300);
                }, 3000);
            }

            console.log('Inicialización completa - Página responsive funcional');
        });
    </script>
</body>