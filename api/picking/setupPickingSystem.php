<?php
require_once "./controllers/mainController.php";

header('Content-Type: application/json');

try {
    $conn = conexion();
    
    $query = $conn->query("SELECT ProductoID, Nombre FROM Productos WHERE UPC IS NULL OR UPC = '' LIMIT 10");
    $products = $query->fetchAll(PDO::FETCH_ASSOC);
    
    $updated = 0;
    foreach ($products as $product) {
        $fakeUPC = '7501' . str_pad($product['ProductoID'], 8, '0', STR_PAD_LEFT);
        
        $updateQuery = $conn->prepare("UPDATE Productos SET UPC = :upc WHERE ProductoID = :id");
        $updateQuery->execute([
            ':upc' => $fakeUPC,
            ':id' => $product['ProductoID']
        ]);
        $updated++;
    }
    
    $statsQuery = $conn->query("
        SELECT 
            COUNT(*) as total_products,
            COUNT(CASE WHEN UPC IS NOT NULL AND UPC != '' THEN 1 END) as products_with_upc,
            COUNT(CASE WHEN Status = 'Abierto' THEN 1 END) as open_orders
        FROM Productos p
        LEFT JOIN MovimientosInventario mi ON p.ProductoID = mi.ProductoID
    ");
    $stats = $statsQuery->fetch(PDO::FETCH_ASSOC);
    
    $ordersQuery = $conn->query("
        SELECT DISTINCT ComandaID, COUNT(*) as product_count
        FROM MovimientosInventario 
        WHERE Status = 'Abierto' AND TipoMovimiento = 'Salida'
        GROUP BY ComandaID
        LIMIT 5
    ");
    $orders = $ordersQuery->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'message' => "Sistema de picking configurado correctamente",
        'updates' => [
            'upc_updated' => $updated,
            'total_products' => $stats['total_products'],
            'products_with_upc' => $stats['products_with_upc'],
            'open_orders' => count($orders)
        ],
        'sample_orders' => $orders,
        'notes' => [
            "Se agregaron códigos UPC fake a {$updated} productos para testing",
            "Hay " . count($orders) . " órdenes abiertas disponibles para picking",
            "Los códigos UPC generados siguen el formato: 7501XXXXXXXX"
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
