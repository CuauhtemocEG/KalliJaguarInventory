<?php
require_once "./controllers/mainController.php";
require_once __DIR__ . '/../vendor/autoload.php';

use Picqer\Barcode\BarcodeGeneratorPNG;

// Solo admin puede acceder
if ($_SESSION['id'] != 1) {
    header('Location: index.php?page=home');
    exit();
}

// Funciones para códigos de barras
function generateValidEan13($code12)
{
    $sum = 0;
    for ($i = 0; $i < 12; $i++) {
        $digit = (int) $code12[$i];
        $sum += ($i % 2 === 0) ? $digit : $digit * 3;
    }
    $checkDigit = (10 - ($sum % 10)) % 10;
    return $code12 . $checkDigit;
}

function generarCodigoConLogo($ean13, $nombreProducto, $logoPath, $fontPath, $scale = 1.5)
{
    $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
    $barcodeData = $generator->getBarcode($ean13, $generator::TYPE_EAN_13, 2 * $scale, 45 * $scale);
    
    $barcodeImage = imagecreatefromstring($barcodeData);
    $barcodeWidth = imagesx($barcodeImage);
    $barcodeHeight = imagesy($barcodeImage);
    
    $logo = imagecreatefrompng($logoPath);
    $logoWidth = imagesx($logo);
    $logoHeight = imagesy($logo);
    
    $padding = 16;
    $maxLogoHeight = $barcodeHeight * 1.5;
    
    if ($logoHeight > $maxLogoHeight) {
        $ratio = $maxLogoHeight / $logoHeight;
        $newLogoWidth = (int)($logoWidth * $ratio);
        $newLogoHeight = (int)$maxLogoHeight;
        
        $resizedLogo = imagecreatetruecolor($newLogoWidth, $newLogoHeight);
        imagesavealpha($resizedLogo, true);
        $transColor = imagecolorallocatealpha($resizedLogo, 0, 0, 0, 127);
        imagefill($resizedLogo, 0, 0, $transColor);
        
        imagecopyresampled($resizedLogo, $logo, 0, 0, 0, 0, $newLogoWidth, $newLogoHeight, $logoWidth, $logoHeight);
        imagedestroy($logo);
        $logo = $resizedLogo;
        $logoWidth = $newLogoWidth;
        $logoHeight = $newLogoHeight;
    }
    
    $finalWidth = $barcodeWidth + $logoWidth + ($padding * 3);
    $textHeight = 30;
    $finalHeight = max($barcodeHeight, $logoHeight) + $textHeight + ($padding * 2) + 20;
    
    $finalImage = imagecreatetruecolor($finalWidth, $finalHeight);
    $white = imagecolorallocate($finalImage, 255, 255, 255);
    $black = imagecolorallocate($finalImage, 0, 0, 0);
    imagefilledrectangle($finalImage, 0, 0, $finalWidth, $finalHeight, $white);
    
    imagecopy($finalImage, $barcodeImage, $padding, $padding, 0, 0, $barcodeWidth, $barcodeHeight);
    
    $logoY = $padding + (int)(($barcodeHeight - $logoHeight) / 2);
    imagecopy($finalImage, $logo, $barcodeWidth + 2 * $padding, $logoY, 0, 0, $logoWidth, $logoHeight);
    
    $fontSize = 19;
    $textBox = imagettfbbox($fontSize, 0, $fontPath, $nombreProducto);
    $textWidth = $textBox[2] - $textBox[0];
    $textX = (int)(($finalWidth - $textWidth) / 2);
    $textY = max($barcodeHeight, $logoHeight) + $padding + 18;
    imagettftext($finalImage, $fontSize, 0, $textX, $textY, $black, $fontPath, $nombreProducto);
    
    $eanFontSize = 16;
    $textBox2 = imagettfbbox($eanFontSize, 0, $fontPath, $ean13);
    $textWidth2 = $textBox2[2] - $textBox2[0];
    $textX2 = (int)(($finalWidth - $textWidth2) / 2);
    $textY2 = $textY + 30;
    imagettftext($finalImage, $eanFontSize, 0, $textX2, $textY2, $black, $fontPath, $ean13);
    
    ob_start();
    imagepng($finalImage);
    $imgData = ob_get_clean();
    
    imagedestroy($finalImage);
    imagedestroy($barcodeImage);
    imagedestroy($logo);
    
    return base64_encode($imgData);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Productos - Kalli System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .modal-backdrop {
            backdrop-filter: blur(4px);
        }
        .product-card {
            transition: all 0.3s ease;
        }
        .product-card:hover {
            transform: translateY(-4px);
        }
        .tag-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            transition: all 0.2s;
        }
        .tag-chip:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen">
    <!-- Header -->
    <div class="bg-white shadow-md border-b border-gray-200">
        <div class="container mx-auto px-4 py-6">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-boxes text-white text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">Gestión de Productos</h1>
                        <p class="text-gray-600 text-sm mt-1">Administra tu inventario con códigos de barras</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <button onclick="window.location.href='../index.php?page=home'" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition flex items-center gap-2">
                        <i class="fas fa-arrow-left"></i>
                        <span class="hidden sm:inline">Volver</span>
                    </button>
                    <button onclick="openCreateModal()" class="px-6 py-2 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-lg hover:from-green-700 hover:to-emerald-700 transition shadow-lg flex items-center gap-2">
                        <i class="fas fa-plus"></i>
                        <span>Nuevo Producto</span>
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
                        <p class="text-gray-500 text-sm font-medium">Total Productos</p>
                        <p id="totalProductos" class="text-3xl font-bold text-gray-800 mt-2">0</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-boxes text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">En Stock</p>
                        <p id="enStock" class="text-3xl font-bold text-green-600 mt-2">0</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Stock Bajo</p>
                        <p id="stockBajo" class="text-3xl font-bold text-yellow-600 mt-2">0</p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-yellow-600 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Sin Stock</p>
                        <p id="sinStock" class="text-3xl font-bold text-red-600 mt-2">0</p>
                    </div>
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-times-circle text-red-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <div class="relative">
                    <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text" id="searchInput" placeholder="Buscar por nombre, UPC, SKU..." class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 transition text-sm">
                </div>
                <select id="filterCategoria" class="px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 transition text-sm">
                    <option value="">Todas las categorías</option>
                </select>
                <select id="filterTag" class="px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 transition text-sm">
                    <option value="">Todos los tags</option>
                </select>
                <select id="filterTipo" class="px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 transition text-sm">
                    <option value="">Todos los tipos</option>
                    <option value="Unidad">Contable</option>
                    <option value="Peso">Pesable</option>
                </select>
                <div class="flex gap-2">
                    <button onclick="applyFilters()" class="flex-1 px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm font-semibold flex items-center justify-center gap-2">
                        <i class="fas fa-filter"></i>
                        Filtrar
                    </button>
                    <button onclick="clearFilters()" class="px-4 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition" title="Limpiar filtros">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Products Grid -->
        <div id="productosGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
            <!-- Populated by JavaScript -->
        </div>

        <div id="emptyState" class="hidden bg-white rounded-xl shadow p-12 text-center">
            <i class="fas fa-boxes text-gray-300 text-6xl mb-4"></i>
            <p class="text-gray-500 text-lg font-semibold">No se encontraron productos</p>
            <p class="text-gray-400 text-sm mt-2">Ajusta los filtros o crea un nuevo producto</p>
        </div>

        <div id="loadingState" class="bg-white rounded-xl shadow p-12 text-center">
            <i class="fas fa-spinner fa-spin text-green-600 text-4xl mb-4"></i>
            <p class="text-gray-600">Cargando productos...</p>
        </div>

        <!-- Pagination -->
        <div id="paginationContainer" class="hidden bg-white rounded-xl shadow p-4 flex items-center justify-between flex-wrap gap-4">
            <div class="text-sm text-gray-600">
                Mostrando <span id="showingFrom" class="font-bold">0</span> a <span id="showingTo" class="font-bold">0</span> de <span id="totalCount" class="font-bold">0</span> productos
            </div>
            <div class="flex gap-2">
                <button onclick="previousPage()" id="btnPrevious" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <span id="currentPage" class="px-4 py-2 bg-green-100 text-green-800 rounded-lg font-bold">1</span>
                <button onclick="nextPage()" id="btnNext" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Crear/Editar Producto -->
    <div id="productoModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4 modal-backdrop overflow-y-auto">
        <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full my-8">
            <div class="bg-gradient-to-r from-green-600 to-emerald-600 px-6 py-5 rounded-t-2xl sticky top-0 z-10">
                <div class="flex items-center justify-between">
                    <h2 id="modalTitle" class="text-2xl font-bold text-white flex items-center gap-2">
                        <i class="fas fa-box"></i>
                        <span>Nuevo Producto</span>
                    </h2>
                    <button onclick="closeModal()" class="text-white hover:text-gray-200 transition">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>
            </div>
            <div class="p-6 space-y-5 max-h-[70vh] overflow-y-auto">
                <input type="hidden" id="productoId">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- Nombre -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fas fa-tag mr-1"></i>Nombre del Producto *
                        </label>
                        <input type="text" id="productoNombre" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 transition" placeholder="Nombre del producto">
                    </div>

                    <!-- UPC -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fas fa-barcode mr-1"></i>UPC/Código de Barras *
                        </label>
                        <input type="text" id="productoUPC" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 transition" placeholder="Código de 12-13 dígitos">
                    </div>

                    <!-- SKU -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fas fa-hashtag mr-1"></i>SKU (Opcional)
                        </label>
                        <input type="text" id="productoSKU" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 transition" placeholder="SKU interno">
                    </div>

                    <!-- Precio -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fas fa-dollar-sign mr-1"></i>Precio Unitario *
                        </label>
                        <input type="number" step="0.01" id="productoPrecio" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 transition" placeholder="0.00">
                    </div>

                    <!-- Cantidad -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fas fa-boxes mr-1"></i>Cantidad Inicial *
                        </label>
                        <input type="number" step="0.01" id="productoCantidad" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 transition" placeholder="0">
                    </div>

                    <!-- Stock Mínimo -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fas fa-exclamation-triangle mr-1"></i>Stock Mínimo *
                        </label>
                        <input type="number" step="0.01" id="productoStockMinimo" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 transition" placeholder="5">
                        <p class="text-xs text-gray-500 mt-1">Alerta cuando el stock esté por debajo de este valor</p>
                    </div>

                    <!-- Categoría -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fas fa-layer-group mr-1"></i>Categoría *
                        </label>
                        <select id="productoCategoria" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 transition">
                            <option value="">Seleccionar categoría</option>
                        </select>
                    </div>

                    <!-- Tipo -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fas fa-clipboard-check mr-1"></i>Tipo de Inventario *
                        </label>
                        <select id="productoTipo" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 transition">
                            <option value="Unidad">Contable (Unidades)</option>
                            <option value="Peso">Pesable (Kg)</option>
                        </select>
                    </div>

                    <!-- Tags (Múltiples) -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fas fa-tags mr-1"></i>Tags (Múltiples)
                        </label>
                        <div id="tagsContainer" class="flex flex-wrap gap-2 p-3 border-2 border-gray-300 rounded-lg min-h-[50px] focus-within:ring-2 focus-within:ring-green-500">
                            <!-- Tags selected will appear here -->
                        </div>
                        <select id="tagsSelector" class="w-full mt-2 px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 transition">
                            <option value="">Agregar tags...</option>
                        </select>
                    </div>

                    <!-- Imagen del Producto -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fas fa-image mr-1"></i>Imagen del Producto
                        </label>
                        <div class="flex items-center gap-4">
                            <input type="file" id="productoImagen" accept="image/*" class="flex-1 px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 transition">
                            <div id="imagenPreview" class="hidden">
                                <img id="imagenPreviewImg" src="" alt="Preview" class="w-24 h-24 object-cover rounded-lg border-2 border-gray-300 shadow-sm">
                            </div>
                        </div>
                        <input type="hidden" id="imagenActual" value="">
                        <p class="text-xs text-gray-500 mt-1">Formatos: JPG, PNG, GIF. Tamaño máx: 2MB</p>
                    </div>

                    <!-- Descripción -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            <i class="fas fa-align-left mr-1"></i>Descripción (Opcional)
                        </label>
                        <textarea id="productoDescripcion" rows="3" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 transition resize-none" placeholder="Descripción del producto..."></textarea>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 rounded-b-2xl flex justify-end gap-3 border-t border-gray-200 sticky bottom-0">
                <button onclick="closeModal()" class="px-6 py-2.5 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition font-semibold">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </button>
                <button onclick="saveProducto()" class="px-6 py-2.5 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-lg hover:from-green-700 hover:to-emerald-700 transition font-semibold shadow-lg">
                    <i class="fas fa-save mr-2"></i>Guardar Producto
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Código de Barras -->
    <div id="barcodeModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4 modal-backdrop">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full">
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-5 rounded-t-2xl">
                <div class="flex items-center justify-between">
                    <h2 class="text-2xl font-bold text-white flex items-center gap-2">
                        <i class="fas fa-barcode"></i>
                        <span id="barcodeProductName">Código de Barras</span>
                    </h2>
                    <button onclick="closeBarcodeModal()" class="text-white hover:text-gray-200 transition">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>
            </div>
            <div class="p-8 text-center">
                <div id="barcodeImageContainer" class="bg-gray-50 rounded-lg p-6 mb-4">
                    <!-- Barcode image will be inserted here -->
                </div>
                <button onclick="downloadBarcode()" class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg hover:from-indigo-700 hover:to-purple-700 transition font-semibold shadow-lg">
                    <i class="fas fa-download mr-2"></i>Descargar Código de Barras
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
        let productos = [];
        let categorias = [];
        let tags = [];
        let selectedTags = [];
        let currentPage = 1;
        let itemsPerPage = 12;
        let totalItems = 0;
        let currentBarcode = null;

        const API_URL = './v2/productos/index.php';
        const API_CATEGORIAS = './v2/categorias/index.php';
        const API_TAGS = './v2/tags/index.php';

        document.addEventListener('DOMContentLoaded', () => {
            loadCategorias();
            loadTags();
            loadProductos();
            setupEventListeners();
        });

        function setupEventListeners() {
            document.getElementById('tagsSelector').addEventListener('change', addTag);
            document.getElementById('searchInput').addEventListener('keyup', (e) => {
                if (e.key === 'Enter') applyFilters();
            });
            document.getElementById('productoImagen').addEventListener('change', previewImage);
        }

        async function loadCategorias() {
            try {
                const response = await fetch(API_CATEGORIAS);
                const data = await response.json();
                if (data.success) {
                    categorias = data.data.categorias;
                    const selectCat = document.getElementById('productoCategoria');
                    const filterCat = document.getElementById('filterCategoria');
                    
                    categorias.forEach(cat => {
                        selectCat.innerHTML += `<option value="${cat.CategoriaID}">${cat.Nombre}</option>`;
                        filterCat.innerHTML += `<option value="${cat.CategoriaID}">${cat.Nombre}</option>`;
                    });
                }
            } catch (error) {
                console.error('Error al cargar categorías:', error);
            }
        }

        async function loadTags() {
            try {
                const response = await fetch(API_TAGS);
                const data = await response.json();
                if (data.success) {
                    tags = data.data.tags.filter(t => t.Activo == 1);
                    const selectTag = document.getElementById('tagsSelector');
                    const filterTag = document.getElementById('filterTag');
                    
                    tags.forEach(tag => {
                        selectTag.innerHTML += `<option value="${tag.TagID}" data-color="${tag.Color}" data-icon="${tag.Icono}">${tag.Nombre}</option>`;
                        filterTag.innerHTML += `<option value="${tag.TagID}">${tag.Nombre}</option>`;
                    });
                }
            } catch (error) {
                console.error('Error al cargar tags:', error);
            }
        }

        async function loadProductos() {
            document.getElementById('loadingState').classList.remove('hidden');
            document.getElementById('emptyState').classList.add('hidden');
            document.getElementById('paginationContainer').classList.add('hidden');
            
            try {
                const offset = (currentPage - 1) * itemsPerPage;
                const response = await fetch(`${API_URL}?limit=${itemsPerPage}&offset=${offset}`);
                const data = await response.json();
                
                if (data.success) {
                    productos = data.data.productos;
                    totalItems = data.data.total;
                    renderProductos(productos);
                    updateStats(data.data.stats);
                    updatePagination();
                } else {
                    showToast(data.message, 'error');
                }
            } catch (error) {
                showToast('Error al cargar productos: ' + error.message, 'error');
            } finally {
                document.getElementById('loadingState').classList.add('hidden');
            }
        }

        function renderProductos(productosToRender) {
            const grid = document.getElementById('productosGrid');
            const emptyState = document.getElementById('emptyState');
            
            if (productosToRender.length === 0) {
                grid.innerHTML = '';
                emptyState.classList.remove('hidden');
                return;
            }
            
            emptyState.classList.add('hidden');
            
            grid.innerHTML = productosToRender.map(prod => {
                const stockMin = parseFloat(prod.StockMinimo) || 5;
                const stockStatus = getStockStatus(prod.Cantidad, stockMin);
                const stockBadge = getStockBadge(stockStatus);
                
                // Manejar tags con mayúscula o minúscula
                const productTags = prod.Tags || prod.tags || [];
                const tagsHtml = productTags.length > 0 ? productTags.map(tag => 
                    `<span class="tag-chip text-white shadow-sm" style="background-color: ${tag.Color}">
                        <i class="fas ${tag.Icono}"></i>
                        ${tag.Nombre}
                    </span>`
                ).join('') : '';
                
                // Imagen del producto
                const imagenUrl = prod.image ? `./img/producto/${prod.image}` : './img/producto.png';
                
                return `
                    <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition product-card overflow-hidden border-2 border-gray-100">
                        <!-- Imagen del producto -->
                        <div class="relative h-40 bg-gradient-to-br from-gray-100 to-gray-200 overflow-hidden">
                            <img src="${imagenUrl}" alt="${prod.Nombre}" class="w-full h-full object-cover" onerror="this.src='./img/producto.png'">
                            <div class="absolute top-2 right-2">
                                ${stockBadge}
                            </div>
                        </div>
                        
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-3">
                                <h3 class="text-lg font-bold text-gray-800 flex-1 line-clamp-2" title="${prod.Nombre}">${prod.Nombre}</h3>
                                ${stockBadge}
                            </div>
                            
                            <div class="space-y-2 text-sm text-gray-600 mb-4">
                                <p class="flex items-center gap-2">
                                    <i class="fas fa-barcode text-gray-400 w-4"></i>
                                    <span class="font-mono font-semibold">${prod.UPC}</span>
                                </p>
                                ${prod.SKU ? `<p class="flex items-center gap-2">
                                    <i class="fas fa-hashtag text-gray-400 w-4"></i>
                                    <span class="font-mono">${prod.SKU}</span>
                                </p>` : ''}
                                <p class="flex items-center gap-2">
                                    <i class="fas fa-dollar-sign text-gray-400 w-4"></i>
                                    <span class="font-bold text-green-600">$${parseFloat(prod.PrecioUnitario).toFixed(2)}</span>
                                </p>
                                <p class="flex items-center gap-2">
                                    <i class="fas fa-boxes text-gray-400 w-4"></i>
                                    <span><strong>${prod.Cantidad}</strong> ${prod.Tipo === 'Peso' ? 'Kg' : 'Uds'}</span>
                                </p>
                            </div>

                            ${tagsHtml ? `<div class="flex flex-wrap gap-1 mb-4">${tagsHtml}</div>` : ''}

                            <div class="flex gap-2 pt-4 border-t border-gray-200">
                                <button onclick="viewBarcode(${prod.ProductoID})" class="flex-1 px-3 py-2 bg-indigo-500 text-white rounded-lg hover:bg-indigo-600 transition font-semibold text-sm flex items-center justify-center gap-1" title="Ver código de barras">
                                    <i class="fas fa-barcode"></i>
                                </button>
                                <button onclick="editProducto(${prod.ProductoID})" class="flex-1 px-3 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition font-semibold text-sm flex items-center justify-center gap-1" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deleteProducto(${prod.ProductoID}, '${prod.Nombre.replace(/'/g, "\\'")}', ${prod.Cantidad})" class="flex-1 px-3 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition font-semibold text-sm flex items-center justify-center gap-1" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        function getStockStatus(cantidad, stockMinimo = 5) {
            if (cantidad <= 0) return 'sin_stock';
            if (cantidad <= stockMinimo) return 'bajo';
            return 'normal';
        }

        function getStockBadge(status) {
            const badges = {
                sin_stock: '<span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-bold flex items-center gap-1"><i class="fas fa-times-circle"></i> Sin stock</span>',
                bajo: '<span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-bold flex items-center gap-1"><i class="fas fa-exclamation-triangle"></i> Bajo</span>',
                normal: '<span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-bold flex items-center gap-1"><i class="fas fa-check-circle"></i> Stock OK</span>'
            };
            return badges[status] || '';
        }

        function updateStats(stats) {
            document.getElementById('totalProductos').textContent = stats.total;
            document.getElementById('enStock').textContent = stats.normal;
            document.getElementById('stockBajo').textContent = stats.bajo;
            document.getElementById('sinStock').textContent = stats.sin_stock;
        }

        function updatePagination() {
            const totalPages = Math.ceil(totalItems / itemsPerPage);
            const showingFrom = ((currentPage - 1) * itemsPerPage) + 1;
            const showingTo = Math.min(currentPage * itemsPerPage, totalItems);
            
            document.getElementById('showingFrom').textContent = showingFrom;
            document.getElementById('showingTo').textContent = showingTo;
            document.getElementById('totalCount').textContent = totalItems;
            document.getElementById('currentPage').textContent = currentPage;
            
            document.getElementById('btnPrevious').disabled = currentPage === 1;
            document.getElementById('btnNext').disabled = currentPage >= totalPages;
            
            if (totalItems > 0) {
                document.getElementById('paginationContainer').classList.remove('hidden');
            }
        }

        function previousPage() {
            if (currentPage > 1) {
                currentPage--;
                loadProductos();
            }
        }

        function nextPage() {
            const totalPages = Math.ceil(totalItems / itemsPerPage);
            if (currentPage < totalPages) {
                currentPage++;
                loadProductos();
            }
        }

        async function applyFilters() {
            const search = document.getElementById('searchInput').value.trim();
            const categoria = document.getElementById('filterCategoria').value;
            const tag = document.getElementById('filterTag').value;
            const tipo = document.getElementById('filterTipo').value;
            
            let url = `${API_URL}?limit=${itemsPerPage}&offset=0`;
            if (search) url += `&search=${encodeURIComponent(search)}`;
            if (categoria) url += `&categoria=${categoria}`;
            if (tag) url += `&tag=${tag}`;
            if (tipo) url += `&tipo=${encodeURIComponent(tipo)}`;
            
            currentPage = 1;
            
            try {
                const response = await fetch(url);
                const data = await response.json();
                
                if (data.success) {
                    productos = data.data.productos;
                    totalItems = data.data.total;
                    renderProductos(productos);
                    updateStats(data.data.stats);
                    updatePagination();
                }
            } catch (error) {
                showToast('Error al aplicar filtros: ' + error.message, 'error');
            }
        }

        function clearFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('filterCategoria').value = '';
            document.getElementById('filterTag').value = '';
            document.getElementById('filterTipo').value = '';
            currentPage = 1;
            loadProductos();
        }

        function addTag() {
            const selector = document.getElementById('tagsSelector');
            const tagId = parseInt(selector.value);
            
            if (!tagId) return;
            
            const tag = tags.find(t => t.TagID === tagId);
            if (!tag || selectedTags.find(t => t.TagID === tagId)) {
                selector.value = '';
                return;
            }
            
            selectedTags.push(tag);
            renderSelectedTags();
            selector.value = '';
        }

        function removeTag(tagId) {
            selectedTags = selectedTags.filter(t => t.TagID !== tagId);
            renderSelectedTags();
        }

        function renderSelectedTags() {
            const container = document.getElementById('tagsContainer');
            if (selectedTags.length === 0) {
                container.innerHTML = '<span class="text-gray-400 text-sm">No hay tags seleccionados</span>';
                return;
            }
            
            container.innerHTML = selectedTags.map(tag => 
                `<span class="tag-chip text-white shadow-sm cursor-pointer" style="background-color: ${tag.Color}" onclick="removeTag(${tag.TagID})">
                    <i class="fas ${tag.Icono}"></i>
                    ${tag.Nombre}
                    <i class="fas fa-times ml-1"></i>
                </span>`
            ).join('');
        }

        function openCreateModal() {
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-box"></i><span>Nuevo Producto</span>';
            document.getElementById('productoId').value = '';
            document.getElementById('productoNombre').value = '';
            document.getElementById('productoUPC').value = '';
            document.getElementById('productoSKU').value = '';
            document.getElementById('productoPrecio').value = '';
            document.getElementById('productoCantidad').value = '0';
            document.getElementById('productoStockMinimo').value = '5';
            document.getElementById('productoCategoria').value = '';
            document.getElementById('productoTipo').value = 'Unidad';
            document.getElementById('productoDescripcion').value = '';
            document.getElementById('productoImagen').value = '';
            document.getElementById('imagenActual').value = '';
            document.getElementById('imagenPreview').classList.add('hidden');
            selectedTags = [];
            renderSelectedTags();
            document.getElementById('productoModal').classList.remove('hidden');
            document.getElementById('productoNombre').focus();
        }

        async function editProducto(productoId) {
            const producto = productos.find(p => p.ProductoID == productoId);
            if (!producto) {
                showToast('Producto no encontrado', 'error');
                return;
            }
            
            console.log('Producto a editar:', producto); // Debug
            
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit"></i><span>Editar Producto</span>';
            document.getElementById('productoId').value = producto.ProductoID;
            document.getElementById('productoNombre').value = producto.Nombre || '';
            document.getElementById('productoUPC').value = producto.UPC || '';
            document.getElementById('productoSKU').value = producto.SKU || '';
            document.getElementById('productoPrecio').value = producto.PrecioUnitario || '';
            document.getElementById('productoCantidad').value = producto.Cantidad || '0';
            document.getElementById('productoStockMinimo').value = producto.StockMinimo || '5';
            document.getElementById('productoCategoria').value = producto.CategoriaID || '';
            document.getElementById('productoTipo').value = producto.Tipo || 'Unidad';
            document.getElementById('productoDescripcion').value = producto.Descripcion || '';
            
            // Cargar imagen actual si existe
            if (producto.image) {
                document.getElementById('imagenActual').value = producto.image;
                document.getElementById('imagenPreview').classList.remove('hidden');
                document.getElementById('imagenPreviewImg').src = `./img/producto/${producto.image}`;
            } else {
                document.getElementById('imagenActual').value = '';
                document.getElementById('imagenPreview').classList.add('hidden');
            }
            
            // Cargar tags del producto - puede venir como 'tags' o 'Tags'
            const productTags = producto.Tags || producto.tags || [];
            console.log('Tags del producto:', productTags); // Debug
            
            // Convertir los tags del producto a objetos completos
            selectedTags = [];
            if (Array.isArray(productTags)) {
                productTags.forEach(tagData => {
                    // Si tagData es un objeto con TagID, buscar el tag completo
                    const tagId = tagData.TagID || tagData.tagId || tagData;
                    const fullTag = tags.find(t => t.TagID == tagId);
                    if (fullTag) {
                        selectedTags.push(fullTag);
                    }
                });
            }
            
            console.log('Tags seleccionados:', selectedTags); // Debug
            renderSelectedTags();
            
            document.getElementById('productoModal').classList.remove('hidden');
            document.getElementById('productoNombre').focus();
        }

        function closeModal() {
            document.getElementById('productoModal').classList.add('hidden');
        }

        async function saveProducto() {
            const productoId = document.getElementById('productoId').value;
            const nombre = document.getElementById('productoNombre').value.trim();
            const upc = document.getElementById('productoUPC').value.trim();
            const sku = document.getElementById('productoSKU').value.trim();
            const precio = document.getElementById('productoPrecio').value;
            const cantidad = document.getElementById('productoCantidad').value;
            const stockMinimo = document.getElementById('productoStockMinimo').value;
            const categoriaId = document.getElementById('productoCategoria').value;
            const tipo = document.getElementById('productoTipo').value;
            const descripcion = document.getElementById('productoDescripcion').value.trim();
            const imagenInput = document.getElementById('productoImagen');
            const imagenActual = document.getElementById('imagenActual').value;
            
            if (!nombre || !upc || !precio || !categoriaId) {
                showToast('Complete los campos requeridos (Nombre, UPC, Precio, Categoría)', 'warning');
                return;
            }
            
            try {
                let imageName = imagenActual; // Por defecto usa la imagen actual
                
                // Si hay una nueva imagen, subirla primero
                if (imagenInput.files && imagenInput.files[0]) {
                    const formData = new FormData();
                    formData.append('imagen', imagenInput.files[0]);
                    formData.append('nombre', nombre);
                    
                    const uploadResponse = await fetch('./api/uploadProductImage.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const uploadResult = await uploadResponse.json();
                    
                    if (!uploadResult.success) {
                        showToast(uploadResult.message, 'error');
                        return;
                    }
                    
                    imageName = uploadResult.data.filename;
                }
                
                // Preparar datos del producto
                const data = {
                    Nombre: nombre,
                    UPC: upc,
                    SKU: sku || null,
                    PrecioUnitario: parseFloat(precio),
                    Cantidad: parseFloat(cantidad),
                    StockMinimo: parseFloat(stockMinimo) || 5,
                    CategoriaID: parseInt(categoriaId),
                    Tipo: tipo,
                    Descripcion: descripcion || null,
                    image: imageName || null,
                    tags: selectedTags.map(t => t.TagID),
                    UsuarioID: <?php echo $_SESSION['id']; ?>
                };
                
                let response;
                if (productoId) {
                    data.ProductoID = parseInt(productoId);
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
                    loadProductos();
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                showToast('Error al guardar producto: ' + error.message, 'error');
            }
        }

        async function deleteProducto(productoId, nombre, cantidad) {
            const confirmMsg = cantidad > 0 
                ? `¿Eliminar el producto "${nombre}"?\n\nAún tiene ${cantidad} unidades en stock.`
                : `¿Eliminar el producto "${nombre}"?`;
                
            if (!confirm(confirmMsg)) return;
            
            try {
                const response = await fetch(`${API_URL}?id=${productoId}`, {
                    method: 'DELETE'
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast(result.message, 'success');
                    loadProductos();
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                showToast('Error al eliminar producto: ' + error.message, 'error');
            }
        }

        async function viewBarcode(productoId) {
        const producto = productos.find(p => p.ProductoID == productoId);
        if (!producto) return;
        
        document.getElementById('barcodeProductName').textContent = producto.Nombre;
        
        // Generate barcode using PHP endpoint con timestamp para evitar cache
        const timestamp = new Date().getTime();
        const barcodeUrl = `./v2/generateBarcode.php?productoId=${productoId}&t=${timestamp}`;
        
        const container = document.getElementById('barcodeImageContainer');
        container.innerHTML = `<div class="flex items-center justify-center p-4"><i class="fas fa-spinner fa-spin text-indigo-600 text-3xl"></i></div>`;
        
        // Crear imagen y esperar a que cargue
        const img = new Image();
        img.onload = function() {
            container.innerHTML = `<img src="${barcodeUrl}" alt="Código de Barras" class="mx-auto shadow-lg rounded-lg" style="max-width:100%;" id="barcodeImage" />`;
        };
        img.onerror = function() {
            container.innerHTML = `<div class="text-red-500"><i class="fas fa-exclamation-triangle mr-2"></i>Error al cargar código de barras</div>`;
        };
        img.src = barcodeUrl;
        
        currentBarcode = {
            id: productoId,
            nombre: producto.Nombre,
            url: barcodeUrl
        };
        
        document.getElementById('barcodeModal').classList.remove('hidden');
        }

        function closeBarcodeModal() {
            document.getElementById('barcodeModal').classList.add('hidden');
            currentBarcode = null;
        }

        function downloadBarcode() {
            if (!currentBarcode) return;
            
            // Crear un canvas para convertir la imagen
            const img = document.getElementById('barcodeImage');
            if (!img) {
                showToast('No se pudo obtener la imagen del código de barras', 'error');
                return;
            }
            
            // Usar fetch para descargar como blob
            fetch(currentBarcode.url)
                .then(response => response.blob())
                .then(blob => {
                    const url = window.URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = `barcode_${currentBarcode.id}_${currentBarcode.nombre.replace(/\s+/g, '_')}.png`;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    window.URL.revokeObjectURL(url);
                    showToast('Código de barras descargado', 'success');
                })
                .catch(error => {
                    showToast('Error al descargar código de barras', 'error');
                    console.error('Error:', error);
                });
        }

        function previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                // Validar tamaño (2MB máximo)
                if (file.size > 2 * 1024 * 1024) {
                    showToast('La imagen es demasiado grande. Máximo 2MB', 'warning');
                    event.target.value = '';
                    return;
                }
                
                // Validar tipo
                if (!file.type.match('image.*')) {
                    showToast('Solo se permiten archivos de imagen', 'warning');
                    event.target.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('imagenPreviewImg').src = e.target.result;
                    document.getElementById('imagenPreview').classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }
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
</body>
</html>
