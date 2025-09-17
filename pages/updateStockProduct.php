<?php
require './controllers/mainController.php';
$pdo = conexion();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    ob_start();

    $codigo = limpiar_cadena($_POST['codigo'] ?? '');
    $nuevo_stock = $_POST['nuevo_stock'];

    if ($codigo && $nuevo_stock >= 0) {
        $stmt = $pdo->prepare("SELECT Cantidad FROM Productos WHERE UPC = :codigo");
        $stmt->execute([':codigo' => $codigo]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$producto) {
            ob_end_clean();
            echo json_encode(['status' => 'error', 'message' => 'Producto no encontrado.']);
            exit();
        }

        $stock_actual = $producto['Cantidad'];

        $upd = $pdo->prepare("UPDATE Productos SET Cantidad = :nuevo WHERE UPC = :codigo");
        if ($upd->execute([':nuevo' => $nuevo_stock, ':codigo' => $codigo])) {
            $log = $pdo->prepare("INSERT INTO Logs_stock (UPC, StockBefore, StockAfter, UsuarioID) VALUES (:codigo, :anterior, :nuevo, :session)");
            $log->execute([
                ':codigo' => $codigo,
                ':anterior' => $stock_actual,
                ':nuevo' => $nuevo_stock,
                ':session' => $_SESSION['id']
            ]);
            ob_end_clean();
            echo json_encode(['status' => 'ok', 'message' => '¡Stock actualizado exitosamente!']);
        } else {
            ob_end_clean();
            echo json_encode(['status' => 'error', 'message' => 'Error al actualizar stock.']);
        }
    } else {
        ob_end_clean();
        echo json_encode(['status' => 'error', 'message' => 'Datos inválidos.']);
    }
    exit();
}

// Si es GET, mostrar formulario
$codigo = $_GET['codigo'] ?? '';
if (!$codigo) {
    echo "<div class='bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4'>
            <div class='flex items-center'>
                <svg class='w-5 h-5 mr-2' fill='currentColor' viewBox='0 0 20 20'>
                    <path fill-rule='evenodd' d='M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z' clip-rule='evenodd'></path>
                </svg>
                Código no proporcionado.
            </div>
          </div>";
    return;
}

$stmt = $pdo->prepare("SELECT * FROM Productos WHERE UPC = :codigo");
$stmt->execute([':codigo' => $codigo]);
$producto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$producto) {
    echo "<div class='bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4'>
            <div class='flex items-center'>
                <svg class='w-5 h-5 mr-2' fill='currentColor' viewBox='0 0 20 20'>
                    <path fill-rule='evenodd' d='M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z' clip-rule='evenodd'></path>
                </svg>
                Producto no encontrado.
            </div>
          </div>";
    return;
}

$etiquetaPesable = '';

if ($producto['Tipo'] === 'Pesable' && $producto['Cantidad'] < 1.0) {
    $etiquetaPesable = 'gr';
} else {
    $etiquetaPesable = 'Kg';
}

$etiquetaUnit = '';

if ($producto['Tipo'] === 'Unidad') {
    $etiquetaUnit = 'Unidad(es)';
}
?>

