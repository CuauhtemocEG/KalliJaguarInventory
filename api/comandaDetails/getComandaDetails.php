<?php
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

require_once '../../controllers/mainController.php';

if (!isset($_GET['ComandaID'])) {
    http_response_code(400);
    echo "ComandaID no proporcionado.";
    exit;
}

$comandaID = $_GET['ComandaID'];
$conn = conexion();

$query = $conn->prepare("
    SELECT 
    m.ProductoID, 
    p.Nombre, 
    p.Descripcion, 
    p.PrecioUnitario, 
    p.Tipo,
    m.Cantidad, 
    m.PrecioFinal, 
    (m.Cantidad * (p.PrecioUnitario*1.16)) AS Subtotal, 
    u.Nombre AS Solicitante, 
    s.nombre AS Sucursal 
    FROM MovimientosInventario m 
    JOIN Productos p ON m.ProductoID = p.ProductoID 
    JOIN Usuarios u ON m.UsuarioID = u.UsuarioID 
    JOIN Sucursales s ON m.SucursalID = s.SucursalID 
    WHERE m.ComandaID = :comandaID 
    AND m.TipoMovimiento = 'Salida'");
$query->execute([':comandaID' => $comandaID]);
$productos = $query->fetchAll(PDO::FETCH_ASSOC);

if (!$productos) {
    echo '
    <div class="flex flex-col items-center justify-center py-12">
        <div class="w-20 h-20 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4">
            <i class="fas fa-box-open text-gray-400 text-2xl"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
            Sin productos encontrados
        </h3>
        <p class="text-gray-500 dark:text-gray-400 text-center">
            No se encontraron productos para esta comanda.
        </p>
    </div>';
    exit;
}

$solicitante = $productos[0]['Solicitante'];
$sucursal = $productos[0]['Sucursal'];
$total = 0;
?>

<?php echo date('Y-m-d H:i:s'); ?> 
<div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg p-6 mb-6 border border-blue-200 dark:border-blue-700">
    <div class="flex items-center mb-4">
        <div class="flex-shrink-0">
            <div class="w-12 h-12 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg flex items-center justify-center shadow-md">
                <i class="fas fa-info-circle text-white text-xl"></i>
            </div>
        </div>
        <div class="ml-4">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                📋 Información de la Solicitud
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-300">
                Detalles completos de la comanda
            </p>
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-600 shadow-sm">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-user text-blue-600 dark:text-blue-400 text-lg"></i>
                </div>
                <div class="ml-3">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                        Solicitante
                    </p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">
                        <?php echo htmlspecialchars($solicitante); ?>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-600 shadow-sm">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-store text-green-600 dark:text-green-400 text-lg"></i>
                </div>
                <div class="ml-3">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                        Sucursal Destino
                    </p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">
                        <?php echo htmlspecialchars($sucursal); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-10 h-10 bg-gradient-to-r from-purple-600 to-pink-600 rounded-lg flex items-center justify-center shadow-md">
                    <i class="fas fa-list text-white"></i>
                </div>
            </div>
            <div class="ml-4">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                    Productos Solicitados
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Listado detallado de productos y cantidades
                </p>
            </div>
        </div>
    </div>
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-600 dark:bg-gray-800">
                <tr>
                    <th class="px-6 py-4 text-left text-sm font-bold text-white uppercase tracking-wider">
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-box text-white"></i>
                            <span>Producto</span>
                        </div>
                    </th>
                    <th class="px-6 py-4 text-left text-sm font-bold text-white uppercase tracking-wider">
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-align-left text-white"></i>
                            <span>Descripción</span>
                        </div>
                    </th>
                    <th class="px-6 py-4 text-center text-sm font-bold text-white uppercase tracking-wider">
                        <div class="flex items-center justify-center space-x-2">
                            <i class="fas fa-calculator text-white"></i>
                            <span>Cantidad</span>
                        </div>
                    </th>
                    <th class="px-6 py-4 text-right text-sm font-bold text-white uppercase tracking-wider">
                        <div class="flex items-center justify-end space-x-2">
                            <i class="fas fa-dollar-sign text-white"></i>
                            <span>Precio Unitario</span>
                        </div>
                    </th>
                    <th class="px-6 py-4 text-right text-sm font-bold text-white uppercase tracking-wider">
                        <div class="flex items-center justify-end space-x-2">
                            <i class="fas fa-receipt text-white"></i>
                            <span>Subtotal</span>
                        </div>
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <?php foreach ($productos as $index => $producto):
                    $total += $producto['Subtotal'];
                    $isEven = $index % 2 === 0;
                ?>
                    <tr class="<?php echo $isEven ? 'bg-white dark:bg-gray-800' : 'bg-gray-50 dark:bg-gray-750'; ?> hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors duration-200">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg flex items-center justify-center shadow-sm">
                                        <i class="fas fa-cube text-white text-sm"></i>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-semibold text-gray-900 dark:text-white">
                                        <?php echo htmlspecialchars($producto['Nombre']); ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-600 dark:text-gray-300 max-w-xs">
                                <p class="truncate" title="<?php echo htmlspecialchars($producto['Descripcion']); ?>">
                                    <?php echo htmlspecialchars($producto['Descripcion']); ?>
                                </p>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                             <?php
                                // Lógica para determinar unidades y formato
                                if ($producto['Tipo'] == "Pesable") {
                                    if ($producto['Cantidad'] >= 1.0) {
                                        $unidadesResult = 'Kg';
                                        $quantityRes = number_format($producto['Cantidad'], 2, '.', '');
                                    } else {
                                        $unidadesResult = 'grs';
                                        $quantityRes = number_format($producto['Cantidad'] * 1000, 0, '.', '');
                                    }
                                } else {
                                    $unidadesResult = 'Unidad(es)';
                                    $quantityRes = number_format($producto['Cantidad'], 0, '.', '');
                                }
                            ?>
                            <div class="flex flex-col items-center space-y-1">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-blue-600 dark:bg-blue-700 text-white shadow-sm">
                                    <i class="fas fa-hashtag mr-1 text-xs"></i>
                                    <?php echo $quantityRes; ?>
                                </span>
                                <span class="text-xs font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wide">
                                    <?php echo $unidadesResult; ?>
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <div class="text-sm font-semibold text-gray-900 dark:text-white">
                                $<?php echo number_format($producto['PrecioUnitario'] * 1.16, 2); ?>
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-300">
                                IVA incluido
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <div class="text-sm font-bold text-green-600 dark:text-green-400">
                                $<?php echo number_format($producto['Subtotal'], 2); ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Footer con total -->
    <div class="bg-gray-600 dark:bg-gray-800 px-6 py-4 border-t border-gray-200 dark:border-gray-600">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                    <i class="fas fa-check text-green-600 dark:text-green-400 text-sm"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-white">
                        Total de productos: <?php echo count($productos); ?>
                    </p>
                    <p class="text-xs text-gray-200">
                        Precios con IVA incluido
                    </p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-sm font-medium text-white mb-1">
                    Total General:
                </p>
                <p class="text-2xl font-bold text-green-400">
                    $<?php echo number_format($total, 2); ?>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="mt-6 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg p-4">
    <div class="flex items-start">
        <div class="flex-shrink-0">
            <i class="fas fa-info-circle text-yellow-600 dark:text-yellow-400 text-lg"></i>
        </div>
        <div class="ml-3">
            <h4 class="text-sm font-semibold text-yellow-800 dark:text-yellow-200 mb-1">
                Información Importante
            </h4>
            <ul class="text-sm text-yellow-700 dark:text-yellow-300 space-y-1">
                <li class="flex items-center">
                    <i class="fas fa-circle text-xs mr-2"></i>
                    Todos los precios incluyen IVA (16%)
                </li>
                <li class="flex items-center">
                    <i class="fas fa-circle text-xs mr-2"></i>
                    Los productos están sujetos a disponibilidad de inventario
                </li>
                <li class="flex items-center">
                    <i class="fas fa-circle text-xs mr-2"></i>
                    El tiempo de entrega puede variar según la sucursal destino
                </li>
            </ul>
        </div>
    </div>
</div>