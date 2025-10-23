<?php
header('Content-Type: application/json');
session_start();

require_once('../../controllers/mainController.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$sucursal_id = (int)($_POST['sucursal_id'] ?? 0);

if (!$sucursal_id) {
    echo json_encode(['success' => false, 'message' => 'ID de sucursal requerido']);
    exit;
}

try {
    $pdo = conexion();
    
    $check_query = "SELECT SucursalID, nombre, estado FROM Sucursales WHERE SucursalID = :sucursal_id";
    $check_stmt = $pdo->prepare($check_query);
    $check_stmt->bindParam(':sucursal_id', $sucursal_id, PDO::PARAM_INT);
    $check_stmt->execute();
    $sucursal = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$sucursal) {
        echo json_encode(['success' => false, 'message' => 'Sucursal no encontrada']);
        exit;
    }
    
    $products_query = "SELECT COUNT(*) as total FROM Productos WHERE SucursalID = :sucursal_id";
    $products_stmt = $pdo->prepare($products_query);
    $products_stmt->bindParam(':sucursal_id', $sucursal_id, PDO::PARAM_INT);
    $products_stmt->execute();
    $products_count = $products_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $movements_query = "SELECT COUNT(*) as total FROM MovimientosInventario WHERE SucursalID = :sucursal_id";
    $movements_stmt = $pdo->prepare($movements_query);
    $movements_stmt->bindParam(':sucursal_id', $sucursal_id, PDO::PARAM_INT);
    $movements_stmt->execute();
    $movements_count = $movements_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    if ($products_count > 0 || $movements_count > 0) {
        $update_query = "
            UPDATE Sucursales SET 
                estado = 'inactiva',
                fecha_actualizacion = NOW(),
                actualizado_por = :actualizado_por
            WHERE SucursalID = :sucursal_id
        ";
        
        $actualizado_por = $_SESSION['id'] ?? null;
        
        $update_stmt = $pdo->prepare($update_query);
        $update_stmt->bindParam(':actualizado_por', $actualizado_por, PDO::PARAM_INT);
        $update_stmt->bindParam(':sucursal_id', $sucursal_id, PDO::PARAM_INT);
        
        if ($update_stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Sucursal desactivada (tiene productos o movimientos asociados)',
                'action' => 'deactivated',
                'sucursal_name' => $sucursal['nombre'],
                'products_count' => $products_count,
                'movements_count' => $movements_count
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al desactivar la sucursal']);
        }
    } else {
        $delete_query = "DELETE FROM Sucursales WHERE SucursalID = :sucursal_id";
        $delete_stmt = $pdo->prepare($delete_query);
        $delete_stmt->bindParam(':sucursal_id', $sucursal_id, PDO::PARAM_INT);
        
        if ($delete_stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Sucursal eliminada exitosamente',
                'action' => 'deleted',
                'sucursal_name' => $sucursal['nombre']
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar la sucursal']);
        }
    }
    
} catch (PDOException $e) {
    error_log("Error deleting sucursal: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error de base de datos']);
}
?>
