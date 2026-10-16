<?php
if (!isset($_SESSION['id'])) {
    echo "<script>window.location.href='index.php?page=404';</script>";
    exit;
}
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        .gradient-bg { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
            overflow: hidden;
        }
        
        .gradient-bg::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 20"><defs><radialGradient id="grain"><stop offset="10%" stop-color="white" stop-opacity="0.1"/><stop offset="100%" stop-color="white" stop-opacity="0"/></radialGradient></defs><rect width="100" height="20" fill="url(%23grain)"/></svg>');
        }
        
        .glass-effect { 
            backdrop-filter: blur(16px); 
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .card-hover {
            transition: all 0.3s ease;
        }
        
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        .btn-action {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        
        .btn-action::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn-action:hover::before {
            left: 100%;
        }
        
        .status-badge {
            position: relative;
            overflow: hidden;
        }
        
        .status-activa { background: linear-gradient(135deg, #10b981, #059669); }
        .status-inactiva { background: linear-gradient(135deg, #ef4444, #dc2626); }
        .status-mantenimiento { background: linear-gradient(135deg, #f59e0b, #d97706); }
        
        .tipo-badge {
            position: relative;
            overflow: hidden;
        }
        
        .tipo-principal { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
        .tipo-sucursal { background: linear-gradient(135deg, #3b82f6, #2563eb); }
        .tipo-almacen { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .tipo-punto_venta { background: linear-gradient(135deg, #10b981, #059669); }
        
        .modal-backdrop {
            backdrop-filter: blur(8px);
            background: rgba(0, 0, 0, 0.5);
        }
        
        .fade-in {
            animation: fadeIn 0.3s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .loading-spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="gradient-bg text-white p-6 md:p-8">
        <div class="max-w-7xl mx-auto relative z-10">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between space-y-4 md:space-y-0">
                <div>
                    <h1 class="text-3xl md:text-4xl font-bold mb-2">Gestión de Sucursales</h1>
                    <p class="text-white/90 text-lg">Administra todas las ubicaciones de tu inventario</p>
                </div>
                
                <div class="flex flex-wrap gap-3">
                    <button onclick="showCreateModal()" class="btn-action bg-white/20 hover:bg-white/30 text-white px-6 py-3 rounded-xl flex items-center space-x-2 font-semibold">
                        <i class="fas fa-plus"></i>
                        <span>Nueva Sucursal</span>
                    </button>
                    <button onclick="exportSucursales()" class="btn-action bg-green-500/90 hover:bg-green-500 text-white px-6 py-3 rounded-xl flex items-center space-x-2 font-semibold">
                        <i class="fas fa-download"></i>
                        <span class="hidden sm:inline">Exportar</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-6">
        <div class="glass-effect rounded-2xl shadow-2xl border border-white/20 p-6 mb-8 fade-in">
            <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-6 gap-4">
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-search mr-1"></i>Buscar
                    </label>
                    <input type="text" id="searchInput" placeholder="Nombre, dirección o gerente..." 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-circle mr-1"></i>Estado
                    </label>
                    <select id="estadoFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Todos</option>
                        <option value="activa">Activa</option>
                        <option value="inactiva">Inactiva</option>
                        <option value="mantenimiento">Mantenimiento</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-building mr-1"></i>Tipo
                    </label>
                    <select id="tipoFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Todos</option>
                        <option value="principal">Principal</option>
                        <option value="sucursal">Sucursal</option>
                        <option value="almacen">Almacén</option>
                        <option value="punto_venta">Punto de Venta</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-sort mr-1"></i>Ordenar por
                    </label>
                    <select id="sortBy" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="nombre">Nombre</option>
                        <option value="fecha_creacion">Fecha Creación</option>
                        <option value="estado">Estado</option>
                        <option value="tipo">Tipo</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-arrow-up-down mr-1"></i>Orden
                    </label>
                    <select id="sortOrder" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="ASC">A-Z</option>
                        <option value="DESC">Z-A</option>
                    </select>
                </div>
            </div>
            
            <div class="flex justify-between items-center mt-4">
                <button onclick="clearFilters()" class="text-gray-600 hover:text-gray-800 text-sm font-medium">
                    <i class="fas fa-times mr-1"></i>Limpiar filtros
                </button>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-600">Mostrando: <span id="resultsCount">0</span> sucursales</span>
                    <select id="limitSelect" class="px-3 py-1 border border-gray-300 rounded-lg text-sm">
                        <option value="10">10 por página</option>
                        <option value="25">25 por página</option>
                        <option value="50">50 por página</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden mb-8">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                                <i class="fas fa-building mr-2"></i>Sucursal
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                                <i class="fas fa-map-marker-alt mr-2"></i>Ubicación
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                                <i class="fas fa-user-tie mr-2"></i>Gerente
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                                <i class="fas fa-info-circle mr-2"></i>Estado/Tipo
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                                <i class="fas fa-clock mr-2"></i>Horario
                            </th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">
                                <i class="fas fa-cogs mr-2"></i>Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody id="sucursalesTableBody" class="bg-white divide-y divide-gray-200">
                    </tbody>
                </table>
            </div>
            
            <div id="loadingState" class="flex items-center justify-center py-12">
                <div class="loading-spinner mr-3"></div>
                <span class="text-gray-600">Cargando sucursales...</span>
            </div>
            
            <div id="emptyState" class="hidden text-center py-12">
                <i class="fas fa-building text-4xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No hay sucursales</h3>
                <p class="text-gray-600 mb-4">Comienza creando tu primera sucursal</p>
                <button onclick="showCreateModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                    <i class="fas fa-plus mr-2"></i>Crear Sucursal
                </button>
            </div>
        </div>

        <div id="paginationContainer" class="flex items-center justify-between bg-white rounded-xl shadow-lg border border-gray-100 px-6 py-4">
        </div>
    </div>

    <div id="sucursalModal" class="hidden fixed inset-0 modal-backdrop z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white p-6 rounded-t-2xl">
                <div class="flex items-center justify-between">
                    <h3 id="modalTitle" class="text-xl font-bold">Nueva Sucursal</h3>
                    <button onclick="closeModal()" class="text-white hover:text-gray-200">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            
            <form id="sucursalForm" class="p-6">
                <input type="hidden" id="sucursalId" name="sucursal_id">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <h4 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">
                            <i class="fas fa-info-circle text-blue-600 mr-2"></i>Información Básica
                        </h4>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Nombre de la Sucursal <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="nombre" name="nombre" required maxlength="100"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Dirección</label>
                            <textarea id="direccion" name="direccion" rows="3" maxlength="255"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Teléfono</label>
                            <input type="tel" id="telefono" name="telefono" maxlength="20"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email" id="email" name="email" maxlength="100"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <h4 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">
                            <i class="fas fa-cogs text-purple-600 mr-2"></i>Configuración
                        </h4>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Gerente</label>
                            <input type="text" id="gerente" name="gerente" maxlength="100"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                                <select id="estado" name="estado" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    <option value="activa">Activa</option>
                                    <option value="inactiva">Inactiva</option>
                                    <option value="mantenimiento">Mantenimiento</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tipo</label>
                                <select id="tipo" name="tipo" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    <option value="principal">Principal</option>
                                    <option value="sucursal">Sucursal</option>
                                    <option value="almacen">Almacén</option>
                                    <option value="punto_venta">Punto de Venta</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Hora Apertura</label>
                                <input type="time" id="horario_apertura" name="horario_apertura"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Hora Cierre</label>
                                <input type="time" id="horario_cierre" name="horario_cierre"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notas</label>
                    <textarea id="notas" name="notas" rows="3" maxlength="500"
                              placeholder="Notas adicionales sobre la sucursal..."
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                
                <div class="flex justify-end space-x-4 mt-8 pt-6 border-t border-gray-200">
                    <button type="button" onclick="closeModal()" 
                            class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button type="submit" id="submitBtn"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center">
                        <span id="submitText">Guardar Sucursal</span>
                        <div id="submitSpinner" class="hidden loading-spinner ml-2"></div>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentPage = 1;
        let currentFilters = {};
        let isLoading = false;
        let isEditing = false;
        
        $(document).ready(function() {
            loadSucursales();
            setupEventListeners();
        });
        
        function setupEventListeners() {
            $('#searchInput').on('input', debounce(function() {
                currentPage = 1;
                loadSucursales();
            }, 500));
            
            $('#estadoFilter, #tipoFilter, #sortBy, #sortOrder, #limitSelect').on('change', function() {
                currentPage = 1;
                loadSucursales();
            });
            
            $('#sucursalForm').on('submit', function(e) {
                e.preventDefault();
                submitSucursalForm();
            });
            
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeModal();
                }
            });
        }
        
        function loadSucursales() {
            if (isLoading) return;
            
            isLoading = true;
            showLoadingState();
            
            const filters = {
                page: currentPage,
                limit: $('#limitSelect').val() || 10,
                search: $('#searchInput').val().trim(),
                estado: $('#estadoFilter').val(),
                tipo: $('#tipoFilter').val(),
                sort_by: $('#sortBy').val() || 'nombre',
                sort_order: $('#sortOrder').val() || 'ASC'
            };
            
            currentFilters = filters;
            
            $.get('api/sucursalController/getSucursales.php', filters)
            .done(function(response) {
                if (response.success) {
                    renderSucursalesTable(response.data);
                    renderPagination(response.pagination);
                    updateResultsCount(response.pagination);
                    
                    if (response.data.length === 0) {
                        showEmptyState();
                    } else {
                        hideEmptyState();
                    }
                } else {
                    showAlert('error', 'Error', response.message);
                    showEmptyState();
                }
            })
            .fail(function() {
                showAlert('error', 'Error de conexión', 'No se pudieron cargar las sucursales');
                showEmptyState();
            })
            .always(function() {
                hideLoadingState();
                isLoading = false;
            });
        }
        
        function renderSucursalesTable(sucursales) {
            const tbody = $('#sucursalesTableBody');
            tbody.empty();
            
            sucursales.forEach(function(sucursal) {
                const estadoClass = `status-${sucursal.estado}`;
                const tipoClass = `tipo-${sucursal.tipo}`;
                
                const horario = sucursal.horario_apertura && sucursal.horario_cierre 
                    ? `${sucursal.horario_apertura} - ${sucursal.horario_cierre}` 
                    : 'No definido';
                
                const row = `
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-500 rounded-lg flex items-center justify-center text-white font-bold mr-3">
                                    ${sucursal.nombre.charAt(0).toUpperCase()}
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">${sucursal.nombre}</div>
                                    <div class="text-xs text-gray-500">${sucursal.telefono || 'Sin teléfono'}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">${sucursal.direccion || 'Sin dirección'}</div>
                            ${sucursal.email ? `<div class="text-xs text-gray-500">${sucursal.email}</div>` : ''}
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">${sucursal.gerente || 'Sin asignar'}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="space-y-1">
                                <span class="status-badge ${estadoClass} text-white text-xs font-semibold px-2 py-1 rounded-full">
                                    ${sucursal.estado_texto}
                                </span>
                                <div>
                                    <span class="tipo-badge ${tipoClass} text-white text-xs font-semibold px-2 py-1 rounded-full">
                                        ${sucursal.tipo_texto}
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">${horario}</div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <button onclick="viewSucursal(${sucursal.SucursalID})" 
                                        class="text-blue-600 hover:text-blue-800 p-2 rounded-lg hover:bg-blue-50" 
                                        title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button onclick="editSucursal(${sucursal.SucursalID})" 
                                        class="text-purple-600 hover:text-purple-800 p-2 rounded-lg hover:bg-purple-50" 
                                        title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deleteSucursal(${sucursal.SucursalID}, '${sucursal.nombre}')" 
                                        class="text-red-600 hover:text-red-800 p-2 rounded-lg hover:bg-red-50" 
                                        title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
                tbody.append(row);
            });
        }
        
        function renderPagination(pagination) {
            const container = $('#paginationContainer');
            
            if (pagination.total_pages <= 1) {
                container.hide();
                return;
            }
            
            container.show();
            
            let paginationHtml = `
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-700">
                        Página ${pagination.current_page} de ${pagination.total_pages}
                        (${pagination.total_records} registros)
                    </span>
                </div>
                <div class="flex items-center space-x-1">
            `;
            
            if (pagination.has_prev) {
                paginationHtml += `
                    <button onclick="goToPage(${pagination.current_page - 1})" 
                            class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                `;
            }
            
            const startPage = Math.max(1, pagination.current_page - 2);
            const endPage = Math.min(pagination.total_pages, pagination.current_page + 2);
            
            for (let i = startPage; i <= endPage; i++) {
                const isActive = i === pagination.current_page;
                paginationHtml += `
                    <button onclick="goToPage(${i})" 
                            class="px-3 py-2 text-sm font-medium ${isActive ? 'text-blue-600 bg-blue-50 border-blue-500' : 'text-gray-500 bg-white border-gray-300'} border rounded-lg hover:bg-gray-50">
                        ${i}
                    </button>
                `;
            }
            
            if (pagination.has_next) {
                paginationHtml += `
                    <button onclick="goToPage(${pagination.current_page + 1})" 
                            class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                `;
            }
            
            paginationHtml += '</div>';
            
            container.html(paginationHtml);
        }
        
        function goToPage(page) {
            currentPage = page;
            loadSucursales();
        }
        
        function updateResultsCount(pagination) {
            $('#resultsCount').text(pagination.total_records);
        }
        
        function showCreateModal() {
            isEditing = false;
            $('#modalTitle').text('Nueva Sucursal');
            $('#submitText').text('Crear Sucursal');
            $('#sucursalForm')[0].reset();
            $('#sucursalId').val('');
            $('#sucursalModal').removeClass('hidden').addClass('fade-in');
            $('#nombre').focus();
        }
        
        function editSucursal(id) {
            isEditing = true;
            $('#modalTitle').text('Editar Sucursal');
            $('#submitText').text('Actualizar Sucursal');
            
            $('#sucursalModal').removeClass('hidden').addClass('fade-in');
            
            $.get('api/sucursalController/getSucursalDetails.php', { id: id })
            .done(function(response) {
                if (response.success) {
                    const sucursal = response.data;
                    $('#sucursalId').val(sucursal.SucursalID);
                    $('#nombre').val(sucursal.nombre);
                    $('#direccion').val(sucursal.direccion);
                    $('#telefono').val(sucursal.telefono);
                    $('#email').val(sucursal.email);
                    $('#gerente').val(sucursal.gerente);
                    $('#estado').val(sucursal.estado);
                    $('#tipo').val(sucursal.tipo);
                    $('#horario_apertura').val(sucursal.horario_apertura);
                    $('#horario_cierre').val(sucursal.horario_cierre);
                    $('#notas').val(sucursal.notas);
                    $('#nombre').focus();
                } else {
                    showAlert('error', 'Error', response.message);
                    closeModal();
                }
            })
            .fail(function() {
                showAlert('error', 'Error', 'No se pudieron cargar los datos de la sucursal');
                closeModal();
            });
        }
        
        function viewSucursal(id) {
            $.get('api/sucursalController/getSucursalDetails.php', { id: id })
            .done(function(response) {
                if (response.success) {
                    showSucursalDetails(response.data, response.estadisticas);
                } else {
                    showAlert('error', 'Error', response.message);
                }
            })
            .fail(function() {
                showAlert('error', 'Error', 'No se pudieron cargar los detalles');
            });
        }
        
        function showSucursalDetails(sucursal, stats) {
            const estadoClass = `status-${sucursal.estado}`;
            const tipoClass = `tipo-${sucursal.tipo}`;
            
            const modal = `
                <div class="fixed inset-0 modal-backdrop z-50 flex items-center justify-center p-4">
                    <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                        <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white p-6 rounded-t-2xl">
                            <div class="flex items-center justify-between">
                                <h3 class="text-xl font-bold">Detalles de ${sucursal.nombre}</h3>
                                <button onclick="closeDetailsModal()" class="text-white hover:text-gray-200">
                                    <i class="fas fa-times text-xl"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Información básica -->
                                <div class="space-y-4">
                                    <h4 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">
                                        <i class="fas fa-info-circle text-blue-600 mr-2"></i>Información General
                                    </h4>
                                    
                                    <div class="space-y-3">
                                        <div>
                                            <span class="text-sm font-medium text-gray-500">Nombre:</span>
                                            <div class="text-gray-900">${sucursal.nombre}</div>
                                        </div>
                                        
                                        <div>
                                            <span class="text-sm font-medium text-gray-500">Dirección:</span>
                                            <div class="text-gray-900">${sucursal.direccion || 'No especificada'}</div>
                                        </div>
                                        
                                        <div>
                                            <span class="text-sm font-medium text-gray-500">Teléfono:</span>
                                            <div class="text-gray-900">${sucursal.telefono || 'No especificado'}</div>
                                        </div>
                                        
                                        <div>
                                            <span class="text-sm font-medium text-gray-500">Email:</span>
                                            <div class="text-gray-900">${sucursal.email || 'No especificado'}</div>
                                        </div>
                                        
                                        <div>
                                            <span class="text-sm font-medium text-gray-500">Gerente:</span>
                                            <div class="text-gray-900">${sucursal.gerente || 'No asignado'}</div>
                                        </div>
                                        
                                        <div class="flex items-center space-x-4">
                                            <div>
                                                <span class="text-sm font-medium text-gray-500">Estado:</span>
                                                <div><span class="status-badge ${estadoClass} text-white text-xs font-semibold px-2 py-1 rounded-full">${sucursal.estado_texto}</span></div>
                                            </div>
                                            <div>
                                                <span class="text-sm font-medium text-gray-500">Tipo:</span>
                                                <div><span class="tipo-badge ${tipoClass} text-white text-xs font-semibold px-2 py-1 rounded-full">${sucursal.tipo_texto}</span></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Estadísticas -->
                                <div class="space-y-4">
                                    <h4 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">
                                        <i class="fas fa-chart-bar text-green-600 mr-2"></i>Estadísticas
                                    </h4>
                                    
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                                            <div class="text-2xl font-bold text-blue-600">${stats.total_productos}</div>
                                            <div class="text-sm text-blue-800">Productos</div>
                                        </div>
                                        
                                        <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                                            <div class="text-2xl font-bold text-green-600">${stats.total_movimientos}</div>
                                            <div class="text-sm text-green-800">Movimientos</div>
                                        </div>
                                        
                                        <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                                            <div class="text-2xl font-bold text-purple-600">${stats.entradas}</div>
                                            <div class="text-sm text-purple-800">Entradas</div>
                                        </div>
                                        
                                        <div class="bg-orange-50 p-4 rounded-lg border border-orange-200">
                                            <div class="text-2xl font-bold text-orange-600">${stats.salidas}</div>
                                            <div class="text-sm text-orange-800">Salidas</div>
                                        </div>
                                    </div>
                                    
                                    ${sucursal.horario_apertura && sucursal.horario_cierre ? `
                                        <div>
                                            <span class="text-sm font-medium text-gray-500">Horario:</span>
                                            <div class="text-gray-900">${sucursal.horario_apertura} - ${sucursal.horario_cierre}</div>
                                        </div>
                                    ` : ''}
                                    
                                    ${stats.ultimo_movimiento ? `
                                        <div>
                                            <span class="text-sm font-medium text-gray-500">Último movimiento:</span>
                                            <div class="text-gray-900">${stats.ultimo_movimiento}</div>
                                        </div>
                                    ` : ''}
                                </div>
                            </div>
                            
                            ${sucursal.notas ? `
                                <div class="mt-6">
                                    <h4 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2 mb-4">
                                        <i class="fas fa-sticky-note text-yellow-600 mr-2"></i>Notas
                                    </h4>
                                    <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
                                        <p class="text-gray-900">${sucursal.notas}</p>
                                    </div>
                                </div>
                            ` : ''}
                            
                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <div class="flex justify-between items-center text-sm text-gray-500">
                                    <div>Creado: ${sucursal.fecha_creacion_formatted}${sucursal.creado_por_nombre ? ` por ${sucursal.creado_por_nombre}` : ''}</div>
                                    <div>Actualizado: ${sucursal.fecha_actualizacion_formatted}${sucursal.actualizado_por_nombre ? ` por ${sucursal.actualizado_por_nombre}` : ''}</div>
                                </div>
                            </div>
                            
                            <div class="flex justify-end space-x-4 mt-6">
                                <button onclick="closeDetailsModal()" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                                    Cerrar
                                </button>
                                <button onclick="closeDetailsModal(); editSucursal(${sucursal.SucursalID})" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                    <i class="fas fa-edit mr-2"></i>Editar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            $('body').append(modal);
        }
        
        function closeDetailsModal() {
            $('.fixed.inset-0.modal-backdrop').last().remove();
        }
        
        function deleteSucursal(id, nombre) {
            if (confirm(`¿Estás seguro de que quieres eliminar la sucursal "${nombre}"?\n\nEsta acción no se puede deshacer.`)) {
                $.post('api/sucursalController/deleteSucursal.php', { sucursal_id: id })
                .done(function(response) {
                    if (response.success) {
                        if (response.action === 'deleted') {
                            showAlert('success', 'Sucursal eliminada', `La sucursal "${response.sucursal_name}" ha sido eliminada exitosamente.`);
                        } else {
                            showAlert('warning', 'Sucursal desactivada', 
                                `La sucursal "${response.sucursal_name}" ha sido desactivada porque tiene ${response.products_count} productos y ${response.movements_count} movimientos asociados.`);
                        }
                        loadSucursales();
                    } else {
                        showAlert('error', 'Error', response.message);
                    }
                })
                .fail(function() {
                    showAlert('error', 'Error', 'No se pudo eliminar la sucursal');
                });
            }
        }
        
        function submitSucursalForm() {
            const submitBtn = $('#submitBtn');
            const submitText = $('#submitText');
            const submitSpinner = $('#submitSpinner');
            
            if (!$('#nombre').val().trim()) {
                showAlert('error', 'Error', 'El nombre de la sucursal es obligatorio');
                $('#nombre').focus();
                return;
            }
            
            submitBtn.prop('disabled', true);
            submitSpinner.removeClass('hidden');
            submitText.text('Procesando...');
            
            const formData = $('#sucursalForm').serialize();
            const url = isEditing ? 'api/sucursalController/updateSucursal.php' : 'api/sucursalController/createSucursal.php';
            
            $.post(url, formData)
            .done(function(response) {
                if (response.success) {
                    const action = isEditing ? 'actualizada' : 'creada';
                    showAlert('success', 'Éxito', `Sucursal "${response.nombre}" ${action} exitosamente`);
                    closeModal();
                    loadSucursales();
                } else {
                    showAlert('error', 'Error', response.message);
                }
            })
            .fail(function() {
                showAlert('error', 'Error', 'No se pudo procesar la solicitud');
            })
            .always(function() {
                submitBtn.prop('disabled', false);
                submitSpinner.addClass('hidden');
                submitText.text(isEditing ? 'Actualizar Sucursal' : 'Crear Sucursal');
            });
        }
        
        function closeModal() {
            $('#sucursalModal').addClass('hidden').removeClass('fade-in');
        }
        
        function clearFilters() {
            $('#searchInput').val('');
            $('#estadoFilter').val('');
            $('#tipoFilter').val('');
            $('#sortBy').val('nombre');
            $('#sortOrder').val('ASC');
            currentPage = 1;
            loadSucursales();
        }
        
        function exportSucursales() {
            showAlert('info', 'Función en desarrollo', 'La exportación de sucursales se implementará próximamente');
        }
        
        function showLoadingState() {
            $('#loadingState').show();
            $('#sucursalesTableBody').hide();
        }
        
        function hideLoadingState() {
            $('#loadingState').hide();
            $('#sucursalesTableBody').show();
        }
        
        function showEmptyState() {
            $('#emptyState').removeClass('hidden');
        }
        
        function hideEmptyState() {
            $('#emptyState').addClass('hidden');
        }
        
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
        
        function showAlert(type, title, message) {
            const alertColors = {
                'success': 'bg-green-50 border-green-200 text-green-800',
                'error': 'bg-red-50 border-red-200 text-red-800',
                'warning': 'bg-yellow-50 border-yellow-200 text-yellow-800',
                'info': 'bg-blue-50 border-blue-200 text-blue-800'
            };
            
            const icons = {
                'success': 'fa-check-circle',
                'error': 'fa-exclamation-circle',
                'warning': 'fa-exclamation-triangle',
                'info': 'fa-info-circle'
            };
            
            const alert = $(`
                <div class="fixed top-4 right-4 z-50 max-w-md w-full ${alertColors[type]} border rounded-xl shadow-lg p-4 fade-in">
                    <div class="flex items-start">
                        <i class="fas ${icons[type]} text-lg mr-3 mt-0.5"></i>
                        <div class="flex-1">
                            <h4 class="font-bold">${title}</h4>
                            <p class="text-sm mt-1">${message}</p>
                        </div>
                        <button onclick="$(this).closest('div').remove()" class="ml-2 text-lg font-bold">×</button>
                    </div>
                </div>
            `);
            
            $('body').append(alert);
            setTimeout(() => alert.remove(), 5000);
        }
    </script>
</body>
</html>
