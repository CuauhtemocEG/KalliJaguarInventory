<?php
require_once "./controllers/mainController.php";

$fechaInicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : date('Y-m-d', strtotime('-30 days'));
$fechaFin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : date('Y-m-d');

$db = conexion();

$topProductosQuery = $db->prepare("
    SELECT 
        p.Nombre,
        p.UPC,
        SUM(mi.Cantidad) as TotalSolicitado,
        COUNT(DISTINCT mi.ComandaID) as NumeroComandas,
        SUM(mi.PrecioFinal) as ValorTotal,
        p.image
    FROM MovimientosInventario mi
    INNER JOIN Productos p ON mi.ProductoID = p.ProductoID
    WHERE mi.TipoMovimiento = 'Salida' 
        AND mi.FechaMovimiento BETWEEN :inicio AND :fin
        AND mi.Status != 'Cancelado'
    GROUP BY p.ProductoID
    ORDER BY TotalSolicitado DESC
    LIMIT 10
");
$topProductosQuery->execute([':inicio' => $fechaInicio, ':fin' => $fechaFin]);
$topProductos = $topProductosQuery->fetchAll(PDO::FETCH_ASSOC);

$tendenciaComandasQuery = $db->prepare("
    SELECT 
        DATE(FechaMovimiento) as Fecha,
        COUNT(DISTINCT ComandaID) as TotalComandas,
        SUM(PrecioFinal) as ValorTotal
    FROM MovimientosInventario
    WHERE TipoMovimiento = 'Salida' 
        AND FechaMovimiento BETWEEN :inicio AND :fin
    GROUP BY DATE(FechaMovimiento)
    ORDER BY Fecha ASC
");
$tendenciaComandasQuery->execute([':inicio' => $fechaInicio, ':fin' => $fechaFin]);
$tendenciaComandas = $tendenciaComandasQuery->fetchAll(PDO::FETCH_ASSOC);

$sucursalesQuery = $db->prepare("
    SELECT 
        s.Nombre as Sucursal,
        COUNT(DISTINCT mi.ComandaID) as TotalComandas,
        SUM(mi.Cantidad) as TotalProductos,
        SUM(mi.PrecioFinal) as ValorTotal,
        AVG(mi.PrecioFinal) as PromedioComanda
    FROM MovimientosInventario mi
    INNER JOIN Sucursales s ON mi.SucursalID = s.SucursalID
    WHERE mi.TipoMovimiento = 'Salida' 
        AND mi.FechaMovimiento BETWEEN :inicio AND :fin
        AND mi.Status != 'Cancelado'
    GROUP BY s.SucursalID
    ORDER BY TotalComandas DESC
");
$sucursalesQuery->execute([':inicio' => $fechaInicio, ':fin' => $fechaFin]);
$sucursales = $sucursalesQuery->fetchAll(PDO::FETCH_ASSOC);

$estadosComandasQuery = $db->prepare("
    SELECT 
        Status,
        COUNT(DISTINCT ComandaID) as Total,
        SUM(PrecioFinal) as ValorTotal,
        AVG(PrecioFinal) as Promedio
    FROM MovimientosInventario
    WHERE TipoMovimiento = 'Salida' 
        AND FechaMovimiento BETWEEN :inicio AND :fin
    GROUP BY Status
");
$estadosComandasQuery->execute([':inicio' => $fechaInicio, ':fin' => $fechaFin]);
$estadosComandas = $estadosComandasQuery->fetchAll(PDO::FETCH_KEY_PAIR);

$topValorQuery = $db->prepare("
    SELECT 
        p.Nombre,
        p.UPC,
        SUM(mi.PrecioFinal) as ValorTotal,
        SUM(mi.Cantidad) as CantidadTotal,
        AVG(mi.PrecioFinal/mi.Cantidad) as PrecioPromedio
    FROM MovimientosInventario mi
    INNER JOIN Productos p ON mi.ProductoID = p.ProductoID
    WHERE mi.TipoMovimiento = 'Salida' 
        AND mi.FechaMovimiento BETWEEN :inicio AND :fin
        AND mi.Status != 'Cancelado'
    GROUP BY p.ProductoID
    ORDER BY ValorTotal DESC
    LIMIT 10
");
$topValorQuery->execute([':inicio' => $fechaInicio, ':fin' => $fechaFin]);
$topValor = $topValorQuery->fetchAll(PDO::FETCH_ASSOC);

$tiempoEntregaQuery = $db->prepare("
    SELECT 
        AVG(DATEDIFF(
            (SELECT MAX(FechaMovimiento) 
             FROM MovimientosInventario mi2 
             WHERE mi2.ComandaID = mi.ComandaID AND mi2.Status = 'Cerrado'),
            mi.FechaMovimiento
        )) as PromedioEntrega,
        MIN(DATEDIFF(
            (SELECT MAX(FechaMovimiento) 
             FROM MovimientosInventario mi2 
             WHERE mi2.ComandaID = mi.ComandaID AND mi2.Status = 'Cerrado'),
            mi.FechaMovimiento
        )) as MinimoEntrega,
        MAX(DATEDIFF(
            (SELECT MAX(FechaMovimiento) 
             FROM MovimientosInventario mi2 
             WHERE mi2.ComandaID = mi.ComandaID AND mi2.Status = 'Cerrado'),
            mi.FechaMovimiento
        )) as MaximoEntrega
    FROM MovimientosInventario mi
    WHERE mi.TipoMovimiento = 'Salida' 
        AND mi.Status = 'Abierto'
        AND mi.FechaMovimiento BETWEEN :inicio AND :fin
");
$tiempoEntregaQuery->execute([':inicio' => $fechaInicio, ':fin' => $fechaFin]);
$tiempoEntrega = $tiempoEntregaQuery->fetch(PDO::FETCH_ASSOC);

$categoriasQuery = $db->prepare("
    SELECT 
        c.Nombre as Categoria,
        COUNT(DISTINCT mi.ComandaID) as NumeroComandas,
        SUM(mi.Cantidad) as TotalProductos,
        SUM(mi.PrecioFinal) as ValorTotal
    FROM MovimientosInventario mi
    INNER JOIN Productos p ON mi.ProductoID = p.ProductoID
    INNER JOIN Categorias c ON p.CategoriaID = c.CategoriaID
    WHERE mi.TipoMovimiento = 'Salida' 
        AND mi.FechaMovimiento BETWEEN :inicio AND :fin
        AND mi.Status != 'Cancelado'
    GROUP BY c.CategoriaID
    ORDER BY TotalProductos DESC
");
$categoriasQuery->execute([':inicio' => $fechaInicio, ':fin' => $fechaFin]);
$categorias = $categoriasQuery->fetchAll(PDO::FETCH_ASSOC);

$metricsQuery = $db->prepare("
    SELECT 
        COUNT(DISTINCT ComandaID) as TotalComandas,
        SUM(Cantidad) as TotalProductosSolicitados,
        SUM(PrecioFinal) as ValorTotalVentas,
        COUNT(DISTINCT ProductoID) as ProductosDiferentes,
        AVG(PrecioFinal) as PromedioComanda
    FROM MovimientosInventario
    WHERE TipoMovimiento = 'Salida' 
        AND FechaMovimiento BETWEEN :inicio AND :fin
");
$metricsQuery->execute([':inicio' => $fechaInicio, ':fin' => $fechaFin]);
$metrics = $metricsQuery->fetch(PDO::FETCH_ASSOC);

$productosCanceladosQuery = $db->prepare("
    SELECT 
        p.Nombre,
        p.UPC,
        COUNT(DISTINCT mi.ComandaID) as VecesCancelado,
        SUM(mi.Cantidad) as CantidadCancelada,
        SUM(mi.PrecioFinal) as ValorPerdido
    FROM MovimientosInventario mi
    INNER JOIN Productos p ON mi.ProductoID = p.ProductoID
    WHERE mi.TipoMovimiento = 'Salida' 
        AND mi.Status = 'Cancelado'
        AND mi.FechaMovimiento BETWEEN :inicio AND :fin
    GROUP BY p.ProductoID
    ORDER BY VecesCancelado DESC
    LIMIT 10
");
$productosCanceladosQuery->execute([':inicio' => $fechaInicio, ':fin' => $fechaFin]);
$productosCancelados = $productosCanceladosQuery->fetchAll(PDO::FETCH_ASSOC);

function formatMoney($value) {
    return '$' . number_format($value, 2);
}

function formatNumber($value) {
    return number_format($value, 0);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Avanzado - Análisis de Inventario</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
</head>
<body>

<div class="min-h-screen bg-gray-50 py-6">
    <div class="w-full mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">📊 Dashboard Analítico Avanzado</h1>
                    <p class="text-gray-600">Análisis detallado de inventario y solicitudes</p>
                </div>
                
                <form method="GET" action="" class="flex flex-wrap gap-3 items-end">
                    <input type="hidden" name="page" value="dashboardAvanzado">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Inicio</label>
                        <input type="date" name="fecha_inicio" value="<?php echo $fechaInicio; ?>" 
                               class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Fin</label>
                        <input type="date" name="fecha_fin" value="<?php echo $fechaFin; ?>" 
                               class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 font-medium">
                        🔍 Aplicar Filtros
                    </button>
                    <a href="?page=home" class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors duration-200 font-medium">
                        ← Volver al Dashboard Principal
                    </a>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm font-medium">Total Comandas</p>
                        <p class="text-3xl font-bold mt-2"><?php echo formatNumber($metrics['TotalComandas'] ?? 0); ?></p>
                    </div>
                    <div class="bg-blue-400 bg-opacity-30 rounded-full p-3">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-sm font-medium">Valor Total</p>
                        <p class="text-3xl font-bold mt-2"><?php echo formatMoney($metrics['ValorTotalVentas'] ?? 0); ?></p>
                    </div>
                    <div class="bg-green-400 bg-opacity-30 rounded-full p-3">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"></path>
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-100 text-sm font-medium">Productos Solicitados</p>
                        <p class="text-3xl font-bold mt-2"><?php echo formatNumber($metrics['TotalProductosSolicitados'] ?? 0); ?></p>
                    </div>
                    <div class="bg-purple-400 bg-opacity-30 rounded-full p-3">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-orange-500 to-orange-600 text-white rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-orange-100 text-sm font-medium">Promedio/Comanda</p>
                        <p class="text-3xl font-bold mt-2"><?php echo formatMoney($metrics['PromedioComanda'] ?? 0); ?></p>
                    </div>
                    <div class="bg-orange-400 bg-opacity-30 rounded-full p-3">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-red-500 to-red-600 text-white rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-red-100 text-sm font-medium">Productos Diferentes</p>
                        <p class="text-3xl font-bold mt-2"><?php echo formatNumber($metrics['ProductosDiferentes'] ?? 0); ?></p>
                    </div>
                    <div class="bg-red-400 bg-opacity-30 rounded-full p-3">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">🏆 Top 10 Productos Más Solicitados</h3>
                <div class="h-80">
                    <canvas id="topProductosChart"></canvas>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">📈 Tendencia de Comandas en el Tiempo</h3>
                <div class="h-80">
                    <canvas id="tendenciaChart"></canvas>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">💰 Top 10 Productos por Valor Generado</h3>
                <div class="h-80">
                    <canvas id="topValorChart"></canvas>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">🏢 Rendimiento por Sucursal</h3>
                <div class="h-80">
                    <canvas id="sucursalesChart"></canvas>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">📦 Categorías Más Solicitadas</h3>
                <div class="h-80">
                    <canvas id="categoriasChart"></canvas>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">📊 Distribución por Estado</h3>
                <div class="h-80">
                    <canvas id="estadosChart"></canvas>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">📋 Detalle Top Productos</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($topProductos as $index => $producto): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-bold text-gray-900"><?php echo $index + 1; ?></td>
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    <div class="font-medium"><?php echo htmlspecialchars($producto['Nombre']); ?></div>
                                    <div class="text-gray-500 text-xs"><?php echo htmlspecialchars($producto['UPC']); ?></div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo formatNumber($producto['TotalSolicitado']); ?>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-green-600">
                                    <?php echo formatMoney($producto['ValorTotal']); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">🏪 Detalle por Sucursal</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sucursal</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comandas</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Productos</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($sucursales as $sucursal): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($sucursal['Sucursal']); ?>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo formatNumber($sucursal['TotalComandas']); ?>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo formatNumber($sucursal['TotalProductos']); ?>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-green-600">
                                    <?php echo formatMoney($sucursal['ValorTotal']); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php if (count($productosCancelados) > 0): ?>
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h3 class="text-xl font-bold text-gray-900 mb-4">⚠️ Productos Más Cancelados (Requieren Atención)</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-red-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">Producto</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">UPC</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">Veces Cancelado</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">Cantidad Perdida</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">Valor Perdido</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($productosCancelados as $producto): ?>
                        <tr class="hover:bg-red-50">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($producto['Nombre']); ?>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                <?php echo htmlspecialchars($producto['UPC']); ?>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-red-600 font-semibold">
                                <?php echo formatNumber($producto['VecesCancelado']); ?>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                <?php echo formatNumber($producto['CantidadCancelada']); ?>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-red-600">
                                <?php echo formatMoney($producto['ValorPerdido']); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>

<script>
Chart.defaults.font.family = "'Inter', 'system-ui', sans-serif";
Chart.defaults.color = '#374151';

const topProductosCtx = document.getElementById('topProductosChart').getContext('2d');
new Chart(topProductosCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_column($topProductos, 'Nombre')); ?>,
        datasets: [{
            label: 'Cantidad Solicitada',
            data: <?php echo json_encode(array_column($topProductos, 'TotalSolicitado')); ?>,
            backgroundColor: 'rgba(59, 130, 246, 0.8)',
            borderColor: 'rgba(59, 130, 246, 1)',
            borderWidth: 2,
            borderRadius: 6
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        indexAxis: 'y',
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: 'rgba(17, 24, 39, 0.95)',
                padding: 12,
                titleFont: { size: 14, weight: 'bold' },
                bodyFont: { size: 13 },
                callbacks: {
                    label: function(context) {
                        return 'Cantidad: ' + context.parsed.x.toLocaleString();
                    }
                }
            }
        },
        scales: {
            x: { 
                beginAtZero: true,
                grid: { color: 'rgba(0, 0, 0, 0.05)' }
            },
            y: { 
                grid: { display: false },
                ticks: { 
                    font: { size: 11 },
                    callback: function(value, index) {
                        const label = this.getLabelForValue(value);
                        return label.length > 20 ? label.substring(0, 20) + '...' : label;
                    }
                }
            }
        }
    }
});

const tendenciaCtx = document.getElementById('tendenciaChart').getContext('2d');
new Chart(tendenciaCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_column($tendenciaComandas, 'Fecha')); ?>,
        datasets: [{
            label: 'Comandas',
            data: <?php echo json_encode(array_column($tendenciaComandas, 'TotalComandas')); ?>,
            borderColor: 'rgba(16, 185, 129, 1)',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            tension: 0.4,
            fill: true,
            pointRadius: 4,
            pointHoverRadius: 6,
            pointBackgroundColor: 'rgba(16, 185, 129, 1)',
            pointBorderColor: '#fff',
            pointBorderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: 'rgba(17, 24, 39, 0.95)',
                padding: 12
            }
        },
        scales: {
            x: { 
                grid: { display: false },
                ticks: { 
                    maxRotation: 45,
                    minRotation: 45,
                    font: { size: 10 }
                }
            },
            y: { 
                beginAtZero: true,
                grid: { color: 'rgba(0, 0, 0, 0.05)' }
            }
        }
    }
});

const topValorCtx = document.getElementById('topValorChart').getContext('2d');
new Chart(topValorCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_column($topValor, 'Nombre')); ?>,
        datasets: [{
            label: 'Valor Total ($)',
            data: <?php echo json_encode(array_column($topValor, 'ValorTotal')); ?>,
            backgroundColor: 'rgba(16, 185, 129, 0.8)',
            borderColor: 'rgba(16, 185, 129, 1)',
            borderWidth: 2,
            borderRadius: 6
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        indexAxis: 'y',
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: 'rgba(17, 24, 39, 0.95)',
                padding: 12,
                callbacks: {
                    label: function(context) {
                        return 'Valor: $' + context.parsed.x.toLocaleString('es-MX', {minimumFractionDigits: 2});
                    }
                }
            }
        },
        scales: {
            x: { 
                beginAtZero: true,
                grid: { color: 'rgba(0, 0, 0, 0.05)' },
                ticks: {
                    callback: function(value) {
                        return '$' + value.toLocaleString();
                    }
                }
            },
            y: { 
                grid: { display: false },
                ticks: { 
                    font: { size: 11 },
                    callback: function(value, index) {
                        const label = this.getLabelForValue(value);
                        return label.length > 20 ? label.substring(0, 20) + '...' : label;
                    }
                }
            }
        }
    }
});

const sucursalesCtx = document.getElementById('sucursalesChart').getContext('2d');
new Chart(sucursalesCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_column($sucursales, 'Sucursal')); ?>,
        datasets: [{
            data: <?php echo json_encode(array_column($sucursales, 'TotalComandas')); ?>,
            backgroundColor: [
                'rgba(59, 130, 246, 0.8)',
                'rgba(16, 185, 129, 0.8)',
                'rgba(251, 191, 36, 0.8)',
                'rgba(239, 68, 68, 0.8)',
                'rgba(139, 92, 246, 0.8)',
                'rgba(236, 72, 153, 0.8)'
            ],
            borderWidth: 3,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { 
                position: 'bottom',
                labels: { 
                    padding: 15,
                    usePointStyle: true,
                    font: { size: 12 }
                }
            },
            tooltip: {
                backgroundColor: 'rgba(17, 24, 39, 0.95)',
                padding: 12,
                callbacks: {
                    label: function(context) {
                        return context.label + ': ' + context.parsed + ' comandas';
                    }
                }
            }
        },
        cutout: '60%'
    }
});

