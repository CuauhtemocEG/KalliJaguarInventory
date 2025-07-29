<?php
header('Content-Type: application/json');

require_once "../../controllers/mainController.php";

try {
    $conexion = conexion();
    $stmt = $conexion->prepare("SELECT SucursalID, nombre, direccion FROM Sucursales ORDER BY nombre ASC");
    $stmt->execute();
    $sucursales = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'sucursales' => $sucursales
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error al obtener sucursales',
        'error' => $e->getMessage()
    ]);
}