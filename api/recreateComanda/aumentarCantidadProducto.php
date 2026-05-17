<?php
require_once '../../controllers/mainController.php';

header('Content-Type: application/json');

// Log para depuración
error_log("aumentarCantidadProducto.php - Iniciando");

$input = json_decode(file_get_contents('php://input'), true);

// Log del input recibido
error_log("Input recibido: " . json_encode($input));

if (!isset($input['movimiento_id']) || !isset($input['cantidad'])) {
    http_response_code(400);
    error_log("Faltan parámetros requeridos");
    echo json_encode(['success' => false, 'error' => 'Faltan parámetros requeridos']);
    exit;
}

$movimientoID = $input['movimiento_id'];
$cantidadAumentar = floatval($input['cantidad']);

if ($cantidadAumentar <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'La cantidad debe ser mayor a 0']);
    exit;
}

try {
    $db = conexion();
    $db->beginTransaction();

    // Obtener información del movimiento
    $stmt = $db->prepare("
        SELECT ProductoID, Cantidad, ComandaID 
        FROM MovimientosInventario 
        WHERE MovimientoID = :id
    ");
    $stmt->bindParam(':id', $movimientoID);
    $stmt->execute();
    $mov = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$mov) {
        throw new Exception('Movimiento no encontrado');
    }

    // Obtener información del producto
    $stmt = $db->prepare("SELECT Cantidad, PrecioUnitario, Tipo FROM Productos WHERE ProductoID = :productoID");
    $stmt->bindParam(':productoID', $mov['ProductoID']);
    $stmt->execute();
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$producto) {
        throw new Exception('Producto no encontrado');
    }

    // Verificar si hay suficiente stock
    if ($producto['Cantidad'] < $cantidadAumentar) {
        throw new Exception('No hay suficiente stock del producto. Stock disponible: ' . $producto['Cantidad']);
    }

    // Actualizar cantidad en el movimiento
    $nuevaCantidad = $mov['Cantidad'] + $cantidadAumentar;
    $nuevoPrecioFinal = $nuevaCantidad * ($producto['PrecioUnitario'] * 1.16);
    
    $stmt = $db->prepare("
        UPDATE MovimientosInventario 
        SET Cantidad = :cantidad, PrecioFinal = :precioFinal 
        WHERE MovimientoID = :id
    ");
    $stmt->bindParam(':cantidad', $nuevaCantidad);
    $stmt->bindParam(':precioFinal', $nuevoPrecioFinal);
    $stmt->bindParam(':id', $movimientoID);
    $stmt->execute();

    // Descontar del stock del producto
    $stmt = $db->prepare("UPDATE Productos SET Cantidad = Cantidad - :cantidad WHERE ProductoID = :productoID");
    $stmt->bindParam(':cantidad', $cantidadAumentar);
    $stmt->bindParam(':productoID', $mov['ProductoID']);
    $stmt->execute();

    $db->commit();
    echo json_encode([
        'success' => true, 
        'message' => 'Cantidad aumentada correctamente'
    ]);

} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    error_log("Error en aumentarCantidadProducto: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} catch (PDOException $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    error_log("Error PDO en aumentarCantidadProducto: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Error de base de datos: ' . $e->getMessage()]);
}
