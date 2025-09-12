<?php
require_once '../../controllers/mainController.php';

// Headers CORS para permitir solicitudes desde diferentes subdominios
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
header('Content-Type: application/json; charset=UTF-8');

// Manejar OPTIONS request para preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['movimiento_id'], $data['cantidad'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
    exit;
}

$movimientoID = $data['movimiento_id'];
$cantidadDevolver = floatval($data['cantidad']);

if ($cantidadDevolver <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Cantidad inválida']);
    exit;
}

try {
    $db = conexion();

    // Obtener datos actuales
    $stmt = $db->prepare("SELECT ProductoID, Cantidad FROM MovimientosInventario WHERE MovimientoID = :id");
    $stmt->bindParam(':id', $movimientoID);
    $stmt->execute();
    $mov = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$mov) {
        echo json_encode(['success' => false, 'error' => 'Movimiento no encontrado']);
        exit;
    }

    if ($cantidadDevolver > $mov['Cantidad']) {
        echo json_encode(['success' => false, 'error' => 'Cantidad a devolver mayor que la cantidad en la comanda']);
        exit;
    }

    // Actualizar cantidad en movimiento (restar cantidad devuelta)
    $nuevaCantidad = $mov['Cantidad'] - $cantidadDevolver;

    if ($nuevaCantidad == 0) {
        // Eliminar el movimiento si queda en 0
        $stmt = $db->prepare("DELETE FROM MovimientosInventario WHERE MovimientoID = :id");
        $stmt->bindParam(':id', $movimientoID);
        $stmt->execute();
    } else {
        $stmt = $db->prepare("UPDATE MovimientosInventario SET Cantidad = :nuevaCantidad WHERE MovimientoID = :id");
        $stmt->bindParam(':nuevaCantidad', $nuevaCantidad);
        $stmt->bindParam(':id', $movimientoID);
        $stmt->execute();
    }

    // Devolver cantidad al stock en Productos
    $stmt = $db->prepare("UPDATE Productos SET Cantidad = Cantidad + :cantidad WHERE ProductoID = :productoID");
    $stmt->bindParam(':cantidad', $cantidadDevolver);
    $stmt->bindParam(':productoID', $mov['ProductoID']);
    $stmt->execute();

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error en la base de datos: ' . $e->getMessage()]);
}
