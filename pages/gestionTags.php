<?php
require_once "./controllers/mainController.php";

// Solo admin puede acceder
if ($_SESSION['rol'] != 'Administrador') {
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
    .tag-badge {
        transition: transform 0.2s ease;
    }
    .tag-badge:hover {
        transform: scale(1.05);
    }
</style>
<div class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen">
    <!-- Header -->
    <div class="bg-white shadow-md border-b border-gray-200">
        <div class="container mx-auto px-4 py-6">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-tags text-white text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">Gestión de Tags</h1>
                        <p class="text-gray-600 text-sm mt-1">Organiza y categoriza tus productos con etiquetas</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <button onclick="window.location.href='index.php?page=home'" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition flex items-center gap-2">
                        <i class="fas fa-arrow-left"></i>
                        <span class="hidden sm:inline">Volver</span>
                    </button>
                    <button onclick="openCreateModal()" class="px-6 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-lg hover:from-purple-700 hover:to-indigo-700 transition shadow-lg flex items-center gap-2">
                        <i class="fas fa-plus"></i>
                        <span>Nuevo Tag</span>
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
                        <p class="text-gray-500 text-sm font-medium">Total Tags</p>
                        <p id="totalTags" class="text-3xl font-bold text-gray-800 mt-2">0</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-tags text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Productos Etiquetados</p>
                        <p id="totalProductos" class="text-3xl font-bold text-gray-800 mt-2">0</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-box text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Tags Activos</p>
                        <p id="tagsActivos" class="text-3xl font-bold text-green-600 mt-2">0</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Promedio/Producto</p>
                        <p id="promedio" class="text-3xl font-bold text-indigo-600 mt-2">0.0</p>
                    </div>
                    <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-chart-line text-indigo-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="bg-white rounded-xl shadow p-4 mb-6">
            <div class="flex items-center gap-4 flex-wrap">
                <div class="flex-1 min-w-[200px] relative">
                    <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text" id="searchInput" placeholder="Buscar tags por nombre..." class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                </div>
                <button onclick="refreshTags()" class="px-4 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition flex items-center gap-2">
                    <i class="fas fa-sync-alt"></i>
                    <span class="hidden sm:inline">Actualizar</span>
                </button>
            </div>
        </div>

        <!-- Tags Grid/Table -->
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <!-- Desktop Table View -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Tag</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Descripción</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Productos</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tagsTableBody" class="divide-y divide-gray-200">
                        <!-- Populated by JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div id="tagsCardView" class="md:hidden divide-y divide-gray-200">
                <!-- Populated by JavaScript -->
            </div>

            <div id="emptyState" class="hidden p-12 text-center">
                <i class="fas fa-tags text-gray-300 text-6xl mb-4"></i>
                <p class="text-gray-500 text-lg font-semibold">No se encontraron tags</p>
                <p class="text-gray-400 text-sm mt-2">Crea tu primer tag para comenzar</p>
            </div>

            <div id="loadingState" class="p-12 text-center">
                <i class="fas fa-spinner fa-spin text-purple-600 text-4xl mb-4"></i>
                <p class="text-gray-600">Cargando tags...</p>
            </div>
        </div>
    </div>

    <!-- Modal Crear/Editar -->
    <div id="tagModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4 modal-backdrop">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto animate__animated animate__fadeInUp">
            <div class="bg-gradient-to-r from-purple-600 to-indigo-600 px-6 py-5 rounded-t-2xl">
                <div class="flex items-center justify-between">
                    <h2 id="modalTitle" class="text-2xl font-bold text-white flex items-center gap-2">
                        <i class="fas fa-tag"></i>
                        <span>Nuevo Tag</span>
                    </h2>
                    <button onclick="closeModal()" class="text-white hover:text-gray-200 transition">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>
            </div>
            <div class="p-6 space-y-5">
                <input type="hidden" id="tagId">
                
                <!-- Nombre -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        <i class="fas fa-font mr-1"></i>Nombre del Tag *
                    </label>
                    <input type="text" id="tagNombre" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition" placeholder="Ej: Proveedores, Urgente, Refrigerado...">
                </div>

                <!-- Color -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        <i class="fas fa-palette mr-1"></i>Color
                    </label>
                    <div class="flex gap-3">
                        <input type="color" id="tagColor" class="w-20 h-12 rounded-lg border-2 border-gray-300 cursor-pointer">
                        <input type="text" id="tagColorHex" class="flex-1 px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition font-mono" placeholder="#6366f1">
                    </div>
                    <div class="mt-3 flex gap-2 flex-wrap">
                        <button type="button" onclick="selectColor('#3b82f6')" class="w-10 h-10 rounded-lg bg-blue-500 hover:ring-4 ring-blue-200 transition shadow"></button>
                        <button type="button" onclick="selectColor('#ef4444')" class="w-10 h-10 rounded-lg bg-red-500 hover:ring-4 ring-red-200 transition shadow"></button>
                        <button type="button" onclick="selectColor('#10b981')" class="w-10 h-10 rounded-lg bg-green-500 hover:ring-4 ring-green-200 transition shadow"></button>
                        <button type="button" onclick="selectColor('#f59e0b')" class="w-10 h-10 rounded-lg bg-yellow-500 hover:ring-4 ring-yellow-200 transition shadow"></button>
                        <button type="button" onclick="selectColor('#8b5cf6')" class="w-10 h-10 rounded-lg bg-purple-500 hover:ring-4 ring-purple-200 transition shadow"></button>
                        <button type="button" onclick="selectColor('#ec4899')" class="w-10 h-10 rounded-lg bg-pink-500 hover:ring-4 ring-pink-200 transition shadow"></button>
                        <button type="button" onclick="selectColor('#6366f1')" class="w-10 h-10 rounded-lg bg-indigo-500 hover:ring-4 ring-indigo-200 transition shadow"></button>
                        <button type="button" onclick="selectColor('#14b8a6')" class="w-10 h-10 rounded-lg bg-teal-500 hover:ring-4 ring-teal-200 transition shadow"></button>
                    </div>
                </div>

                <!-- Icono -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        <i class="fas fa-icons mr-1"></i>Icono (FontAwesome)
                    </label>
                    <div class="relative">
                        <i id="iconPreview" class="fas fa-tag absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 text-lg"></i>
                        <input type="text" id="tagIcono" class="w-full pl-12 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition" placeholder="fa-tag">
                    </div>
                    <p class="text-xs text-gray-500 mt-2 flex items-center gap-1">
                        <i class="fas fa-info-circle"></i>
                        Visita <a href="https://fontawesome.com/icons" target="_blank" class="text-purple-600 hover:underline font-semibold">FontAwesome</a> para ver iconos
                    </p>
                </div>

                <!-- Descripción -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        <i class="fas fa-align-left mr-1"></i>Descripción (Opcional)
                    </label>
                    <textarea id="tagDescripcion" rows="3" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition resize-none" placeholder="Descripción breve del tag y su uso..."></textarea>
                </div>

                <!-- Vista Previa -->
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg p-4 border-2 border-gray-200">
                    <p class="text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                        <i class="fas fa-eye"></i>Vista Previa
                    </p>
                    <div id="tagPreview" class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-white text-sm font-semibold shadow-lg">
                        <i class="fas fa-tag"></i>
                        <span>Nombre del Tag</span>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 rounded-b-2xl flex justify-end gap-3 border-t border-gray-200">
                <button onclick="closeModal()" class="px-6 py-2.5 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition font-semibold">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </button>
                <button onclick="saveTag()" class="px-6 py-2.5 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-lg hover:from-purple-700 hover:to-indigo-700 transition font-semibold shadow-lg">
                    <i class="fas fa-save mr-2"></i>Guardar Tag
                </button>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="hidden fixed bottom-4 right-4 bg-white rounded-lg shadow-2xl p-4 z-50 min-w-[300px] border-l-4">
        <div class="flex items-center gap-3">
            <i id="toastIcon" class="text-2xl"></i>
            <div class="flex-1">
                <span id="toastMessage" class="font-semibold text-gray-800 block"></span>
            </div>
        </div>
    </div>

    <script>
        let tags = [];
        const API_URL = './v2/tags/index.php';

        document.addEventListener('DOMContentLoaded', () => {
            loadTags();
            setupEventListeners();
        });

        function setupEventListeners() {
            document.getElementById('searchInput').addEventListener('input', filterTags);
            document.getElementById('tagNombre').addEventListener('input', updatePreview);
            document.getElementById('tagColor').addEventListener('input', (e) => {
                document.getElementById('tagColorHex').value = e.target.value;
                updatePreview();
            });
            document.getElementById('tagColorHex').addEventListener('input', (e) => {
                const color = e.target.value;
                if (/^#[0-9A-F]{6}$/i.test(color)) {
                    document.getElementById('tagColor').value = color;
                    updatePreview();
                }
            });
            document.getElementById('tagIcono').addEventListener('input', updatePreview);
        }

        async function loadTags() {
            document.getElementById('loadingState').classList.remove('hidden');
            document.getElementById('emptyState').classList.add('hidden');
            
            try {
                const response = await fetch(`${API_URL}?withProductCount=true`);
                const data = await response.json();
                
                if (data.success) {
                    tags = data.data.tags;
                    renderTags(tags);
                    updateStats();
                } else {
                    showToast(data.message, 'error');
                }
            } catch (error) {
                showToast('Error al cargar tags: ' + error.message, 'error');
            } finally {
                document.getElementById('loadingState').classList.add('hidden');
            }
        }

        function renderTags(tagsToRender) {
            const tbody = document.getElementById('tagsTableBody');
            const cardView = document.getElementById('tagsCardView');
            const emptyState = document.getElementById('emptyState');
            
            if (tagsToRender.length === 0) {
                tbody.innerHTML = '';
                cardView.innerHTML = '';
                emptyState.classList.remove('hidden');
                return;
            }
            
            emptyState.classList.add('hidden');
            
            // Desktop Table
            tbody.innerHTML = tagsToRender.map(tag => `
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-lg flex items-center justify-center text-white shadow-md" style="background-color: ${tag.Color}">
                                <i class="fas ${tag.Icono} text-lg"></i>
                            </div>
                            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-white text-sm font-semibold shadow tag-badge" style="background-color: ${tag.Color}">
                                <i class="fas ${tag.Icono}"></i>
                                ${tag.Nombre}
                            </span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-gray-600 text-sm max-w-xs">
                        ${tag.Descripcion || '<span class="text-gray-400 italic">Sin descripción</span>'}
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-100 text-blue-800 rounded-full text-sm font-bold">
                            <i class="fas fa-box"></i>
                            ${tag.ProductosAsociados || 0}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        ${tag.Activo == 1 
                            ? '<span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-100 text-green-800 rounded-full text-sm font-bold"><i class="fas fa-check-circle"></i> Activo</span>' 
                            : '<span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 text-gray-800 rounded-full text-sm font-bold"><i class="fas fa-times-circle"></i> Inactivo</span>'}
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <button onclick="editTag(${tag.TagID})" class="px-3 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition shadow" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteTag(${tag.TagID}, '${tag.Nombre.replace(/'/g, "\\'")}', ${tag.ProductosAsociados})" class="px-3 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition shadow" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');

            // Mobile Cards
            cardView.innerHTML = tagsToRender.map(tag => `
                <div class="p-4 hover:bg-gray-50 transition">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-center gap-3 flex-1">
                            <div class="w-12 h-12 rounded-lg flex items-center justify-center text-white shadow-md flex-shrink-0" style="background-color: ${tag.Color}">
                                <i class="fas ${tag.Icono} text-lg"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-white text-sm font-semibold shadow mb-2" style="background-color: ${tag.Color}">
                                    <i class="fas ${tag.Icono}"></i>
                                    ${tag.Nombre}
                                </span>
                                <p class="text-sm text-gray-600 mt-1">${tag.Descripcion || '<span class="italic text-gray-400">Sin descripción</span>'}</p>
                            </div>
                        </div>
                        <div class="flex flex-col gap-2 flex-shrink-0">
                            <button onclick="editTag(${tag.TagID})" class="px-3 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition shadow text-sm">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteTag(${tag.TagID}, '${tag.Nombre.replace(/'/g, "\\'")}', ${tag.ProductosAsociados})" class="px-3 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition shadow text-sm">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mt-3 flex items-center gap-4 text-sm">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-100 text-blue-800 rounded-full font-semibold">
                            <i class="fas fa-box"></i>
                            ${tag.ProductosAsociados || 0} productos
                        </span>
                        ${tag.Activo == 1 
                            ? '<span class="inline-flex items-center gap-1.5 px-3 py-1 bg-green-100 text-green-800 rounded-full font-semibold"><i class="fas fa-check-circle"></i> Activo</span>' 
                            : '<span class="inline-flex items-center gap-1.5 px-3 py-1 bg-gray-100 text-gray-800 rounded-full font-semibold"><i class="fas fa-times-circle"></i> Inactivo</span>'}
                    </div>
                </div>
            `).join('');
        }

        function updateStats() {
            document.getElementById('totalTags').textContent = tags.length;
            const activos = tags.filter(t => t.Activo == 1).length;
            document.getElementById('tagsActivos').textContent = activos;
            
            const totalProductos = tags.reduce((sum, t) => sum + parseInt(t.ProductosAsociados || 0), 0);
            document.getElementById('totalProductos').textContent = totalProductos;
            
            const promedio = tags.length > 0 ? (totalProductos / tags.length).toFixed(1) : '0.0';
            document.getElementById('promedio').textContent = promedio;
        }

        function filterTags() {
            const search = document.getElementById('searchInput').value.toLowerCase();
            const filtered = tags.filter(tag => 
                tag.Nombre.toLowerCase().includes(search) ||
                (tag.Descripcion && tag.Descripcion.toLowerCase().includes(search))
            );
            renderTags(filtered);
        }

        function openCreateModal() {
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-tag"></i><span>Nuevo Tag</span>';
            document.getElementById('tagId').value = '';
            document.getElementById('tagNombre').value = '';
            document.getElementById('tagColor').value = '#6366f1';
            document.getElementById('tagColorHex').value = '#6366f1';
            document.getElementById('tagIcono').value = 'fa-tag';
            document.getElementById('tagDescripcion').value = '';
            updatePreview();
            document.getElementById('tagModal').classList.remove('hidden');
        }

        async function editTag(tagId) {
            const tag = tags.find(t => t.TagID == tagId);
            if (!tag) return;
            
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit"></i><span>Editar Tag</span>';
            document.getElementById('tagId').value = tag.TagID;
            document.getElementById('tagNombre').value = tag.Nombre;
            document.getElementById('tagColor').value = tag.Color;
            document.getElementById('tagColorHex').value = tag.Color;
            document.getElementById('tagIcono').value = tag.Icono;
            document.getElementById('tagDescripcion').value = tag.Descripcion || '';
            updatePreview();
            document.getElementById('tagModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('tagModal').classList.add('hidden');
        }

        async function saveTag() {
            const tagId = document.getElementById('tagId').value;
            const nombre = document.getElementById('tagNombre').value.trim();
            const color = document.getElementById('tagColorHex').value;
            const icono = document.getElementById('tagIcono').value.trim();
            const descripcion = document.getElementById('tagDescripcion').value.trim();
            
            if (!nombre) {
                showToast('El nombre es requerido', 'warning');
                return;
            }
            
            const data = {
                Nombre: nombre,
                Color: color || '#6366f1',
                Icono: icono || 'fa-tag',
                Descripcion: descripcion,
                CreadoPor: <?php echo $_SESSION['id']; ?>
            };
            
            try {
                let response;
                if (tagId) {
                    data.TagID = parseInt(tagId);
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
                    loadTags();
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                showToast('Error al guardar tag: ' + error.message, 'error');
            }
        }

        async function deleteTag(tagId, nombre, productosAsociados) {
            const mensaje = productosAsociados > 0 
                ? `¿Eliminar el tag "${nombre}"?\n\nTiene ${productosAsociados} producto(s) asociado(s).\nSe desactivará pero se mantendrán las relaciones.`
                : `¿Eliminar el tag "${nombre}"?`;
                
            if (!confirm(mensaje)) {
                return;
            }
            
            try {
                const response = await fetch(`${API_URL}?id=${tagId}`, {
                    method: 'DELETE'
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast(result.message, 'success');
                    loadTags();
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                showToast('Error al eliminar tag: ' + error.message, 'error');
            }
        }

        function selectColor(color) {
            document.getElementById('tagColor').value = color;
            document.getElementById('tagColorHex').value = color;
            updatePreview();
        }

        function updatePreview() {
            const nombre = document.getElementById('tagNombre').value || 'Nombre del Tag';
            const color = document.getElementById('tagColorHex').value || '#6366f1';
            const icono = document.getElementById('tagIcono').value || 'fa-tag';
            
            const preview = document.getElementById('tagPreview');
            preview.style.backgroundColor = color;
            preview.innerHTML = `
                <i class="fas ${icono}"></i>
                <span>${nombre}</span>
            `;
            
            document.getElementById('iconPreview').className = `fas ${icono} absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 text-lg`;
        }

        function refreshTags() {
            const refreshBtn = event.target.closest('button');
            const icon = refreshBtn.querySelector('i');
            icon.classList.add('fa-spin');
            
            loadTags().finally(() => {
                icon.classList.remove('fa-spin');
            showToast('Tags actualizados', 'success');
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
        
        setTimeout(() => {
            toast.classList.add('hidden');
        }, 4000);
    }
</script>
</div>