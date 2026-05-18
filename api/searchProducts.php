<?php
require '../controllers/mainController.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit;
}

$query = limpiar_cadena($_GET['q'] ?? '');

if (strlen($query) < 2) {
    echo json_encode(['status' => 'error', 'message' => 'La búsqueda debe tener al menos 2 caracteres']);
    exit;
}

$pdo = conexion();

try {
    $stmt = $pdo->prepare("
        SELECT 
            UPC, 
            Nombre, 
            Tipo, 
            Cantidad, 
            PrecioUnitario,
            image AS Imagen
        FROM Productos 
        WHERE Nombre LIKE :query 
           OR UPC LIKE :query
        ORDER BY Nombre ASC
        LIMIT 20
    ");
    
    $searchTerm = '%' . $query . '%';
    $stmt->execute([':query' => $searchTerm]);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'ok', 
        'productos' => $productos,
        'total' => count($productos)
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Error al buscar productos: ' . $e->getMessage()
    ]);
}
