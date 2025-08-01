<?php
header('Content-Type: application/json');

require_once "../../controllers/mainController.php";

try {
    $conexion = conexion();
    $stmt = $conexion->prepare("SELECT * FROM Categorias ORDER BY Nombre ASC");
    $stmt->execute();
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'categorias' => $categorias
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error al obtener categorias',
        'error' => $e->getMessage()
    ]);
}