<?php
require_once "./controllers/mainController.php";

// Solo admin puede acceder
if ($_SESSION['id'] != 1) {
    header('Location: index.php?page=home');
    exit();
}
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<script src="https://cdn.tailwindcss.com"></script>
<style>
    .modal-backdrop {
        backdrop-filter: blur(4px);
    }
    .category-card {
        transition: all 0.3s ease;
    }
    .category-card:hover {
        transform: translateY(-4px);
    }
</style>
<div class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen">
    <!-- Header -->
    <div class="bg-white shadow-md border-b border-gray-200">
        <div class="container mx-auto px-4 py-6">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-layer-group text-white text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">Gestión de Categorías</h1>
                        <p class="text-gray-600 text-sm mt-1">Organiza tu catálogo de productos por categorías</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <button onclick="window.location.href='index.php?page=home'" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition flex items-center gap-2">
                        <i class="fas fa-arrow-left"></i>
                        <span class="hidden sm:inline">Volver</span>
                    </button>
                    <button onclick="openCreateModal()" class="px-6 py-2 bg-gradient-to-r from-blue-600 to-cyan-600 text-white rounded-lg hover:from-blue-700 hover:to-cyan-700 transition shadow-lg flex items-center gap-2">
                        <i class="fas fa-plus"></i>
                        <span>Nueva Categoría</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="container mx-auto px-4 py-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Total Categorías</p>
                        <p id="totalCategorias" class="text-3xl font-bold text-gray-800 mt-2">0</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-layer-group text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Total Productos</p>
                        <p id="totalProductos" class="text-3xl font-bold text-gray-800 mt-2">0</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-box text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Con Más Productos</p>
                        <p id="categoriaMayor" class="text-xl font-bold text-indigo-600 mt-2 truncate">-</p>
                    </div>
                    <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-crown text-indigo-600 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Promedio/Categoría</p>
                        <p id="promedio" class="text-3xl font-bold text-cyan-600 mt-2">0.0</p>
                    </div>
                    <div class="w-12 h-12 bg-cyan-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-chart-bar text-cyan-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search -->
        <div class="bg-white rounded-xl shadow p-4 mb-6">
            <div class="flex items-center gap-4 flex-wrap">
                <div class="flex-1 min-w-[200px] relative">
                    <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text" id="searchInput" placeholder="Buscar categorías..." class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                </div>
                <button onclick="refreshCategorias()" class="px-4 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition flex items-center gap-2">
                    <i class="fas fa-sync-alt"></i>
                    <span class="hidden sm:inline">Actualizar</span>
                </button>
                <select id="sortOrder" onchange="sortCategorias()" class="px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 transition">
                    <option value="nombre">Ordenar: A-Z</option>
                    <option value="nombreDesc">Ordenar: Z-A</option>
                    <option value="productos">Más productos</option>
                    <option value="productosAsc">Menos productos</option>
                </select>
            </div>
        </div>

        <!-- Categories Grid -->
        <div id="categoriasGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-6">
            <!-- Populated by JavaScript -->
        </div>

        <div id="emptyState" class="hidden bg-white rounded-xl shadow p-12 text-center">
            <i class="fas fa-layer-group text-gray-300 text-6xl mb-4"></i>
            <p class="text-gray-500 text-lg font-semibold">No se encontraron categorías</p>
            <p class="text-gray-400 text-sm mt-2">Crea tu primera categoría para comenzar</p>
        </div>

        <div id="loadingState" class="bg-white rounded-xl shadow p-12 text-center">
            <i class="fas fa-spinner fa-spin text-blue-600 text-4xl mb-4"></i>
            <p class="text-gray-600">Cargando categorías...</p>
        </div>
    </div>

    <!-- Modal Crear/Editar -->
    <div id="categoriaModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4 modal-backdrop">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full">
            <div class="bg-gradient-to-r from-blue-600 to-cyan-600 px-6 py-5 rounded-t-2xl">
                <div class="flex items-center justify-between">
                    <h2 id="modalTitle" class="text-2xl font-bold text-white flex items-center gap-2">
                        <i class="fas fa-layer-group"></i>
                        <span>Nueva Categoría</span>
                    </h2>
                    <button onclick="closeModal()" class="text-white hover:text-gray-200 transition">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>
            </div>
            <div class="p-6 space-y-5">
                <input type="hidden" id="categoriaId">
                
                <!-- Nombre -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        <i class="fas fa-font mr-1"></i>Nombre de la Categoría *
                    </label>
                    <input type="text" id="categoriaNombre" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" placeholder="Ej: Bebidas, Snacks, Lácteos..." maxlength="100">
                    <p class="text-xs text-gray-500 mt-1">Máximo 100 caracteres</p>
                </div>

                <div id="productosInfo" class="hidden bg-blue-50 rounded-lg p-4 border-2 border-blue-200">
                    <p class="text-sm font-bold text-blue-800 flex items-center gap-2">
                        <i class="fas fa-info-circle"></i>
                        Esta categoría tiene <span id="productosCount" class="font-black">0</span> producto(s) asociado(s)
                    </p>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 rounded-b-2xl flex justify-end gap-3 border-t border-gray-200">
                <button onclick="closeModal()" class="px-6 py-2.5 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition font-semibold">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </button>
                <button onclick="saveCategoria()" class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-cyan-600 text-white rounded-lg hover:from-blue-700 hover:to-cyan-700 transition font-semibold shadow-lg">
                    <i class="fas fa-save mr-2"></i>Guardar
                </button>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="hidden fixed bottom-4 right-4 bg-white rounded-lg shadow-2xl p-4 z-50 min-w-[300px] border-l-4">
        <div class="flex items-center gap-3">
            <i id="toastIcon" class="text-2xl"></i>
            <span id="toastMessage" class="font-semibold text-gray-800"></span>
        </div>
    </div>

    <script>
        let categorias = [];
        const API_URL = './v2/categorias/index.php';

        document.addEventListener('DOMContentLoaded', () => {
            loadCategorias();
            document.getElementById('searchInput').addEventListener('input', filterCategorias);
        });

        async function loadCategorias() {
            document.getElementById('loadingState').classList.remove('hidden');
            document.getElementById('emptyState').classList.add('hidden');
            
            try {
                const response = await fetch(`${API_URL}?withProductCount=true`);
                const data = await response.json();
                
                if (data.success) {
                    categorias = data.data.categorias;
                    renderCategorias(categorias);
                    updateStats();
                } else {
                    showToast(data.message, 'error');
                }
            } catch (error) {
                showToast('Error al cargar categorías: ' + error.message, 'error');
            } finally {
                document.getElementById('loadingState').classList.add('hidden');
            }
        }

        function renderCategorias(categoriasToRender) {
            const grid = document.getElementById('categoriasGrid');
            const emptyState = document.getElementById('emptyState');
            
            if (categoriasToRender.length === 0) {
                grid.innerHTML = '';
                emptyState.classList.remove('hidden');
                return;
            }
            
            emptyState.classList.add('hidden');
            
            const colors = ['blue', 'green', 'purple', 'pink', 'indigo', 'red', 'yellow', 'teal'];
            
            grid.innerHTML = categoriasToRender.map((cat, index) => {
                const color = colors[index % colors.length];
                return `
                    <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition category-card overflow-hidden border-2 border-gray-100">
                        <div class="bg-gradient-to-br from-${color}-400 to-${color}-600 p-6 text-white">
                            <div class="flex items-center justify-between mb-3">
                                <i class="fas fa-layer-group text-4xl opacity-80"></i>
                                <span class="text-3xl font-black">${cat.ProductosAsociados || 0}</span>
                            </div>
                            <h3 class="text-xl font-bold truncate">${cat.Nombre}</h3>
                            <p class="text-sm opacity-90 mt-1">productos</p>
                        </div>
                        <div class="p-6">
                            <div class="flex gap-2">
                                <button onclick="editCategoria(${cat.CategoriaID})" class="flex-1 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition font-semibold text-sm">
                                    <i class="fas fa-edit mr-1"></i>Editar
                                </button>
                                <button onclick="deleteCategoria(${cat.CategoriaID}, '${cat.Nombre.replace(/'/g, "\\'")}', ${cat.ProductosAsociados || 0})" class="flex-1 px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition font-semibold text-sm">
                                    <i class="fas fa-trash mr-1"></i>Eliminar
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        function updateStats() {
            document.getElementById('totalCategorias').textContent = categorias.length;
            
            const totalProductos = categorias.reduce((sum, c) => sum + parseInt(c.ProductosAsociados || 0), 0);
            document.getElementById('totalProductos').textContent = totalProductos;
            
            const mayor = categorias.reduce((max, c) => 
                (c.ProductosAsociados || 0) > (max.ProductosAsociados || 0) ? c : max
            , categorias[0] || {});
            document.getElementById('categoriaMayor').textContent = mayor.Nombre || '-';
            
            const promedio = categorias.length > 0 ? (totalProductos / categorias.length).toFixed(1) : '0.0';
            document.getElementById('promedio').textContent = promedio;
        }

        function filterCategorias() {
            const search = document.getElementById('searchInput').value.toLowerCase();
            const filtered = categorias.filter(cat => 
                cat.Nombre.toLowerCase().includes(search)
            );
            renderCategorias(filtered);
        }

        function sortCategorias() {
            const order = document.getElementById('sortOrder').value;
            const sorted = [...categorias];
            
            switch(order) {
                case 'nombre':
                    sorted.sort((a, b) => a.Nombre.localeCompare(b.Nombre));
                    break;
                case 'nombreDesc':
                    sorted.sort((a, b) => b.Nombre.localeCompare(a.Nombre));
                    break;
                case 'productos':
                    sorted.sort((a, b) => (b.ProductosAsociados || 0) - (a.ProductosAsociados || 0));
                    break;
                case 'productosAsc':
                    sorted.sort((a, b) => (a.ProductosAsociados || 0) - (b.ProductosAsociados || 0));
                    break;
            }
            
            renderCategorias(sorted);
        }

        function openCreateModal() {
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-layer-group"></i><span>Nueva Categoría</span>';
            document.getElementById('categoriaId').value = '';
            document.getElementById('categoriaNombre').value = '';
            document.getElementById('productosInfo').classList.add('hidden');
            document.getElementById('categoriaModal').classList.remove('hidden');
            document.getElementById('categoriaNombre').focus();
        }

        async function editCategoria(categoriaId) {
            const categoria = categorias.find(c => c.CategoriaID == categoriaId);
            if (!categoria) return;
            
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit"></i><span>Editar Categoría</span>';
            document.getElementById('categoriaId').value = categoria.CategoriaID;
            document.getElementById('categoriaNombre').value = categoria.Nombre;
            
            if (categoria.ProductosAsociados > 0) {
                document.getElementById('productosCount').textContent = categoria.ProductosAsociados;
                document.getElementById('productosInfo').classList.remove('hidden');
            } else {
                document.getElementById('productosInfo').classList.add('hidden');
            }
            
            document.getElementById('categoriaModal').classList.remove('hidden');
            document.getElementById('categoriaNombre').focus();
        }

        function closeModal() {
            document.getElementById('categoriaModal').classList.add('hidden');
        }

        async function saveCategoria() {
            const categoriaId = document.getElementById('categoriaId').value;
            const nombre = document.getElementById('categoriaNombre').value.trim();
            
            if (!nombre) {
                showToast('El nombre es requerido', 'warning');
                document.getElementById('categoriaNombre').focus();
                return;
            }
            
            if (nombre.length > 100) {
                showToast('El nombre no puede exceder 100 caracteres', 'warning');
                return;
            }
            
            const data = { Nombre: nombre };
            
            try {
                let response;
                if (categoriaId) {
                    data.CategoriaID = parseInt(categoriaId);
                    response = await fetch(API_URL, {
                        method: 'PUT',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify(data)
                    });
                } else {
                    response = await fetch(API_URL, {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify(data)
                    });
                }
                
                const result = await response.json();
                
                if (result.success) {
                    showToast(result.message, 'success');
                    closeModal();
                    loadCategorias();
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                showToast('Error al guardar categoría: ' + error.message, 'error');
            }
        }

        async function deleteCategoria(categoriaId, nombre, productosAsociados) {
            if (productosAsociados > 0) {
                const confirmMsg = `La categoría "${nombre}" tiene ${productosAsociados} producto(s) asociado(s).\n\n¿Deseas eliminarla de todas formas?\n\nLos productos quedarán sin categoría (NULL).`;
                
                if (!confirm(confirmMsg)) {
                    return;
                }
                
                // Force delete
                try {
                    const response = await fetch(`${API_URL}?id=${categoriaId}&force=true`, {
                        method: 'DELETE'
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        showToast(result.message, 'success');
                        loadCategorias();
                    } else {
                        showToast(result.message, 'error');
                    }
                } catch (error) {
                    showToast('Error al eliminar categoría: ' + error.message, 'error');
                }
            } else {
                if (!confirm(`¿Eliminar la categoría "${nombre}"?`)) {
                    return;
                }
                
                try {
                    const response = await fetch(`${API_URL}?id=${categoriaId}`, {
                        method: 'DELETE'
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        showToast(result.message, 'success');
                        loadCategorias();
                    } else {
                        showToast(result.message, 'error');
                    }
                } catch (error) {
                    showToast('Error al eliminar categoría: ' + error.message, 'error');
                }
            }
        }

        function refreshCategorias() {
            const refreshBtn = event.target.closest('button');
            const icon = refreshBtn.querySelector('i');
            icon.classList.add('fa-spin');
            
            loadCategorias().finally(() => {
                icon.classList.remove('fa-spin');
                showToast('Categorías actualizadas', 'success');
            });
        }

        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            const icon = document.getElementById('toastIcon');
            const msg = document.getElementById('toastMessage');
            
            const types = {
                success: { icon: 'fas fa-check-circle text-green-500', border: 'border-green-500' },
                error: { icon: 'fas fa-times-circle text-red-500', border: 'border-red-500' },
                warning: { icon: 'fas fa-exclamation-circle text-yellow-500', border: 'border-yellow-500' }
            };
            
            icon.className = types[type].icon + ' text-2xl';
            msg.textContent = message;
            toast.className = `fixed bottom-4 right-4 bg-white rounded-lg shadow-2xl p-4 z-50 min-w-[300px] border-l-4 ${types[type].border}`;
            toast.classList.remove('hidden');
            
            setTimeout(() => toast.classList.add('hidden'), 4000);
        }
    </script>
</div>
