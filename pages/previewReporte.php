<?php
require_once('./controllers/mainController.php');

$fechaInicio = $_GET['fecha_desde'] ?? date('Y-m-01');
$fechaFin = $_GET['fecha_hasta'] ?? date('Y-m-d');

function formatearCantidad($cantidad, $tipo) {
    if (strtolower($tipo) === 'pesable') {
        if ($cantidad >= 1.0) {
            return number_format($cantidad, 2) . ' Kg';
        } else {
            return number_format($cantidad * 1000, 0) . ' g';
        }
    } else {
        return number_format($cantidad, 0) . ' Unid.';
    }
}

try {
    $conn = conexion();

    $query = "
        SELECT 
            m.ComandaID,
            m.SucursalID,
            s.nombre AS NombreSucursal,
            p.Nombre AS NombreProducto,
            p.Tipo,
            m.Cantidad,
            p.PrecioUnitario,
            m.PrecioFinal,
            (m.Cantidad * p.PrecioUnitario) AS SubtotalSinIVA,
            (m.Cantidad * p.PrecioUnitario * 0.16) AS IVA,
            (m.Cantidad * p.PrecioUnitario * 1.16) AS SubtotalConIVA,
            m.FechaMovimiento
        FROM MovimientosInventario m
        JOIN Productos p ON m.ProductoID = p.ProductoID
        JOIN Sucursales s ON m.SucursalID = s.SucursalID
        WHERE m.TipoMovimiento = 'Salida'
          AND DATE(m.FechaMovimiento) BETWEEN :fechaInicio AND :fechaFin
        ORDER BY s.nombre, m.ComandaID, p.Nombre
        LIMIT 50
    ";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':fechaInicio', $fechaInicio);
    $stmt->bindParam(':fechaFin', $fechaFin);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Organizar datos
    $datos = [];
    $totalesSucursal = [];
    $totalComandas = [];

    foreach ($rows as $row) {
        $sucursal = $row['NombreSucursal'];
        $comanda = $row['ComandaID'];

        if (!isset($datos[$sucursal])) $datos[$sucursal] = [];
        if (!isset($datos[$sucursal][$comanda])) $datos[$sucursal][$comanda] = [];

        $datos[$sucursal][$comanda][] = $row;

        if (!isset($totalesSucursal[$sucursal])) $totalesSucursal[$sucursal] = 0;
        if (!isset($totalComandas[$sucursal][$comanda])) $totalComandas[$sucursal][$comanda] = 0;
        
        $subtotal = $row['SubtotalConIVA'];
        $totalesSucursal[$sucursal] += $subtotal;
        $totalComandas[$sucursal][$comanda] += $subtotal;
    }

} catch (PDOException $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vista Previa - Reporte de Ventas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { font-size: 12px; }
        }
        .invoice-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="no-print bg-white shadow-sm border-b border-gray-200 p-4 mb-6">
        <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-900">Vista Previa del Reporte</h2>
                <p class="text-sm text-gray-600">Período: <?= date('d/m/Y', strtotime($fechaInicio)) ?> - <?= date('d/m/Y', strtotime($fechaFin)) ?></p>
            </div>
            <div class="flex gap-3">
                <form method="GET" class="flex items-center gap-2">
                    <input type="date" name="fecha_desde" value="<?= $fechaInicio ?>" class="px-3 py-2 border rounded-lg text-sm">
                    <input type="date" name="fecha_hasta" value="<?= $fechaFin ?>" class="px-3 py-2 border rounded-lg text-sm">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">
                        <i class="fas fa-sync mr-1"></i> Actualizar
                    </button>
                </form>
                <button onclick="window.print()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    <i class="fas fa-print mr-2"></i>Imprimir
                </button>
                <a href="./api/generarReportePDF.php" onclick="generatePDF()" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    <i class="fas fa-file-pdf mr-2"></i>Generar PDF
                </a>
                <a href="index.php?page=reportOrders" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    <i class="fas fa-arrow-left mr-2"></i>Volver
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto bg-white shadow-xl print:shadow-none print:max-w-none">
        <div class="invoice-header text-white p-8">
            <div class="flex items-start justify-between">
                <div class="flex items-center space-x-4">
                    <img src="./img/logo.png" alt="Logo" class="h-16 w-16 bg-white rounded-lg p-2" onerror="this.style.display='none'">
                    <div>
                        <h1 class="text-2xl font-bold">KALLI JAGUAR INVENTORY</h1>
                        <p class="text-blue-100">Sistema de Gestión de Inventario</p>
                        <p class="text-blue-100 text-sm">Reporte Generado: <?= date('d/m/Y H:i:s') ?></p>
                    </div>
                </div>
                
                <div class="text-right">
                    <div class="bg-white/20 rounded-lg p-4">
                        <h2 class="text-lg font-bold mb-2">REPORTE DE VENTAS</h2>
                        <div class="text-sm space-y-1">
                            <p><strong>Período:</strong> <?= date('d/m/Y', strtotime($fechaInicio)) ?></p>
                            <p><strong>Hasta:</strong> <?= date('d/m/Y', strtotime($fechaFin)) ?></p>
                            <p><strong>Registros:</strong> <?= count($rows) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-8">
            <?php if (isset($error)): ?>
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle text-red-500 mr-3"></i>
                        <div>
                            <h3 class="text-red-800 font-semibold">Error de conexión</h3>
                            <p class="text-red-600 text-sm"><?= htmlspecialchars($error) ?></p>
                        </div>
                    </div>
                </div>
            <?php elseif (empty($rows)): ?>
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
                    <i class="fas fa-info-circle text-yellow-500 text-2xl mb-3"></i>
                    <h3 class="text-yellow-800 font-semibold mb-2">No hay datos</h3>
                    <p class="text-yellow-600">No se encontraron registros en el período seleccionado.</p>
                </div>
            <?php else: ?>
                <div class="bg-slate-800 text-white p-4 rounded-lg mb-6">
                    <h2 class="text-xl font-bold text-center">📊 REPORTE DETALLADO POR SUCURSALES Y COMANDAS</h2>
                </div>

                <?php 
                $totalGeneral = 0;
                $contadorSucursales = 0;
                
                foreach ($datos as $sucursal => $comandas): 
                    $contadorSucursales++;
                ?>
                    <div class="bg-blue-600 text-white p-4 rounded-t-lg mt-8">
                        <h3 class="text-lg font-bold">
                            <i class="fas fa-map-marker-alt mr-2"></i>
                            SUCURSAL: <?= strtoupper(htmlspecialchars($sucursal)) ?>
                        </h3>
                    </div>

                    <?php foreach ($comandas as $comandaID => $items): ?>
                        <div class="bg-gray-100 p-3 border-x border-gray-300">
                            <h4 class="font-semibold text-gray-800">
                                <i class="fas fa-receipt mr-2"></i>
                                Comanda N° <?= htmlspecialchars($comandaID) ?>
                            </h4>
                        </div>

                        <div class="border-x border-gray-300">
                            <table class="w-full">
                                <thead class="bg-gray-600 text-white">
                                    <tr>
                                        <th class="px-4 py-3 text-left">PRODUCTO</th>
                                        <th class="px-4 py-3 text-center">CANTIDAD</th>
                                        <th class="px-4 py-3 text-center">P. UNIT.</th>
                                        <th class="px-4 py-3 text-center">IVA (16%)</th>
                                        <th class="px-4 py-3 text-center">SUBTOTAL</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $index => $item): ?>
                                        <tr class="<?= $index % 2 == 0 ? 'bg-white' : 'bg-gray-50' ?> border-b border-gray-200">
                                            <td class="px-4 py-3"><?= htmlspecialchars($item['NombreProducto']) ?></td>
                                            <td class="px-4 py-3 text-center"><?= formatearCantidad($item['Cantidad'], $item['Tipo']) ?></td>
                                            <td class="px-4 py-3 text-center">$<?= number_format($item['PrecioUnitario'], 2) ?></td>
                                            <td class="px-4 py-3 text-center">$<?= number_format($item['IVA'], 2) ?></td>
                                            <td class="px-4 py-3 text-center font-semibold">$<?= number_format($item['SubtotalConIVA'], 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="bg-orange-500 text-white p-3 border-x border-gray-300">
                            <div class="flex justify-between items-center">
                                <span class="font-bold">
                                    <i class="fas fa-calculator mr-2"></i>
                                    TOTAL COMANDA
                                </span>
                                <span class="text-xl font-bold">$<?= number_format($totalComandas[$sucursal][$comandaID], 2) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="bg-green-600 text-white p-4 rounded-b-lg mb-2">
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-bold">
                                <i class="fas fa-building mr-2"></i>
                                TOTAL SUCURSAL: <?= strtoupper(htmlspecialchars($sucursal)) ?>
                            </span>
                            <span class="text-2xl font-bold">$<?= number_format($totalesSucursal[$sucursal], 2) ?></span>
                        </div>
                    </div>

                    <?php $totalGeneral += $totalesSucursal[$sucursal]; ?>
                <?php endforeach; ?>

                <div class="bg-red-600 text-white p-6 rounded-lg mt-8 mb-6">
                    <div class="flex justify-between items-center">
                        <span class="text-2xl font-bold">
                            <i class="fas fa-trophy mr-3"></i>
                            TOTAL GENERAL DEL PERÍODO
                        </span>
                        <span class="text-3xl font-bold">$<?= number_format($totalGeneral, 2) ?></span>
                    </div>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                    <h3 class="text-lg font-bold text-blue-800 mb-4">
                        <i class="fas fa-chart-bar mr-2"></i>
                        RESUMEN ESTADÍSTICO
                    </h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-white p-4 rounded-lg border">
                            <div class="text-2xl font-bold text-blue-600"><?= $contadorSucursales ?></div>
                            <div class="text-sm text-gray-600">Sucursales</div>
                        </div>
                        <div class="bg-white p-4 rounded-lg border">
                            <div class="text-2xl font-bold text-green-600"><?= count($rows) ?></div>
                            <div class="text-sm text-gray-600">Registros</div>
                        </div>
                        <div class="bg-white p-4 rounded-lg border">
                            <div class="text-2xl font-bold text-orange-600">$<?= number_format($totalGeneral / $contadorSucursales, 2) ?></div>
                            <div class="text-sm text-gray-600">Promedio/Sucursal</div>
                        </div>
                        <div class="bg-white p-4 rounded-lg border">
                            <div class="text-lg font-bold text-purple-600"><?= date('d/m/Y', strtotime($fechaInicio)) ?> - <?= date('d/m/Y', strtotime($fechaFin)) ?></div>
                            <div class="text-sm text-gray-600">Período</div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="bg-gray-100 p-4 text-center border-t">
            <p class="text-sm text-gray-600">Kalli Jaguar Inventory - Sistema de Gestión Integral</p>
            <p class="text-xs text-gray-500">Generado el <?= date('d/m/Y H:i:s') ?></p>
        </div>
    </div>

    <script>
        function generatePDF() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = './api/generarReportePDF.php';
            form.target = '_blank';
            
            const fechaDesde = document.createElement('input');
            fechaDesde.type = 'hidden';
            fechaDesde.name = 'fecha_desde';
            fechaDesde.value = '<?= $fechaInicio ?>';
            
            const fechaHasta = document.createElement('input');
            fechaHasta.type = 'hidden';
            fechaHasta.name = 'fecha_hasta';
            fechaHasta.value = '<?= $fechaFin ?>';
            
            form.appendChild(fechaDesde);
            form.appendChild(fechaHasta);
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        }
    </script>
</body>
</html>
