<?php
session_name("INV");
session_start();

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
header('Content-Type: application/json; charset=UTF-8');

// Manejar OPTIONS request para preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../../controllers/mainController.php';
$conn = conexion();

$userId = $_SESSION['id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);
    $idProducto = $data['idProduct'] ?? null;

    if (!$userId || !$idProducto) {
        echo json_encode([
            'status' => 'error', 
            'message' => 'Datos inválidos',
            'debug' => [
                'userId' => $userId,
                'idProducto' => $idProducto,
                'rawInput' => $rawInput,
                'decodedData' => $data
            ]
        ]);
        exit();
    }

    try {
        $stmt = $conn->prepare("DELETE FROM CarritoSolicitudes WHERE UsuarioID = ? AND ProductoID = ?");
        $result = $stmt->execute([$userId, $idProducto]);
        
        if ($result) {
            $affectedRows = $stmt->rowCount();
            if ($affectedRows > 0) {
                echo json_encode(['status' => 'success', 'message' => 'Producto eliminado del carrito']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Producto no encontrado en el carrito']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al eliminar producto']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error en la base de datos']);
    }
    exit();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit();
}