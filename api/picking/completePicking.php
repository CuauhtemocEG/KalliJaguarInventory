<?php
require_once '../../controllers/mainController.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['orderId']) || !isset($input['pickedProducts'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

$orderId = $input['orderId'];
$pickedProducts = $input['pickedProducts'];

$conn = null;
$transactionStarted = false;

try {
    $conn = conexion();
    
    $checkQuery = $conn->prepare("
        SELECT COUNT(*) as count, Status 
        FROM MovimientosInventario 
        WHERE ComandaID = :orderId 
        AND TipoMovimiento = 'Salida'
        AND Status = 'Abierto'
    ");
    $checkQuery->execute([':orderId' => $orderId]);
    $orderCheck = $checkQuery->fetch(PDO::FETCH_ASSOC);
    
    if (!$orderCheck || $orderCheck['count'] == 0) {
        $statusCheckQuery = $conn->prepare("
            SELECT Status, COUNT(*) as count 
            FROM MovimientosInventario 
            WHERE ComandaID = :orderId 
            AND TipoMovimiento = 'Salida'
            GROUP BY Status
        ");
        $statusCheckQuery->execute([':orderId' => $orderId]);
        $statusCheck = $statusCheckQuery->fetch(PDO::FETCH_ASSOC);
        
        if (!$statusCheck || $statusCheck['count'] == 0) {
            throw new Exception("La orden {$orderId} no existe");
        } else {
            throw new Exception("La orden {$orderId} no está en estado 'Abierto'. Estado actual: {$statusCheck['Status']}");
        }
    }
    
    $conn->beginTransaction();
    $transactionStarted = true;
    
    $productsQuery = $conn->prepare("
        SELECT 
            mi.MovimientoID,
            mi.ProductoID,
            mi.Cantidad as CantidadSolicitada,
            p.UPC,
            p.Nombre,
            p.Tipo
        FROM MovimientosInventario mi
        JOIN Productos p ON mi.ProductoID = p.ProductoID
        WHERE mi.ComandaID = :orderId 
        AND mi.TipoMovimiento = 'Salida'
    ");
    $productsQuery->execute([':orderId' => $orderId]);
    $orderProducts = $productsQuery->fetchAll(PDO::FETCH_ASSOC);
    
    $validationErrors = [];
    $pickingLog = [];
    
    foreach ($orderProducts as $product) {
        $upc = $product['UPC'] ?: 'SIN-UPC-' . $product['ProductoID'];
        
        if (!isset($pickedProducts[$upc])) {
            $validationErrors[] = "Producto '{$product['Nombre']}' no fue pickeado";
            continue;
        }
        
        $picked = $pickedProducts[$upc];
        
        if (!$picked['completed']) {
            $validationErrors[] = "Producto '{$product['Nombre']}' no fue completado";
            continue;
        }
        
        $cantidadSolicitada = floatval($product['CantidadSolicitada']);
        $cantidadPickeada = floatval($picked['scanned']);
        
        if ($product['Tipo'] === 'Pesable') {
            if ($cantidadPickeada <= 0) {
                $validationErrors[] = "Producto '{$product['Nombre']}' no tiene cantidad válida";
                continue;
            }
        } else {
            $cantidadSolicitadaInt = intval($cantidadSolicitada);
            $cantidadPickeadaInt = intval($cantidadPickeada);
            
            if ($cantidadPickeadaInt < $cantidadSolicitadaInt) {
                $validationErrors[] = "Producto '{$product['Nombre']}' incompleto: {$cantidadPickeadaInt} de {$cantidadSolicitadaInt}";
                continue;
            }
        }
        
        $pickingLog[] = [
            'MovimientoID' => $product['MovimientoID'],
            'ProductoID' => $product['ProductoID'],
            'UPC' => $upc,
            'Nombre' => $product['Nombre'],
            'Tipo' => $product['Tipo'],
            'CantidadSolicitada' => $cantidadSolicitada,
            'CantidadPickeada' => $cantidadPickeada
        ];
    }
    
    if (!empty($validationErrors)) {
        throw new Exception('Errores de validación: ' . implode(', ', $validationErrors));
    }
    
    $tableExistsQuery = $conn->query("SHOW TABLES LIKE 'PickingLog'");
    if ($tableExistsQuery->rowCount() == 0) {
        $createTableQuery = "
            CREATE TABLE IF NOT EXISTS PickingLog (
                PickingLogID INT AUTO_INCREMENT PRIMARY KEY,
                ComandaID VARCHAR(50) NOT NULL,
                ProductoID INT NOT NULL,
                UPC VARCHAR(50),
                CantidadSolicitada DECIMAL(10,3) NOT NULL,
                CantidadPickeada DECIMAL(10,3) NOT NULL,
                FechaPicking DATETIME NOT NULL,
                UsuarioID INT NOT NULL,
                INDEX idx_comanda (ComandaID),
                INDEX idx_fecha (FechaPicking)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        $conn->exec($createTableQuery);
        
        if ($transactionStarted) {
            $conn->beginTransaction();
        }
    }
    
    $insertPickingLogQuery = $conn->prepare("
        INSERT INTO PickingLog (ComandaID, ProductoID, UPC, CantidadSolicitada, CantidadPickeada, FechaPicking, UsuarioID)
        VALUES (:comandaId, :productoId, :upc, :cantidadSolicitada, :cantidadPickeada, NOW(), :usuarioId)
    ");
    
    $currentUserId = $_SESSION['id'] ?? 1;
    
    foreach ($pickingLog as $log) {
        $insertPickingLogQuery->execute([
            ':comandaId' => $orderId,
            ':productoId' => $log['ProductoID'],
            ':upc' => $log['UPC'],
            ':cantidadSolicitada' => $log['CantidadSolicitada'],
            ':cantidadPickeada' => $log['CantidadPickeada'],
            ':usuarioId' => $currentUserId
        ]);
    }
    
    $updateStatusQuery = $conn->prepare("
        UPDATE MovimientosInventario 
        SET Status = 'En Tránsito'
        WHERE ComandaID = :orderId 
        AND TipoMovimiento = 'Salida'
    ");
    $updateStatusQuery->execute([':orderId' => $orderId]);
    
    $tableEstadosExistsQuery = $conn->query("SHOW TABLES LIKE 'EstadosComanda'");
    if ($tableEstadosExistsQuery->rowCount() == 0) {
        $createEstadosTableQuery = "
            CREATE TABLE IF NOT EXISTS EstadosComanda (
                EstadoComandaID INT AUTO_INCREMENT PRIMARY KEY,
                ComandaID VARCHAR(50) NOT NULL,
                EstadoAnterior VARCHAR(20) NOT NULL,
                EstadoNuevo VARCHAR(20) NOT NULL,
                FechaCambio DATETIME NOT NULL,
                UsuarioID INT NOT NULL,
                Observaciones TEXT,
                INDEX idx_comanda (ComandaID),
                INDEX idx_fecha (FechaCambio)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        $conn->exec($createEstadosTableQuery);
        
        if ($transactionStarted) {
            $conn->beginTransaction();
        }
    }
    
    $insertEstadoQuery = "
        INSERT INTO EstadosComanda (ComandaID, EstadoAnterior, EstadoNuevo, FechaCambio, UsuarioID, Observaciones)
        VALUES (?, 'Abierto', 'En Tránsito', NOW(), ?, 'Picking completado exitosamente')
    ";
    
    $insertEstadoStmt = $conn->prepare($insertEstadoQuery);
    $insertEstadoStmt->execute([$orderId, $currentUserId]);
    
    $conn->commit();
    $transactionStarted = false;
    
    echo json_encode([
        'success' => true,
        'message' => 'Picking completado exitosamente',
        'orderId' => $orderId,
        'pickedItems' => count($pickingLog),
        'newStatus' => 'En Tránsito'
    ]);
    
} catch (Exception $e) {
    if ($conn) {
        try {
            if ($conn->inTransaction()) {
                $conn->rollback();
                error_log("Rollback ejecutado correctamente");
            }
        } catch (Exception $rollbackException) {
            error_log("Error en rollback: " . $rollbackException->getMessage());
        }
    }
    
    error_log("Error en completePicking.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al completar picking: ' . $e->getMessage(),
        'debug' => [
            'transactionStarted' => $transactionStarted,
            'connectionExists' => ($conn !== null),
            'inTransaction' => ($conn && $conn->inTransaction())
        ]
    ]);
}
