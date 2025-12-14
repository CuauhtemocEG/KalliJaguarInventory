<?php
require_once "./controllers/mainController.php";

if (!isset($_GET['comandaID'])) {
    header('Location: index.php?page=home');
    exit;
}

$comandaID = $_GET['comandaID'];
$db = conexion();

$comandaQuery = $db->prepare("
    SELECT 
        mi.ComandaID,
        mi.Status,
        MIN(mi.FechaMovimiento) as FechaCreacion,
        MAX(mi.FechaDelivery) as FechaDelivery,
        mi.SucursalID,
        s.nombre as SucursalNombre,
        s.direccion as SucursalDireccion,
        mi.UsuarioID,
        u.Nombre as UsuarioNombre,
        u.email as UsuarioEmail,
        COUNT(DISTINCT mi.ProductoID) as TotalProductos,
        SUM(mi.Cantidad) as TotalCantidad,
        SUM(mi.PrecioFinal) as TotalPrecio
    FROM MovimientosInventario mi
    INNER JOIN Sucursales s ON mi.SucursalID = s.SucursalID
    INNER JOIN Usuarios u ON mi.UsuarioID = u.UsuarioID
    WHERE mi.ComandaID = :comandaID
    AND mi.TipoMovimiento = 'Salida'
    GROUP BY mi.ComandaID, mi.Status, mi.SucursalID, s.nombre, s.direccion, mi.UsuarioID, u.Nombre, u.email
");
$comandaQuery->execute([':comandaID' => $comandaID]);
$comanda = $comandaQuery->fetch(PDO::FETCH_ASSOC);

if (!$comanda) {
    echo '<div class="min-h-screen flex items-center justify-center bg-gray-50">
            <div class="text-center">
                <i class="fas fa-exclamation-circle text-red-500 text-6xl mb-4"></i>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Comanda no encontrada</h2>
                <p class="text-gray-600 mb-4">El ID de comanda proporcionado no existe.</p>
                <a href="index.php?page=home" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="fas fa-arrow-left mr-2"></i> Volver al inicio
                </a>
            </div>
        </div>';
    exit;
}

$productosQuery = $db->prepare("
    SELECT 
        p.ProductoID,
        p.Nombre,
        p.Descripcion,
        p.UPC,
        p.image,
        p.Tipo,
        p.PrecioUnitario,
        mi.Cantidad,
        mi.PrecioFinal,
        c.Nombre as Categoria
    FROM MovimientosInventario mi
    INNER JOIN Productos p ON mi.ProductoID = p.ProductoID
    LEFT JOIN Categorias c ON p.CategoriaID = c.CategoriaID
    WHERE mi.ComandaID = :comandaID
    AND mi.TipoMovimiento = 'Salida'
    ORDER BY p.Nombre ASC
");
$productosQuery->execute([':comandaID' => $comandaID]);
$productos = $productosQuery->fetchAll(PDO::FETCH_ASSOC);

function getStatusBadge($status) {
    $badges = [
        'Abierto' => 'bg-blue-100 text-blue-800 border-blue-200',
        'En transito' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
        'Cerrado' => 'bg-green-100 text-green-800 border-green-200',
        'Cancelado' => 'bg-red-100 text-red-800 border-red-200'
    ];
    return $badges[$status] ?? 'bg-gray-100 text-gray-800 border-gray-200';
}

function getStatusIcon($status) {
    $icons = [
        'Abierto' => 'fa-clipboard-check',
        'En transito' => 'fa-truck',
        'Cerrado' => 'fa-check-circle',
        'Cancelado' => 'fa-times-circle'
    ];
    return $icons[$status] ?? 'fa-question-circle';
}
?>

<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="mb-6">
            <a href="javascript:history.back()" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 mb-4">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Volver
            </a>
            
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                        <svg class="w-8 h-8 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Comanda #<?php echo htmlspecialchars($comanda['ComandaID']); ?>
                    </h1>
                    <p class="text-gray-600 mt-1">Detalles completos de la solicitud</p>
                </div>
                
                <div class="mt-4 md:mt-0 flex flex-wrap gap-3">
                    <a href="index.php?page=showPDF&ComandaID=<?php echo urlencode($comanda['ComandaID']); ?>" 
                       target="_blank"
                       class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors shadow-md">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Descargar PDF
                    </a>
                    
                    <?php if ($comanda['Status'] === 'Abierto' && $_SESSION['id'] == $comanda['UsuarioID']): ?>
                    <button onclick="cancelarComanda('<?php echo htmlspecialchars($comanda['ComandaID']); ?>')"
                            class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors shadow-md">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Cancelar Comanda
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Estado</h3>
                    <i class="fas <?php echo getStatusIcon($comanda['Status']); ?> text-2xl text-blue-600"></i>
                </div>
                <div class="space-y-3">
                    <div>
                        <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold border-2 <?php echo getStatusBadge($comanda['Status']); ?>">
                            <?php echo htmlspecialchars($comanda['Status']); ?>
                        </span>
                    </div>
                    <div class="pt-3 border-t border-gray-200">
                        <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Fecha de creación</p>
                        <p class="text-sm font-medium text-gray-900">
                            <?php echo date('d/m/Y H:i', strtotime($comanda['FechaCreacion'])); ?>
                        </p>
                    </div>
                    <?php if ($comanda['FechaDelivery']): ?>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Fecha de entrega</p>
                        <p class="text-sm font-medium text-gray-900">
                            <?php echo date('d/m/Y', strtotime($comanda['FechaDelivery'])); ?>
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Solicitante</h3>
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <div class="space-y-3">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Nombre</p>
                        <p class="text-sm font-medium text-gray-900">
                            <?php echo htmlspecialchars($comanda['UsuarioNombre']); ?>
                        </p>
                    </div>
                    <?php if ($comanda['UsuarioEmail']): ?>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Email</p>
                        <p class="text-sm font-medium text-gray-900">
                            <?php echo htmlspecialchars($comanda['UsuarioEmail']); ?>
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-purple-500">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Sucursal</h3>
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                <div class="space-y-3">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Nombre</p>
                        <p class="text-sm font-medium text-gray-900">
                            <?php echo htmlspecialchars($comanda['SucursalNombre']); ?>
                        </p>
                    </div>
                    <?php if ($comanda['SucursalDireccion']): ?>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Dirección</p>
                        <p class="text-sm font-medium text-gray-900">
                            <?php echo htmlspecialchars($comanda['SucursalDireccion']); ?>
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-gray-900 via-gray-800 to-black rounded-xl shadow-lg p-6 mb-6 text-white">
            <h3 class="text-lg font-semibold mb-4 flex items-center">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
                Resumen de la Comanda
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4">
                    <p class="text-gray-300 text-sm mb-1">Productos diferentes</p>
                    <p class="text-3xl font-bold"><?php echo number_format($comanda['TotalProductos']); ?></p>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4">
                    <p class="text-gray-300 text-sm mb-1">Cantidad total</p>
                    <p class="text-3xl font-bold"><?php echo number_format($comanda['TotalCantidad']); ?></p>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4">
                    <p class="text-gray-300 text-sm mb-1">Valor total</p>
                    <p class="text-3xl font-bold">$<?php echo number_format($comanda['TotalPrecio'], 2); ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-xl font-semibold text-gray-900 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                    Productos Solicitados
                </h3>
            </div>

            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">UPC</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoría</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Precio Unit.</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($productos as $producto): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-12 w-12">
                                        <?php if ($producto['image']): ?>
                                        <img class="h-12 w-12 rounded-lg object-cover border border-gray-200" 
                                             src="img/producto/<?php echo htmlspecialchars($producto['image']); ?>" 
                                             alt="<?php echo htmlspecialchars($producto['Nombre']); ?>">
                                        <?php else: ?>
                                        <div class="h-12 w-12 rounded-lg bg-gray-200 flex items-center justify-center">
                                            <i class="fas fa-box text-gray-400"></i>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($producto['Nombre']); ?>
                                        </div>
                                        <?php if ($producto['Descripcion']): ?>
                                        <div class="text-sm text-gray-500">
                                            <?php echo htmlspecialchars($producto['Descripcion']); ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo htmlspecialchars($producto['UPC']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    <?php echo htmlspecialchars($producto['Categoria'] ?? 'Sin categoría'); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    <?php echo $producto['Tipo'] === 'Pesable' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800'; ?>">
                                    <?php echo htmlspecialchars($producto['Tipo']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-gray-900">
                                <?php echo number_format($producto['Cantidad'], $producto['Tipo'] === 'Pesable' ? 3 : 0); ?>
                                <?php echo $producto['Tipo'] === 'Pesable' ? 'kg' : 'uds'; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                $<?php echo number_format($producto['PrecioUnitario']*1.16, 2); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-green-600">
                                $<?php echo number_format($producto['PrecioFinal'], 2); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-right text-sm font-bold text-gray-900">
                                TOTAL:
                            </td>
                            <td class="px-6 py-4 text-right text-lg font-bold text-green-600">
                                $<?php echo number_format($comanda['TotalPrecio'], 2); ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="md:hidden divide-y divide-gray-200">
                <?php foreach ($productos as $producto): ?>
                <div class="p-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-start space-x-4 mb-3">
                        <div class="flex-shrink-0">
                            <?php if ($producto['image']): ?>
                            <img class="h-16 w-16 rounded-lg object-cover border border-gray-200" 
                                 src="img/producto/<?php echo htmlspecialchars($producto['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($producto['Nombre']); ?>">
                            <?php else: ?>
                            <div class="h-16 w-16 rounded-lg bg-gray-200 flex items-center justify-center">
                                <i class="fas fa-box text-gray-400"></i>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">
                                <?php echo htmlspecialchars($producto['Nombre']); ?>
                            </p>
                            <p class="text-xs text-gray-500">
                                UPC: <?php echo htmlspecialchars($producto['UPC']); ?>
                            </p>
                            <div class="flex items-center space-x-2 mt-1">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium 
                                    <?php echo $producto['Tipo'] === 'Pesable' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800'; ?>">
                                    <?php echo htmlspecialchars($producto['Tipo']); ?>
                                </span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                    <?php echo htmlspecialchars($producto['Categoria'] ?? 'Sin categoría'); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-4 text-sm">
                        <div>
                            <p class="text-gray-500 text-xs">Cantidad</p>
                            <p class="font-semibold text-gray-900">
                                <?php echo number_format($producto['Cantidad'], $producto['Tipo'] === 'Pesable' ? 3 : 0); ?>
                                <?php echo $producto['Tipo'] === 'Pesable' ? 'kg' : 'uds'; ?>
                            </p>
                        </div>
                        <div>
                            <p class="text-gray-500 text-xs">Precio Unit.</p>
                            <p class="font-semibold text-gray-900">
                                $<?php echo number_format($producto['PrecioUnitario'], 2); ?>
                            </p>
                        </div>
                        <div>
                            <p class="text-gray-500 text-xs">Total</p>
                            <p class="font-bold text-green-600">
                                $<?php echo number_format($producto['PrecioFinal'], 2); ?>
                            </p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <div class="p-4 bg-gray-50">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-bold text-gray-900">TOTAL:</span>
                        <span class="text-lg font-bold text-green-600">
                            $<?php echo number_format($comanda['TotalPrecio'], 2); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function cancelarComanda(comandaID) {
    Swal.fire({
        title: '¿Cancelar Comanda?',
        text: "Esta acción devolverá los productos al inventario. ¿Deseas continuar?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Sí, cancelar',
        cancelButtonText: 'No, mantener'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Procesando...',
                text: 'Cancelando comanda y devolviendo productos al inventario',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            window.location.href = `index.php?page=cancelRequest&ComandaID=${comandaID}`;
        }
    });
}
</script>
