<?php
session_start();
header('Content-Type: application/json');
require_once '../../controllers/mainController.php';
$conn = conexion();

$userId = $_SESSION['id'] ?? null;
if (!$userId) {
    echo json_encode(['status' => 'error', 'message' => 'Usuario no identificado']);
    exit();
}

$stmt = $conn->prepare("SELECT MAX(FechaMovimiento) AS FechaMovimiento, Status, ComandaID, 
           MAX(SucursalID) AS SucursalID, MAX(MovimientoID) AS MovimientoID, 
           COUNT(DISTINCT ProductoID) AS TotalProductos, 
           SUM(Cantidad) AS TotalCantidad 
    FROM MovimientosInventario 
    WHERE TipoMovimiento = 'Salida' AND UsuarioID = ? 
    GROUP BY ComandaID, Status, UsuarioID 
    ORDER BY MovimientoID DESC
                        ");
$stmt->execute([$userId]);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['status' => 'success', 'requests' => $requests]);
