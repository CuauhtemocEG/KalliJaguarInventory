<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../controllers/mainController.php';

try {
    $conexion = conexion();
    
    $query = "SELECT Nombre FROM Tags WHERE Activo = 1 ORDER BY Nombre";
    $stmt = $conexion->prepare($query);
    $stmt->execute();
    $tags = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo json_encode([
        'success' => true,
        'tags' => $tags
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Error al obtener los tags: ' . $e->getMessage()
    ]);
}
?>
