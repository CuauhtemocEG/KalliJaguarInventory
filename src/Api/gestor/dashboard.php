<?php
header('Content-Type: application/json; charset=utf-8');
require_once BASE_PATH . '/src/Helpers/legacy.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$db = conexion();

$estados = $db->query(
    "SELECT Status AS estado, COUNT(DISTINCT ComandaID) AS total
     FROM MovimientosInventario
     WHERE TipoMovimiento = 'Salida' AND ComandaID IS NOT NULL
     GROUP BY Status"
)->fetchAll(PDO::FETCH_KEY_PAIR);

$mes = $db->query(
    "SELECT COUNT(DISTINCT ComandaID) AS pedidos, COALESCE(SUM(PrecioFinal), 0) AS monto
     FROM MovimientosInventario
     WHERE TipoMovimiento = 'Salida' AND ComandaID IS NOT NULL AND Status <> 'Cancelado'
       AND YEAR(FechaMovimiento) = YEAR(CURDATE()) AND MONTH(FechaMovimiento) = MONTH(CURDATE())"
)->fetch(PDO::FETCH_ASSOC);

$inventario = $db->query(
    "SELECT COUNT(*) AS productos,
            COALESCE(SUM(Cantidad * PrecioUnitario), 0) AS valor,
            SUM(CASE WHEN Cantidad <= StockMinimo THEN 1 ELSE 0 END) AS bajo_stock
     FROM Productos"
)->fetch(PDO::FETCH_ASSOC);

$bajoStock = $db->query(
    "SELECT ProductoID, Nombre, Cantidad, StockMinimo, Tipo
     FROM Productos
     WHERE Cantidad <= StockMinimo
     ORDER BY (Cantidad - StockMinimo) ASC, Nombre
     LIMIT 8"
)->fetchAll(PDO::FETCH_ASSOC);

$recientes = $db->query(
    "SELECT m.ComandaID, MIN(m.FechaMovimiento) AS fecha, MAX(m.Status) AS estado,
            s.nombre AS sucursal, u.Nombre AS solicitante, SUM(m.PrecioFinal) AS total
     FROM MovimientosInventario m
     LEFT JOIN Sucursales s ON s.SucursalID = m.SucursalID
     LEFT JOIN Usuarios u ON u.UsuarioID = m.UsuarioID
     WHERE m.TipoMovimiento = 'Salida' AND m.ComandaID IS NOT NULL
     GROUP BY m.ComandaID, s.nombre, u.Nombre
     ORDER BY fecha DESC
     LIMIT 8"
)->fetchAll(PDO::FETCH_ASSOC);

$masSolicitados = $db->query(
    "SELECT p.Nombre, p.Tipo, SUM(m.Cantidad) AS cantidad
     FROM MovimientosInventario m
     INNER JOIN Productos p ON p.ProductoID = m.ProductoID
     WHERE m.TipoMovimiento = 'Salida' AND m.Status <> 'Cancelado'
       AND m.FechaMovimiento >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
     GROUP BY p.ProductoID, p.Nombre, p.Tipo
     ORDER BY cantidad DESC
     LIMIT 5"
)->fetchAll(PDO::FETCH_ASSOC);

$mesAnterior = $db->query(
    "SELECT COUNT(DISTINCT ComandaID) AS pedidos, COALESCE(SUM(PrecioFinal), 0) AS monto
     FROM MovimientosInventario
     WHERE TipoMovimiento = 'Salida' AND ComandaID IS NOT NULL AND Status <> 'Cancelado'
       AND FechaMovimiento >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-01')
       AND FechaMovimiento < DATE_FORMAT(CURDATE(), '%Y-%m-01')"
)->fetch(PDO::FETCH_ASSOC);

$serie = $db->query(
    "SELECT DATE(FechaMovimiento) AS dia, COALESCE(SUM(PrecioFinal), 0) AS monto, COUNT(DISTINCT ComandaID) AS pedidos
     FROM MovimientosInventario
     WHERE TipoMovimiento = 'Salida' AND ComandaID IS NOT NULL AND Status <> 'Cancelado'
       AND FechaMovimiento >= DATE_SUB(CURDATE(), INTERVAL 13 DAY)
     GROUP BY DATE(FechaMovimiento)"
)->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);

$porSucursal = $db->query(
    "SELECT COALESCE(s.nombre, 'Sin sucursal') AS sucursal, COALESCE(SUM(m.PrecioFinal), 0) AS monto, COUNT(DISTINCT m.ComandaID) AS pedidos
     FROM MovimientosInventario m
     LEFT JOIN Sucursales s ON s.SucursalID = m.SucursalID
     WHERE m.TipoMovimiento = 'Salida' AND m.ComandaID IS NOT NULL AND m.Status <> 'Cancelado'
       AND YEAR(m.FechaMovimiento) = YEAR(CURDATE()) AND MONTH(m.FechaMovimiento) = MONTH(CURDATE())
     GROUP BY s.SucursalID, s.nombre
     ORDER BY monto DESC
     LIMIT 5"
)->fetchAll(PDO::FETCH_ASSOC);

$hoy = (int) $db->query(
    "SELECT COUNT(DISTINCT ComandaID) FROM MovimientosInventario
     WHERE TipoMovimiento = 'Salida' AND ComandaID IS NOT NULL AND DATE(FechaMovimiento) = CURDATE()"
)->fetchColumn();

try {
    $requisiciones = $db->query(
        "SELECT SUM(Estado = 'Enviada') AS por_aprobar, SUM(Estado IN ('Aprobada', 'Parcial')) AS por_recibir
         FROM Requisiciones"
    )->fetch(PDO::FETCH_ASSOC) ?: [];
} catch (Throwable $e) {
    $requisiciones = [];
}

echo json_encode([
    'success' => true,
    'estados' => $estados,
    'mes' => $mes,
    'mes_anterior' => $mesAnterior,
    'serie' => $serie,
    'por_sucursal' => $porSucursal,
    'hoy' => $hoy,
    'requisiciones' => $requisiciones,
    'inventario' => $inventario,
    'bajo_stock' => $bajoStock,
    'recientes' => $recientes,
    'mas_solicitados' => $masSolicitados,
], JSON_UNESCAPED_UNICODE);
