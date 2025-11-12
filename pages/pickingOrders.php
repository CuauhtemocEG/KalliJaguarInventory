<?php
require_once "./controllers/mainController.php";

if (!isset($_SESSION["id"])) {
    header("Location: index.php?page=login");
    exit;
}

$userID = $_SESSION["id"];
$userName = $_SESSION["nombre"];

$conn = conexion();
$query = $conn->prepare("
    SELECT DISTINCT 
        mi.ComandaID,
        mi.FechaMovimiento,
        mi.FechaDelivery,
        mi.SucursalID,
        s.nombre AS SucursalNombre,
        u.Nombre AS Solicitante,
        COUNT(DISTINCT mi.ProductoID) as TotalProductos,
        SUM(mi.Cantidad) as TotalCantidad,
        mi.Status
    FROM MovimientosInventario mi
    JOIN Sucursales s ON mi.SucursalID = s.SucursalID  
    JOIN Usuarios u ON mi.UsuarioID = u.UsuarioID
    WHERE mi.TipoMovimiento = 'Salida' 
    AND mi.Status = 'Abierto'
    GROUP BY mi.ComandaID, mi.FechaMovimiento, mi.FechaDelivery, mi.SucursalID, s.nombre, u.Nombre, mi.Status
    ORDER BY mi.FechaMovimiento DESC
");
$query->execute();
$ordenes = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="min-h-screen bg-white dark:bg-gray-800">
    <div class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-6">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-gradient-to-r from-orange-500 to-red-600 rounded-xl flex items-center justify-center shadow-lg">
                                <i class="fas fa-clipboard-list text-white text-xl"></i>
                            </div>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                                Picking de Órdenes
                            </h1>
                            <p class="text-gray-600 dark:text-gray-400">
                                Gestiona el picking de órdenes abiertas con scanner de códigos
                            </p>
                        </div>
                    </div>
                    
                    <div class="mt-4 lg:mt-0 flex items-center space-x-3">
                        <div class="bg-orange-50 dark:bg-orange-900/20 px-4 py-2 rounded-lg">
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-boxes text-orange-600 dark:text-orange-400"></i>
                                <span class="text-sm font-medium text-orange-600 dark:text-orange-400">
                                    <?php echo count($ordenes); ?> órdenes abiertas
                                </span>
                            </div>
                        </div>
                        
                        <button onclick="refreshOrders()" 
                                class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg transition-colors duration-200">
                            <i class="fas fa-sync-alt mr-2"></i>
                            Actualizar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <?php if (empty($ordenes)): ?>
            <div class="text-center py-12">
                <div class="w-24 h-24 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-clipboard-list text-gray-400 text-3xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                    No hay órdenes abiertas
                </h3>
                <p class="text-gray-500 dark:text-gray-400 mb-6">
                    Todas las órdenes han sido procesadas o no hay órdenes pendientes en este momento.
                </p>
                <button onclick="refreshOrders()" 
                        class="inline-flex items-center px-6 py-3 bg-orange-600 hover:bg-orange-700 text-white font-medium rounded-lg transition-colors duration-200">
                    <i class="fas fa-sync-alt mr-2"></i>
                    Verificar nuevamente
                </button>
            </div>
        <?php else: ?>
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <?php foreach ($ordenes as $orden): ?>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow duration-200">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-file-alt text-white"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                            <?php echo htmlspecialchars($orden['ComandaID']); ?>
                                        </h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            <?php echo date('d/m/Y H:i', strtotime($orden['FechaMovimiento'])); ?>
                                        </p>
                                    </div>
                                </div>
                                
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400">
                                    <i class="fas fa-circle text-xs mr-1"></i>
                                    Abierto
                                </span>
                            </div>
                        </div>
                        
                        <div class="p-6 space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Productos
                                    </p>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-white">
                                        <?php echo $orden['TotalProductos']; ?>
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Cantidad Total
                                    </p>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-white">
                                        <?php echo number_format($orden['TotalCantidad'], 2); ?>
                                    </p>
                                </div>
                            </div>
                            
                            <div>
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">
                                    Sucursal Destino
                                </p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    <i class="fas fa-store text-blue-500 mr-2"></i>
                                    <?php echo htmlspecialchars($orden['SucursalNombre']); ?>
                                </p>
                            </div>
                            
                            <div>
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">
                                    Solicitante
                                </p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    <i class="fas fa-user text-green-500 mr-2"></i>
                                    <?php echo htmlspecialchars($orden['Solicitante']); ?>
                                </p>
                            </div>
                            
                            <?php if ($orden['FechaDelivery']): ?>
                            <div>
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">
                                    Fecha de Entrega
                                </p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    <i class="fas fa-calendar text-orange-500 mr-2"></i>
                                    <?php echo date('d/m/Y', strtotime($orden['FechaDelivery'])); ?>
                                </p>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="px-6 py-4 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
                            <div class="flex space-x-3">
                                <button onclick="startPicking('<?php echo $orden['ComandaID']; ?>')"
                                        class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-700 hover:to-red-700 text-white font-medium rounded-lg transition-all duration-200 transform hover:scale-105">
                                    <i class="fas fa-barcode mr-2"></i>
                                    Comenzar Picking
                                </button>
                                
                                <button onclick="viewOrderDetails('<?php echo $orden['ComandaID']; ?>')"
                                        class="inline-flex items-center justify-center px-4 py-2 bg-gray-100 dark:bg-gray-600 hover:bg-gray-200 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-300 rounded-lg transition-colors duration-200">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<div id="pickingModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-hidden">
            <div class="bg-gradient-to-r from-orange-600 to-red-600 px-6 py-4 text-white">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-barcode text-2xl"></i>
                        <div>
                            <h3 class="text-xl font-bold">Picking de Orden</h3>
                            <p class="text-orange-100" id="modalOrderId">COM-20250915-6-699</p>
                        </div>
                    </div>
                    <button onclick="closePicking()" class="text-white hover:text-orange-200 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            
            <div class="flex flex-col lg:flex-row h-[70vh]">
                <div class="flex-1 p-6 overflow-y-auto border-r border-gray-200 dark:border-gray-700">
                    <div class="mb-4">
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                            Productos a Pickear
                        </h4>
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            <span id="pickedCount">0</span> de <span id="totalCount">0</span> productos completados
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 mt-2">
                            <div id="progressBar" class="bg-gradient-to-r from-orange-500 to-red-500 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                        </div>
                    </div>
                    
                    <div id="productsList" class="space-y-3">
                    </div>
                </div>
                
                <div class="w-full lg:w-96 p-6 bg-white dark:bg-gray-800">
                    <div class="mb-6">
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                            Scanner de Códigos
                        </h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Escanea o ingresa el código de barras del producto
                        </p>
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Código de Barras
                        </label>
                        <div class="relative">
                            <input type="text" 
                                   id="barcodeInput" 
                                   class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500" 
                                   placeholder="Escanea o ingresa el código..."
                                   autocomplete="off">
                            <div class="absolute right-3 top-3">
                                <i class="fas fa-barcode text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div id="currentProduct" class="hidden">
                        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-600 mb-4">
                            <h5 class="font-semibold text-gray-900 dark:text-white mb-2" id="currentProductName">
                                Producto Encontrado
                            </h5>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Tipo:</span>
                                    <span id="currentProductType" class="font-medium text-gray-900 dark:text-white">Unidad</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Cantidad Solicitada:</span>
                                    <span id="currentProductQty" class="font-medium text-gray-900 dark:text-white">2</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Cantidad Escaneada:</span>
                                    <span id="currentProductScanned" class="font-medium text-orange-600">0</span>
                                </div>
                            </div>
                        </div>
                        
                        <div id="weightInput" class="hidden mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Cantidad Pesada (kg)
                            </label>
                            <input type="number" 
                                   id="weightAmount" 
                                   step="0.001" 
                                   min="0"
                                   class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-orange-500 focus:border-orange-500" 
                                   placeholder="0.000">
                        </div>
                        
                        <button onclick="confirmScan()" 
                                id="confirmBtn"
                                class="w-full py-3 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-medium rounded-lg transition-all duration-200">
                            <i class="fas fa-check mr-2"></i>
                            Confirmar
                        </button>
                    </div>
                    
                    <div class="mt-6 p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-600">
                        <h5 class="font-semibold text-gray-900 dark:text-white mb-2">Estado del Picking</h5>
                        <div class="space-y-2 text-sm">
                            <div class="flex items-center text-green-600">
                                <i class="fas fa-check-circle mr-2"></i>
                                <span id="completedItems">0 productos completados</span>
                            </div>
                            <div class="flex items-center text-orange-600">
                                <i class="fas fa-clock mr-2"></i>
                                <span id="pendingItems">0 productos pendientes</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="px-6 py-4 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
                <div class="flex justify-between items-center">
                    <button onclick="closePicking()" 
                            class="px-6 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition-colors duration-200">
                        <i class="fas fa-times mr-2"></i>
                        Cancelar
                    </button>
                    
                    <button onclick="completePicking()" 
                            id="completeBtn"
                            disabled
                            class="px-6 py-2 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white rounded-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-clipboard-check mr-2"></i>
                        Completar Picking
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentOrderId = '';
let orderProducts = [];
let pickedProducts = {};

function refreshOrders() {
    location.reload();
}

function viewOrderDetails(orderId) {
    window.open(`index.php?page=showPDF&ComandaID=${orderId}`);
}

async function startPicking(orderId) {
    currentOrderId = orderId;
    document.getElementById('modalOrderId').textContent = orderId;
    
    try {
        document.getElementById('pickingModal').classList.remove('hidden');
        document.getElementById('productsList').innerHTML = `
            <div class="flex items-center justify-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-orange-600"></div>
                <span class="ml-3 text-gray-600 dark:text-gray-400">Cargando productos...</span>
            </div>
        `;
        
        const response = await fetch(`api/picking/getOrderProducts.php?orderId=${orderId}`);
        const data = await response.json();
        
        if (data.success) {
            orderProducts = data.products;
            pickedProducts = {};
            renderProductsList();
            updateProgress();
            
            document.getElementById('barcodeInput').focus();
        } else {
            throw new Error(data.message || 'Error al cargar productos');
        }
        
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            title: 'Error',
            text: 'No se pudieron cargar los productos de la orden',
            icon: 'error'
        });
        closePicking();
    }
}

