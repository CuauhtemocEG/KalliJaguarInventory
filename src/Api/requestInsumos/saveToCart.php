<?php
\App\Core\Session::start();
header('Content-Type: application/json; charset=UTF-8');

require_once BASE_PATH . '/src/Helpers/legacy.php';

use App\Services\Solicitud;

$userId = (int) ($_SESSION['id'] ?? 0);
if ($userId <= 0) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Sesión no válida']);
    exit();
}

$conn = conexion();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $idProducto = filter_var($data['idProduct'] ?? null, FILTER_VALIDATE_INT);
    if (!is_array($data) || !$idProducto || $idProducto < 1) {
        http_response_code(422);
        echo json_encode(['status' => 'error', 'message' => 'Datos inválidos']);
        exit();
    }

    try {
        // Precio, nombre y tipo se toman de la base de datos, nunca del cliente
        Solicitud::fijarCantidad($conn, $userId, $idProducto, $data['cantidad'] ?? null);
    } catch (InvalidArgumentException $e) {
        http_response_code(422);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage(), 'cart' => Solicitud::carrito($conn, $userId)], JSON_UNESCAPED_UNICODE);
        exit();
    }
} elseif ($method !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit();
}

echo json_encode(['status' => 'success', 'cart' => Solicitud::carrito($conn, $userId)], JSON_UNESCAPED_UNICODE);
