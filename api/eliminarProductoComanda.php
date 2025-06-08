<?php
require_once '../controllers/mainController.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['movimiento_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Falta el ID del movimiento']);
    exit;
}

$movimientoID = $data['movimiento_id'];

try {
    $db = conexion();

    // Obtener datos antes de eliminar
    $stmt = $db->prepare("SELECT ProductoID, Cantidad FROM MovimientosInventario WHERE MovimientoID = :id");
    $stmt->bindParam(':id', $movimientoID);
    $stmt->execute();
    $mov = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$mov) {
        echo json_encode(['success' => false, 'error' => 'Movimiento no encontrado']);
        exit;
    }

    // Eliminar movimiento
    $stmt = $db->prepare("DELETE FROM MovimientosInventario WHERE MovimientoID = :id");
    $stmt->bindParam(':id', $movimientoID);
    $stmt->execute();

    // Devolver stock
    $stmt = $db->prepare("UPDATE Productos SET Cantidad = Cantidad + :cantidad WHERE ProductoID = :productoID");
    $stmt->bindParam(':cantidad', $mov['Cantidad']);
    $stmt->bindParam(':productoID', $mov['ProductoID']);
    $stmt->execute();

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error en la base de datos: ' . $e->getMessage()]);
}
