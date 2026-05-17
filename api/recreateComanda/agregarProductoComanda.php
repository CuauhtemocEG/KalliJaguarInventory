<?php
require_once '../../controllers/mainController.php';

header('Content-Type: application/json');

// Log para depuración
error_log("agregarProductoComanda.php - Iniciando");

$input = json_decode(file_get_contents('php://input'), true);

// Log del input recibido
error_log("Input recibido: " . json_encode($input));

if (!isset($input['comanda_id']) || !isset($input['producto_id']) || !isset($input['cantidad'])) {
    http_response_code(400);
    error_log("Faltan parámetros requeridos");
    echo json_encode(['success' => false, 'error' => 'Faltan parámetros requeridos']);
    exit;
}

$comandaID = $input['comanda_id'];
$productoID = $input['producto_id'];
$cantidad = floatval($input['cantidad']);

if ($cantidad <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'La cantidad debe ser mayor a 0']);
    exit;
}

try {
    $db = conexion();
    $db->beginTransaction();

    // Verificar que el producto existe y obtener su información
    $stmt = $db->prepare("SELECT ProductoID, Nombre, Cantidad, PrecioUnitario, Tipo FROM Productos WHERE ProductoID = :productoID");
    $stmt->bindParam(':productoID', $productoID);
    $stmt->execute();
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$producto) {
        throw new Exception('El producto no existe');
    }

    // Verificar si hay suficiente stock
    if ($producto['Cantidad'] < $cantidad) {
        throw new Exception('No hay suficiente stock del producto. Stock disponible: ' . $producto['Cantidad']);
    }

    // Verificar si el producto ya existe en la comanda
    $stmt = $db->prepare("
        SELECT MovimientoID, Cantidad, SucursalID, UsuarioID, Status 
        FROM MovimientosInventario 
        WHERE ComandaID = :comandaID 
        AND ProductoID = :productoID 
        AND TipoMovimiento = 'Salida'
        LIMIT 1
    ");
    $stmt->bindParam(':comandaID', $comandaID);
    $stmt->bindParam(':productoID', $productoID);
    $stmt->execute();
    $movimientoExistente = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($movimientoExistente) {
        // Si ya existe, actualizar la cantidad
        $nuevaCantidad = $movimientoExistente['Cantidad'] + $cantidad;
        $nuevoPrecioFinal = $nuevaCantidad * ($producto['PrecioUnitario'] * 1.16);
        
        $stmt = $db->prepare("
            UPDATE MovimientosInventario 
            SET Cantidad = :cantidad, PrecioFinal = :precioFinal 
            WHERE MovimientoID = :movimientoID
        ");
        $stmt->bindParam(':cantidad', $nuevaCantidad);
        $stmt->bindParam(':precioFinal', $nuevoPrecioFinal);
        $stmt->bindParam(':movimientoID', $movimientoExistente['MovimientoID']);
        $stmt->execute();
    } else {
        // Si no existe, obtener datos de la comanda (SucursalID, UsuarioID, Status de un movimiento existente)
        $stmt = $db->prepare("
            SELECT SucursalID, UsuarioID, Status 
            FROM MovimientosInventario 
            WHERE ComandaID = :comandaID 
            LIMIT 1
        ");
        $stmt->bindParam(':comandaID', $comandaID);
        $stmt->execute();
        $infoComanda = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$infoComanda) {
            throw new Exception('No se pudo obtener información de la comanda');
        }
        
        // Crear nuevo movimiento con los mismos datos de la comanda
        $fecha = date('Y-m-d H:i:s');
        $precioFinal = $cantidad * ($producto['PrecioUnitario'] * 1.16);
        
        $stmt = $db->prepare("
            INSERT INTO MovimientosInventario 
            (ComandaID, SucursalID, ProductoID, TipoMovimiento, Cantidad, FechaMovimiento, PrecioFinal, UsuarioID, Status) 
            VALUES (:comandaID, :sucursalID, :productoID, 'Salida', :cantidad, :fecha, :precioFinal, :usuarioID, :status)
        ");
        $stmt->bindParam(':comandaID', $comandaID);
        $stmt->bindParam(':sucursalID', $infoComanda['SucursalID']);
        $stmt->bindParam(':productoID', $productoID);
        $stmt->bindParam(':cantidad', $cantidad);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->bindParam(':precioFinal', $precioFinal);
        $stmt->bindParam(':usuarioID', $infoComanda['UsuarioID']);
        $stmt->bindParam(':status', $infoComanda['Status']);
        $stmt->execute();
    }

    // Descontar del stock del producto
    $stmt = $db->prepare("UPDATE Productos SET Cantidad = Cantidad - :cantidad WHERE ProductoID = :productoID");
    $stmt->bindParam(':cantidad', $cantidad);
    $stmt->bindParam(':productoID', $productoID);
    $stmt->execute();

    $db->commit();
    echo json_encode([
        'success' => true, 
        'message' => 'Producto agregado correctamente'
    ]);

} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    error_log("Error en agregarProductoComanda: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} catch (PDOException $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    error_log("Error PDO en agregarProductoComanda: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Error de base de datos: ' . $e->getMessage()]);
}
