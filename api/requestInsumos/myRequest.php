<?php
session_name("INV");
session_start();
header('Content-Type: application/json');
require_once '../../controllers/mainController.php';
$conn = conexion();

$userId = $_SESSION['id'] ?? null;
if (!$userId) {
    echo json_encode(['status' => 'error', 'message' => 'Usuario no identificado']);
    exit();
}

$stmt = $conn->prepare("SELECT 
    MAX(MI.FechaMovimiento) AS FechaMovimiento, 
    MI.Status, 
    MI.ComandaID, 
    MAX(MI.SucursalID) AS SucursalID, 
    S.nombre AS SucursalNombre,
    MAX(MI.MovimientoID) AS MovimientoID, 
    COUNT(DISTINCT MI.ProductoID) AS TotalProductos, 
    SUM(MI.Cantidad) AS TotalCantidad,
    U.Nombre AS UsuarioNombre
FROM MovimientosInventario MI
INNER JOIN Sucursales S ON S.SucursalID = MI.SucursalID
INNER JOIN Usuarios U ON U.UsuarioID = MI.UsuarioID
WHERE MI.TipoMovimiento = 'Salida' AND MI.UsuarioID = ?
GROUP BY MI.ComandaID, MI.Status, MI.UsuarioID, S.nombre, U.Nombre
ORDER BY MovimientoID DESC
                        ");
$stmt->execute([$userId]);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['status' => 'success', 'requests' => $requests]);