function renderProductsList() {
    const container = document.getElementById('productsList');
    
    if (orderProducts.length === 0) {
        container.innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-box-open text-gray-400 text-3xl mb-2"></i>
                <p class="text-gray-600 dark:text-gray-400">No hay productos en esta orden</p>
            </div>
        `;
        return;
    }
    
    const html = orderProducts.map(product => {
        const picked = pickedProducts[product.UPC] || { scanned: 0, completed: false };
        const isCompleted = picked.completed;
        const progress = product.Tipo === 'Pesable' ? 
            (picked.scanned > 0 ? 100 : 0) : 
            Math.min((picked.scanned / product.Cantidad) * 100, 100);
        
        return `
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-600 ${isCompleted ? 'ring-2 ring-green-500' : ''}">
                <div class="flex items-start space-x-4 mb-3">
                    <div class="flex-shrink-0">
                        <img src="${product.Image ? 'img/producto/' + product.Image : 'img/producto.png'}" 
                             alt="${product.Nombre}"
                             class="w-20 h-20 object-cover rounded-lg border-2 border-gray-200 dark:border-gray-600"
                             onerror="this.src='img/producto.png'">
                    </div>
                    <div class="flex-1 min-w-0">
                        <h5 class="font-semibold text-gray-900 dark:text-white mb-1">${product.Nombre}</h5>
                        <p class="text-sm text-gray-600 dark:text-gray-400">UPC: ${product.UPC}</p>
                        ${product.Descripcion ? `<p class="text-xs text-gray-500 mt-1 line-clamp-2">${product.Descripcion}</p>` : ''}
                    </div>
                    <div class="flex-shrink-0">
                        ${isCompleted ? 
                            '<i class="fas fa-check-circle text-green-500 text-2xl"></i>' : 
                            '<i class="fas fa-clock text-orange-500 text-2xl"></i>'
                        }
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4 text-sm mb-3">
                    <div>
                        <span class="text-gray-600 dark:text-gray-400">Tipo:</span>
                        <span class="ml-1 font-medium">${product.Tipo}</span>
                    </div>
                    <div>
                        <span class="text-gray-600 dark:text-gray-400">Solicitado:</span>
                        <span class="ml-1 font-medium">${product.Tipo === 'Pesable' ? 
                            parseFloat(product.Cantidad).toFixed(3) + ' kg' : 
                            Math.round(product.Cantidad) + ' un.'
                        }</span>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-600 dark:text-gray-400">Progreso:</span>
                        <span class="font-medium">${Math.round(progress)}%</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="bg-gradient-to-r from-orange-500 to-red-500 h-2 rounded-full transition-all duration-300" 
                             style="width: ${progress}%"></div>
                    </div>
                </div>
                
                <div class="text-sm">
                    <span class="text-gray-600 dark:text-gray-400">
                        ${product.Tipo === 'Pesable' ? 
                            `Cantidad: ${picked.scanned > 0 ? parseFloat(picked.scanned).toFixed(3) + ' kg' : 'Pendiente'}` :
                            `Escaneado: ${picked.scanned} de ${Math.round(product.Cantidad)}`
                        }
                    </span>
                </div>
            </div>
        `;
    }).join('');
    
    container.innerHTML = html;
    updateCounts();
}

function updateProgress() {
    const completed = Object.values(pickedProducts).filter(p => p.completed).length;
    const total = orderProducts.length;
    const percentage = total > 0 ? (completed / total) * 100 : 0;
    
    document.getElementById('pickedCount').textContent = completed;
    document.getElementById('totalCount').textContent = total;
    document.getElementById('progressBar').style.width = percentage + '%';
    
    const completeBtn = document.getElementById('completeBtn');
    completeBtn.disabled = completed < total;
}

function updateCounts() {
    const completed = Object.values(pickedProducts).filter(p => p.completed).length;
    const pending = orderProducts.length - completed;
    
    document.getElementById('completedItems').textContent = `${completed} productos completados`;
    document.getElementById('pendingItems').textContent = `${pending} productos pendientes`;
}

document.getElementById('barcodeInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        processScan();
    }
});

document.getElementById('barcodeInput').addEventListener('input', function(e) {
    const barcode = e.target.value.trim();
    if (barcode.length > 8) {
        setTimeout(() => processScan(), 100);
    }
});

function processScan() {
    const barcode = document.getElementById('barcodeInput').value.trim();
    
    if (!barcode) {
        Swal.fire({
            title: 'Código requerido',
            text: 'Por favor escanea o ingresa un código de barras',
            icon: 'warning'
        });
        return;
    }
    
    const product = orderProducts.find(p => p.UPC === barcode);
    
    if (!product) {
        Swal.fire({
            title: 'Producto no encontrado',
            text: 'Este código no corresponde a ningún producto de esta orden',
            icon: 'error'
        });
        document.getElementById('barcodeInput').value = '';
        return;
    }
    
    if (pickedProducts[barcode] && pickedProducts[barcode].completed) {
        Swal.fire({
            title: 'Producto ya completado',
            text: 'Este producto ya ha sido pickeado completamente',
            icon: 'info'
        });
        document.getElementById('barcodeInput').value = '';
        return;
    }
    
    showCurrentProduct(product);
}

function showCurrentProduct(product) {
    const currentProductDiv = document.getElementById('currentProduct');
    const weightInput = document.getElementById('weightInput');
    
    document.getElementById('currentProductName').textContent = product.Nombre;
    document.getElementById('currentProductType').textContent = product.Tipo;
    document.getElementById('currentProductQty').textContent = 
        product.Tipo === 'Pesable' ? 
        parseFloat(product.Cantidad).toFixed(3) + ' kg' : 
        Math.round(product.Cantidad) + ' un.';
    
    const current = pickedProducts[product.UPC] || { scanned: 0, completed: false };
    document.getElementById('currentProductScanned').textContent = 
        product.Tipo === 'Pesable' ? 
        (current.scanned > 0 ? parseFloat(current.scanned).toFixed(3) + ' kg' : '0 kg') :
        current.scanned + ' un.';
    
    if (product.Tipo === 'Pesable') {
        weightInput.classList.remove('hidden');
        document.getElementById('weightAmount').focus();
    } else {
        weightInput.classList.add('hidden');
    }
    
    currentProductDiv.classList.remove('hidden');
    
    window.currentScannedProduct = product;
}

function confirmScan() {
    const product = window.currentScannedProduct;
    if (!product) return;
    
    const barcode = product.UPC;
    
    if (!pickedProducts[barcode]) {
        pickedProducts[barcode] = { scanned: 0, completed: false };
    }
    
    if (product.Tipo === 'Pesable') {
        const weight = parseFloat(document.getElementById('weightAmount').value);
        
        if (!weight || weight <= 0) {
            Swal.fire({
                title: 'Cantidad requerida',
                text: 'Por favor ingresa la cantidad pesada',
                icon: 'warning'
            });
            return;
        }
        
        pickedProducts[barcode].scanned = weight;
        pickedProducts[barcode].completed = true;
        
        Swal.fire({
            title: '¡Producto pickeado!',
            text: `${product.Nombre}: ${weight.toFixed(3)} kg`,
            icon: 'success',
            timer: 1500,
            showConfirmButton: false
        });
        
    } else {
        pickedProducts[barcode].scanned += 1;
        
        const required = Math.round(product.Cantidad);
        const scanned = pickedProducts[barcode].scanned;
        
        if (scanned >= required) {
            pickedProducts[barcode].completed = true;
            Swal.fire({
                title: '¡Producto completado!',
                text: `${product.Nombre}: ${scanned} de ${required} unidades`,
                icon: 'success',
                timer: 1500,
                showConfirmButton: false
            });
        } else {
            Swal.fire({
                title: 'Producto escaneado',
                text: `${product.Nombre}: ${scanned} de ${required} unidades`,
                icon: 'info',
                timer: 1000,
                showConfirmButton: false
            });
        }
    }
    
    renderProductsList();
    updateProgress();
    
    document.getElementById('barcodeInput').value = '';
    document.getElementById('weightAmount').value = '';
    document.getElementById('currentProduct').classList.add('hidden');
    
    document.getElementById('barcodeInput').focus();
}

async function completePicking() {
    try {
        const response = await fetch('api/picking/completePicking.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                orderId: currentOrderId,
                pickedProducts: pickedProducts
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            Swal.fire({
                title: '¡Picking Completado!',
                text: 'La orden ha sido marcada como pickeada exitosamente',
                icon: 'success'
            }).then(() => {
                closePicking();
                refreshOrders();
            });
        } else {
            throw new Error(data.message || 'Error al completar picking');
        }
        
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            title: 'Error',
            text: 'No se pudo completar el picking',
            icon: 'error'
        });
    }
}

function closePicking() {
    document.getElementById('pickingModal').classList.add('hidden');
    currentOrderId = '';
    orderProducts = [];
    pickedProducts = {};
    
    document.getElementById('barcodeInput').value = '';
    document.getElementById('weightAmount').value = '';
    document.getElementById('currentProduct').classList.add('hidden');
}
</script>

<style>
.animate-fadeIn {
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-slideIn {
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from { opacity: 0; transform: translateX(-20px); }
    to { opacity: 1; transform: translateX(0); }
}

#barcodeInput:focus {
    box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.1);
}

.ring-2 {
    box-shadow: 0 0 0 2px currentColor;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

#productsList .bg-white:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transition: all 0.2s ease-in-out;
}

#productsList img {
    transition: transform 0.2s ease-in-out;
}

#productsList .bg-white:hover img {
    transform: scale(1.05);
}

</style>
