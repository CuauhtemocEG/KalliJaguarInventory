<?php
header('Content-Type: application/json');
session_name("INV");
session_start();

require_once('../../controllers/mainController.php');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$sucursal_id = (int)($_GET['id'] ?? 0);

if (!$sucursal_id) {
    echo json_encode(['success' => false, 'message' => 'ID de sucursal requerido']);
    exit;
}

try {
    $pdo = conexion();
    
    $query = "
        SELECT 
            s.*,
            u1.Nombre as creado_por_nombre,
            u2.Nombre as actualizado_por_nombre,
            DATE_FORMAT(s.fecha_creacion, '%d/%m/%Y %H:%i') as fecha_creacion_formatted,
            DATE_FORMAT(s.fecha_actualizacion, '%d/%m/%Y %H:%i') as fecha_actualizacion_formatted,
            CASE 
                WHEN s.estado = 'activa' THEN 'Activa'
                WHEN s.estado = 'inactiva' THEN 'Inactiva'
                WHEN s.estado = 'mantenimiento' THEN 'Mantenimiento'
                ELSE COALESCE(s.estado, 'Sin definir')
            END as estado_texto,
            CASE 
                WHEN s.tipo = 'principal' THEN 'Principal'
                WHEN s.tipo = 'sucursal' THEN 'Sucursal'
                WHEN s.tipo = 'almacen' THEN 'Almacén'
                WHEN s.tipo = 'punto_venta' THEN 'Punto de Venta'
                ELSE COALESCE(s.tipo, 'Sin definir')
            END as tipo_texto
        FROM Sucursales s
        LEFT JOIN Usuarios u1 ON s.creado_por = u1.UsuarioID
        LEFT JOIN Usuarios u2 ON s.actualizado_por = u2.UsuarioID
        WHERE s.SucursalID = :sucursal_id
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':sucursal_id', $sucursal_id, PDO::PARAM_INT);
    $stmt->execute();
    $sucursal = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$sucursal) {
        echo json_encode(['success' => false, 'message' => 'Sucursal no encontrada']);
        exit;
    }
    
    $estadisticas = [
        'total_productos' => 0,
        'total_movimientos' => 0,
        'entradas' => 0,
        'salidas' => 0,
        'ultimo_movimiento' => null
    ];
    
    try {
        $productos_query = "SELECT COUNT(*) FROM Productos WHERE SucursalID = :sucursal_id";
        $productos_stmt = $pdo->prepare($productos_query);
        $productos_stmt->bindParam(':sucursal_id', $sucursal_id, PDO::PARAM_INT);
        $productos_stmt->execute();
        $estadisticas['total_productos'] = (int)$productos_stmt->fetchColumn();
    } catch (Exception $e) {
    }
    
    try {
        $movimientos_query = "
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN TipoMovimiento = 'Entrada' THEN 1 ELSE 0 END) as entradas,
                SUM(CASE WHEN TipoMovimiento = 'Salida' THEN 1 ELSE 0 END) as salidas,
                DATE(MAX(FechaMovimiento)) as ultimo_movimiento
            FROM MovimientosInventario 
            WHERE SucursalID = :sucursal_id
        ";
        $movimientos_stmt = $pdo->prepare($movimientos_query);
        $movimientos_stmt->bindParam(':sucursal_id', $sucursal_id, PDO::PARAM_INT);
        $movimientos_stmt->execute();
        $movimientos_result = $movimientos_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($movimientos_result) {
            $estadisticas['total_movimientos'] = (int)$movimientos_result['total'];
            $estadisticas['entradas'] = (int)$movimientos_result['entradas'];
            $estadisticas['salidas'] = (int)$movimientos_result['salidas'];
            $estadisticas['ultimo_movimiento'] = $movimientos_result['ultimo_movimiento'];
        }
    } catch (Exception $e) {
    }
    
    echo json_encode([
        'success' => true,
        'data' => $sucursal,
        'estadisticas' => $estadisticas
    ]);
    
} catch (PDOException $e) {
    error_log("Error getting sucursal details: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Error al obtener los detalles de la sucursal',
        'debug_error' => $e->getMessage()
    ]);
}
?>
