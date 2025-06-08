<?php
require_once '../controllers/mainController.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['movimiento_id'], $data['cantidad'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos incompletos']);
    exit;
}

$movimientoID = $data['movimiento_id'];
$cantidadDevolver = floatval($data['cantidad']);

try {
    $db = conexion();

    // Obtener el movimiento original
    $stmt = $db->prepare("SELECT * FROM MovimientosInventario WHERE ID = :id");
    $stmt->bindParam(':id', $movimientoID);
    $stmt->execute();
    $mov = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$mov) {
        echo json_encode(['error' => 'Movimiento no encontrado']);
        exit;
    }

    if ($cantidadDevolver <= 0 || $cantidadDevolver > $mov['Cantidad']) {
        echo json_encode(['error' => 'Cantidad inválida']);
        exit;
    }

    // Restar cantidad en el movimiento original
    $nuevaCantidad = $mov['Cantidad'] - $cantidadDevolver;
    $stmt = $db->prepare("UPDATE MovimientosInventario SET Cantidad = :cantidad WHERE MovimientoID = :id");
    $stmt->bindParam(':cantidad', $nuevaCantidad);
    $stmt->bindParam(':id', $movimientoID);
    $stmt->execute();

    // Registrar entrada al inventario
    $stmt = $db->prepare("
        INSERT INTO MovimientosInventario (ProductoID, Cantidad, TipoMovimiento, Fecha, UsuarioID, ComandaID)
        VALUES (:productoID, :cantidad, 'Entrada', NOW(), :usuarioID, :comandaID)
    ");
    $stmt->bindParam(':productoID', $mov['ProductoID']);
    $stmt->bindParam(':cantidad', $cantidadDevolver);
    $stmt->bindParam(':usuarioID', $mov['UsuarioID']);
    $stmt->bindParam(':comandaID', $mov['ComandaID']);
    $stmt->execute();

    // Devolver stock
    $stmt = $db->prepare("UPDATE Productos SET Cantidad = Cantidad + :cantidad WHERE ProductoID = :productoID");
    $stmt->bindParam(':cantidad', $cantidadDevolver);
    $stmt->bindParam(':productoID', $mov['ProductoID']);
    $stmt->execute();

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
