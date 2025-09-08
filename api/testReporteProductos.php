<?php
// Archivo de prueba para diagnosticar el problema
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../controllers/mainController.php';

try {
    echo json_encode(['status' => 'Iniciando prueba...']);
    
    $conexion = conexion();
    if (!$conexion) {
        throw new Exception('No se pudo establecer conexión con la base de datos');
    }
    
    // Primero probemos una consulta simple
    $queryTest = "SELECT COUNT(*) as total FROM Productos WHERE Tag IS NOT NULL";
    $stmtTest = $conexion->prepare($queryTest);
    $stmtTest->execute();
    $totalProductos = $stmtTest->fetch(PDO::FETCH_ASSOC);
    
    // Ahora probemos listar los tags disponibles
    $queryTags = "SELECT DISTINCT Tag FROM Productos WHERE Tag IS NOT NULL AND Tag != '' ORDER BY Tag";
    $stmtTags = $conexion->prepare($queryTags);
    $stmtTags->execute();
    $tags = $stmtTags->fetchAll(PDO::FETCH_COLUMN);
    
    // Ahora probemos una consulta de movimientos
    $queryMovimientos = "SELECT COUNT(*) as total FROM MovimientosInventario WHERE TipoMovimiento = 'Salida'";
    $stmtMov = $conexion->prepare($queryMovimientos);
    $stmtMov->execute();
    $totalMovimientos = $stmtMov->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'totalProductos' => $totalProductos,
        'tags' => $tags,
        'totalMovimientos' => $totalMovimientos,
        'parametros' => $_POST
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'file' => __FILE__,
        'line' => __LINE__
    ]);
}
?>
