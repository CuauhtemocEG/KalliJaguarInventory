<?php
require_once '../../controllers/mainController.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

if (!isset($_GET['orderId']) || empty($_GET['orderId'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Falta el parámetro orderId']);
    exit;
}

$orderId = $_GET['orderId'];

try {
    $conn = conexion();
    
    $checkQuery = $conn->prepare("
        SELECT COUNT(*) as count, Status 
        FROM MovimientosInventario 
        WHERE ComandaID = :orderId 
        AND TipoMovimiento = 'Salida'
        GROUP BY Status
    ");
    $checkQuery->execute([':orderId' => $orderId]);
    $orderCheck = $checkQuery->fetch(PDO::FETCH_ASSOC);
    
    if (!$orderCheck || $orderCheck['Status'] !== 'Abierto') {
        echo json_encode([
            'success' => false, 
            'message' => 'La orden no existe o no está en estado abierto'
        ]);
        exit;
    }
    
    $query = $conn->prepare("
        SELECT 
            mi.MovimientoID,
            mi.ProductoID,
            mi.Cantidad,
            mi.PrecioFinal,
            p.UPC,
            p.Nombre,
            p.Descripcion,
            p.Tipo,
            p.PrecioUnitario,
            p.image
        FROM MovimientosInventario mi
        JOIN Productos p ON mi.ProductoID = p.ProductoID
        WHERE mi.ComandaID = :orderId 
        AND mi.TipoMovimiento = 'Salida'
        ORDER BY p.Nombre ASC
    ");
    
    $query->execute([':orderId' => $orderId]);
    $products = $query->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($products)) {
        echo json_encode([
            'success' => false, 
            'message' => 'No se encontraron productos para esta orden'
        ]);
        exit;
    }
    
    $formattedProducts = [];
    foreach ($products as $product) {
        $formattedProducts[] = [
            'MovimientoID' => $product['MovimientoID'],
            'ProductoID' => $product['ProductoID'],
            'UPC' => $product['UPC'] ?: 'SIN-UPC-' . $product['ProductoID'],
            'Nombre' => $product['Nombre'],
            'Descripcion' => $product['Descripcion'],
            'Tipo' => $product['Tipo'],
            'Cantidad' => floatval($product['Cantidad']),
            'PrecioUnitario' => floatval($product['PrecioUnitario']),
            'PrecioFinal' => floatval($product['PrecioFinal']),
            'Image' => $product['image']
        ];
    }
    
    $orderInfoQuery = $conn->prepare("
        SELECT DISTINCT
            s.nombre AS SucursalNombre,
            u.Nombre AS Solicitante,
            mi.FechaMovimiento,
            mi.FechaDelivery
        FROM MovimientosInventario mi
        JOIN Sucursales s ON mi.SucursalID = s.SucursalID
        JOIN Usuarios u ON mi.UsuarioID = u.UsuarioID
        WHERE mi.ComandaID = :orderId
        AND mi.TipoMovimiento = 'Salida'
        LIMIT 1
    ");
    
    $orderInfoQuery->execute([':orderId' => $orderId]);
    $orderInfo = $orderInfoQuery->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'orderId' => $orderId,
        'orderInfo' => $orderInfo,
        'products' => $formattedProducts,
        'totalProducts' => count($formattedProducts)
    ]);
    
} catch (Exception $e) {
    error_log("Error en getOrderProducts.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor: ' . $e->getMessage()
    ]);
}
