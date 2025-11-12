<?php
require_once '../../controllers/mainController.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

try {
    $conn = conexion();
    
    $dateFrom = $_GET['dateFrom'] ?? null;
    $dateTo = $_GET['dateTo'] ?? null;
    $orderId = $_GET['orderId'] ?? null;
    $limit = intval($_GET['limit'] ?? 50);
    $offset = intval($_GET['offset'] ?? 0);
    
    $whereConditions = [];
    $params = [];
    
    if ($dateFrom) {
        $whereConditions[] = "pl.FechaPicking >= :dateFrom";
        $params[':dateFrom'] = $dateFrom . ' 00:00:00';
    }
    
    if ($dateTo) {
        $whereConditions[] = "pl.FechaPicking <= :dateTo";
        $params[':dateTo'] = $dateTo . ' 23:59:59';
    }
    
    if ($orderId) {
        $whereConditions[] = "pl.ComandaID LIKE :orderId";
        $params[':orderId'] = '%' . $orderId . '%';
    }
    
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    $query = "
        SELECT 
            pl.PickingLogID,
            pl.ComandaID,
            pl.UPC,
            pl.CantidadSolicitada,
            pl.CantidadPickeada,
            pl.FechaPicking,
            p.Nombre as ProductoNombre,
            p.Tipo as ProductoTipo,
            u.Nombre as UsuarioNombre,
            ec.EstadoNuevo
        FROM PickingLog pl
        LEFT JOIN Productos p ON pl.ProductoID = p.ProductoID
        LEFT JOIN Usuarios u ON pl.UsuarioID = u.UsuarioID
        LEFT JOIN EstadosComanda ec ON pl.ComandaID = ec.ComandaID 
            AND ec.EstadoNuevo = 'En Tránsito'
            AND DATE(ec.FechaCambio) = DATE(pl.FechaPicking)
        {$whereClause}
        ORDER BY pl.FechaPicking DESC
        LIMIT :limit OFFSET :offset
    ";
    
    $stmt = $conn->prepare($query);
    
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    $pickingHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $countQuery = "
        SELECT COUNT(*) as total
        FROM PickingLog pl
        {$whereClause}
    ";
    
    $countStmt = $conn->prepare($countQuery);
    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value);
    }
    $countStmt->execute();
    $totalRecords = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $statsQuery = "
        SELECT 
            COUNT(DISTINCT ComandaID) as totalOrders,
            COUNT(*) as totalItems,
            SUM(CantidadPickeada) as totalQuantity,
            DATE(MAX(FechaPicking)) as lastPickingDate
        FROM PickingLog pl
        {$whereClause}
    ";
    
    $statsStmt = $conn->prepare($statsQuery);
    foreach ($params as $key => $value) {
        $statsStmt->bindValue($key, $value);
    }
    $statsStmt->execute();
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $pickingHistory,
        'pagination' => [
            'total' => intval($totalRecords),
            'limit' => $limit,
            'offset' => $offset,
            'hasMore' => ($offset + $limit) < $totalRecords
        ],
        'stats' => $stats,
        'filters' => [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'orderId' => $orderId
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Error en getPickingHistory.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener historial: ' . $e->getMessage()
    ]);
}
