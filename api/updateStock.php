<?php
require '../controllers/mainController.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit;
}

$codigo = limpiar_cadena($_POST['codigo'] ?? '');
$nuevo_stock = isset($_POST['nuevo_stock']) && $_POST['nuevo_stock'] !== '' ? $_POST['nuevo_stock'] : null;
$nuevo_precio = isset($_POST['nuevo_precio']) && $_POST['nuevo_precio'] !== '' ? $_POST['nuevo_precio'] : null;
$session_id = $_POST['id'];

if (!$codigo) {
    echo json_encode(['status' => 'error', 'message' => 'Código no proporcionado']);
    exit;
}

if ($nuevo_stock === null && $nuevo_precio === null) {
    echo json_encode(['status' => 'error', 'message' => 'Debes proporcionar al menos un valor para actualizar']);
    exit;
}

if ($nuevo_stock !== null && $nuevo_stock < 0) {
    echo json_encode(['status' => 'error', 'message' => 'El stock no puede ser negativo']);
    exit;
}

if ($nuevo_precio !== null && $nuevo_precio < 0) {
    echo json_encode(['status' => 'error', 'message' => 'El precio no puede ser negativo']);
    exit;
}

$pdo = conexion();

$stmt = $pdo->prepare("SELECT Cantidad, PrecioUnitario FROM Productos WHERE UPC = :codigo");
$stmt->execute([':codigo' => $codigo]);
$producto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$producto) {
    echo json_encode(['status' => 'error', 'message' => 'Producto no encontrado']);
    exit;
}

$stock_actual = $producto['Cantidad'];
$precio_actual = $producto['PrecioUnitario'];

$actualizaciones = [];
$mensaje_partes = [];

try {
    $pdo->beginTransaction();
    
    // Actualizar stock si se proporcionó
    if ($nuevo_stock !== null) {
        $upd_stock = $pdo->prepare("UPDATE Productos SET Cantidad = :nuevo WHERE UPC = :codigo");
        $upd_stock->execute([':nuevo' => $nuevo_stock, ':codigo' => $codigo]);
        
        $log = $pdo->prepare("INSERT INTO Logs_stock (UPC, StockBefore, StockAfter, UsuarioID) VALUES (:codigo, :anterior, :nuevo, :session)");
        $log->execute([
            ':codigo' => $codigo,
            ':anterior' => $stock_actual,
            ':nuevo' => $nuevo_stock,
            ':session' => $session_id
        ]);
        
        $mensaje_partes[] = 'stock';
    }
    
    // Actualizar precio si se proporcionó
    if ($nuevo_precio !== null) {
        $upd_precio = $pdo->prepare("UPDATE Productos SET PrecioUnitario = :nuevo WHERE UPC = :codigo");
        $upd_precio->execute([':nuevo' => $nuevo_precio, ':codigo' => $codigo]);
        
        // Intentar registrar log de precio (si existe la tabla)
        try {
            $log_precio = $pdo->prepare("INSERT INTO Logs_precio (UPC, PrecioBefore, PrecioAfter, UsuarioID, FechaCambio) VALUES (:codigo, :anterior, :nuevo, :session, NOW())");
            $log_precio->execute([
                ':codigo' => $codigo,
                ':anterior' => $precio_actual,
                ':nuevo' => $nuevo_precio,
                ':session' => $session_id
            ]);
        } catch (PDOException $e) {
            // Si la tabla no existe, continuar sin error
        }
        
        $mensaje_partes[] = 'precio';
    }
    
    $pdo->commit();
    
    $mensaje = '¡' . ucfirst(implode(' y ', $mensaje_partes)) . ' actualizado exitosamente!';
    echo json_encode(['status' => 'ok', 'message' => $mensaje]);
    
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Error al actualizar: ' . $e->getMessage()]);
}