const categoriasCtx = document.getElementById('categoriasChart').getContext('2d');
new Chart(categoriasCtx, {
    type: 'polarArea',
    data: {
        labels: <?php echo json_encode(array_column($categorias, 'Categoria')); ?>,
        datasets: [{
            data: <?php echo json_encode(array_column($categorias, 'TotalProductos')); ?>,
            backgroundColor: [
                'rgba(59, 130, 246, 0.7)',
                'rgba(16, 185, 129, 0.7)',
                'rgba(251, 191, 36, 0.7)',
                'rgba(239, 68, 68, 0.7)',
                'rgba(139, 92, 246, 0.7)',
                'rgba(236, 72, 153, 0.7)',
                'rgba(14, 165, 233, 0.7)',
                'rgba(249, 115, 22, 0.7)'
            ],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { 
                position: 'bottom',
                labels: { 
                    padding: 12,
                    font: { size: 11 }
                }
            },
            tooltip: {
                backgroundColor: 'rgba(17, 24, 39, 0.95)',
                padding: 12
            }
        }
    }
});

const estadosCtx = document.getElementById('estadosChart').getContext('2d');
new Chart(estadosCtx, {
    type: 'pie',
    data: {
        labels: ['Abierto', 'En tránsito', 'Cerrado', 'Cancelado'],
        datasets: [{
            data: [
                <?= $estadosComandas['Abierto'] ?? 0 ?>,
                <?= $estadosComandas['En transito'] ?? 0 ?>,
                <?= $estadosComandas['Cerrado'] ?? 0 ?>,
                <?= $estadosComandas['Cancelado'] ?? 0 ?>
            ],
            backgroundColor: [
                'rgba(59, 130, 246, 0.8)',
                'rgba(251, 191, 36, 0.8)',
                'rgba(16, 185, 129, 0.8)',
                'rgba(239, 68, 68, 0.8)'
            ],
            borderWidth: 3,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { 
                position: 'bottom',
                labels: { 
                    padding: 15,
                    usePointStyle: true,
                    font: { size: 12 }
                }
            },
            tooltip: {
                backgroundColor: 'rgba(17, 24, 39, 0.95)',
                padding: 12,
                callbacks: {
                    label: function(context) {
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((context.parsed / total) * 100).toFixed(1);
                        return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                    }
                }
            }
        }
    }
});
</script>

</body>
</html>