<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-600 rounded-full mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Actualizar Stock</h1>
            <p class="text-lg text-gray-600">Modifica la cantidad disponible del producto escaneado</p>
        </div>

        <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-white flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        Información del Producto
                    </h2>
                    <div class="flex items-center space-x-2">
                        <?php if ($producto['Tipo'] === 'Pesable'): ?>
                            <span class="px-3 py-1 bg-yellow-400 text-yellow-900 text-xs font-medium rounded-full">Pesable</span>
                        <?php else: ?>
                            <span class="px-3 py-1 bg-green-400 text-green-900 text-xs font-medium rounded-full">Por Unidad</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="space-y-4">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <label class="text-sm font-medium text-gray-600">Nombre del Producto</label>
                            <p class="text-lg font-semibold text-gray-900 mt-1"><?= htmlspecialchars($producto['Nombre']) ?></p>
                        </div>
                        
                        <div class="bg-gray-50 rounded-lg p-4">
                            <label class="text-sm font-medium text-gray-600">Código de Barras (UPC)</label>
                            <p class="text-lg font-mono text-gray-900 mt-1"><?= htmlspecialchars($producto['UPC']) ?></p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <label class="text-sm font-medium text-gray-600">Tipo de Inventario</label>
                            <p class="text-lg font-semibold text-gray-900 mt-1"><?= htmlspecialchars($producto['Tipo']) ?></p>
                        </div>
                        
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-4 border border-blue-200">
                            <label class="text-sm font-medium text-blue-700">Stock Actual</label>
                            <div id="stock-actual-container" class="mt-2">
                                <?php if ($producto['Tipo'] === 'Pesable'): ?>
                                    <span id="stock-actual" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-lg font-semibold rounded-lg">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16l3-1m-3 1l-3-1"></path>
                                        </svg>
                                        <?= number_format($producto['Cantidad'], 3) . ' ' . $etiquetaPesable ?>
                                    </span>
                                <?php else: ?>
                                    <span id="stock-actual" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-lg font-semibold rounded-lg">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                        </svg>
                                        <?= number_format($producto['Cantidad'], 0) . ' ' . $etiquetaUnit ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-lg p-6 border-2 border-dashed border-gray-300">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Actualizar Cantidad
                    </h3>
                    
                    <form id="form-actualizar" class="space-y-6">
                        <div>
                            <label for="nuevo_stock" class="block text-sm font-medium text-gray-700 mb-2">
                                Nuevo stock
                                <?php if ($producto['Tipo'] === 'Pesable'): ?>
                                    <span class="text-xs text-gray-500">(en <?= $etiquetaPesable ?>)</span>
                                <?php else: ?>
                                    <span class="text-xs text-gray-500">(en unidades)</span>
                                <?php endif; ?>
                            </label>
                            <?php if ($producto['Tipo'] === 'Pesable'): ?>
                                <input type="number" 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg font-medium" 
                                       id="nuevo_stock" 
                                       name="nuevo_stock" 
                                       min="0" 
                                       step="0.250" 
                                       placeholder="0.000"
                                       required>
                            <?php else: ?>
                                <input type="number" 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg font-medium" 
                                       id="nuevo_stock" 
                                       name="nuevo_stock" 
                                       min="0" 
                                       step="1" 
                                       placeholder="0"
                                       required>
                            <?php endif; ?>
                            <input type="hidden" name="codigo" value="<?= htmlspecialchars($producto['UPC']) ?>">
                            <input type="hidden" name="id" value="<?= $_SESSION['id']?>">
                            <input type="hidden" name="tipo" value="<?= htmlspecialchars($producto['Tipo']) ?>">
                        </div>
                        
                        <button type="submit" class="w-full flex items-center justify-center px-6 py-4 bg-gradient-to-r from-green-600 to-green-700 text-white text-lg font-semibold rounded-lg hover:from-green-700 hover:to-green-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transform transition-all duration-200 hover:scale-105 shadow-lg">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Actualizar Stock
                        </button>
                    </form>
                </div>

                <div id="boton-nuevo-escanear" class="mt-6 hidden">
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div>
                                    <h4 class="text-sm font-medium text-green-800">¡Stock actualizado correctamente!</h4>
                                    <p class="text-sm text-green-600">¿Deseas escanear otro producto?</p>
                                </div>
                            </div>
                            <a href="index.php?page=scanProducts" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h4"></path>
                                </svg>
                                Nuevo Escaneo
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById("form-actualizar").addEventListener("submit", function(e) {
        e.preventDefault();

        const datos = new FormData(this);
        const nuevoStock = document.getElementById("nuevo_stock").value;
        const tipoProducto = document.querySelector('input[name="tipo"]').value;

        fetch('../api/updateStock.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new FormData(this)
            })
            .then(res => res.json())
            .then(data => {
                Swal.fire({
                    title: data.status === "ok" ? "¡Éxito!" : "Error",
                    text: data.message,
                    icon: data.status === "ok" ? "success" : "error",
                    timer: data.status === "ok" ? 3000 : undefined,
                    showConfirmButton: data.status !== "ok"
                });

                if (data.status === "ok") {
                    const stockActualElement = document.getElementById("stock-actual");
                    let nuevaEtiqueta = '';
                    let stockFormateado = '';
                    
                    if (tipoProducto === 'Pesable') {
                        if (parseFloat(nuevoStock) >= 1.0) {
                            nuevaEtiqueta = 'Kg';
                            stockFormateado = parseFloat(nuevoStock).toFixed(3);
                        } else {
                            nuevaEtiqueta = 'gr';
                            stockFormateado = parseFloat(nuevoStock).toFixed(3);
                        }
                    } else {
                        nuevaEtiqueta = 'Unidad(es)';
                        stockFormateado = parseInt(nuevoStock).toString();
                    }
                    
                    const iconoSVG = tipoProducto === 'Pesable' 
                        ? '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16l3-1m-3 1l-3-1"></path></svg>'
                        : '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>';
                    
                    stockActualElement.innerHTML = iconoSVG + stockFormateado + ' ' + nuevaEtiqueta;
                    
                    stockActualElement.classList.add('animate-pulse');
                    setTimeout(() => {
                        stockActualElement.classList.remove('animate-pulse');
                    }, 2000);
                    
                    document.getElementById("boton-nuevo-escanear").classList.remove("hidden");
                    
                    document.getElementById("nuevo_stock").value = '';
                }
            })
            .catch(err => {
                Swal.fire({
                    title: "Error",
                    text: "No se pudo actualizar el stock. Intenta nuevamente.",
                    icon: "error"
                });
                console.error(err);
            });
    });
</script>