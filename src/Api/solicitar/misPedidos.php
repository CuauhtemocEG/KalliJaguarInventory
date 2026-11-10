<?php
header('Content-Type: application/json; charset=utf-8');
require_once BASE_PATH . '/src/Helpers/legacy.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Solo las comandas del usuario en sesión (?comanda=ID devuelve además su detalle)
$userId = (int) $_SESSION['id'];
$db = conexion();

$stmt = $db->prepare(
    "SELECT m.ComandaID, MIN(m.FechaMovimiento) AS fecha, MAX(m.Status) AS estado,
            MAX(m.FechaDelivery) AS entrega, s.nombre AS sucursal,
            COUNT(*) AS articulos, SUM(m.PrecioFinal) AS total
     FROM MovimientosInventario m
     LEFT JOIN Sucursales s ON s.SucursalID = m.SucursalID
     WHERE m.UsuarioID = :u AND m.TipoMovimiento = 'Salida' AND m.ComandaID IS NOT NULL
     GROUP BY m.ComandaID, s.nombre
     ORDER BY fecha DESC
     LIMIT 50"
);
$stmt->execute([':u' => $userId]);
$respuesta = ['success' => true, 'pedidos' => $stmt->fetchAll(PDO::FETCH_ASSOC)];

$comanda = trim((string) ($_GET['comanda'] ?? ''));
if ($comanda !== '') {
    $det = $db->prepare(
        "SELECT p.Nombre, p.Tipo, m.Cantidad, m.PrecioFinal
         FROM MovimientosInventario m
         INNER JOIN Productos p ON p.ProductoID = m.ProductoID
         WHERE m.ComandaID = :c AND m.UsuarioID = :u AND m.TipoMovimiento = 'Salida'
         ORDER BY p.Nombre"
    );
    $det->execute([':c' => $comanda, ':u' => $userId]);
    $respuesta['detalle'] = $det->fetchAll(PDO::FETCH_ASSOC);
}

echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
