<?php
// La configuración de sesión se maneja en includes/session_start.php
session_name("INV");
session_start();

// Headers CORS para permitir solicitudes desde diferentes subdominios
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
    
    if (!$data || !$userId) {
        echo json_encode([
            'status' => 'error', 
            'message' => 'Datos inválidos',
            'debug' => [
                'data_present' => $data ? true : false,
                'user_id' => $userId,
                'session_id' => session_id(),
                'raw_input' => $rawInput,
                'parsed_data' => $data
            ]
        ]);
        exit();
    }

    $id = $data['idProduct'];
    $precio = $data['precio'];
    $cantidad = $data['cantidad'];
    $nombre = $data['nombre'];
    $tipo = $data['tipo'];
    $imagen = $data['imagen'] ?? null;

    $stmt = $conn->prepare("REPLACE INTO CarritoSolicitudes (UsuarioID, ProductoID, Cantidad, PrecioUnitario, NombreProducto, Tipo, Imagen) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $id, $cantidad, $precio, $nombre, $tipo, $imagen]);

    $cartStmt = $conn->prepare("SELECT * FROM CarritoSolicitudes WHERE UsuarioID = ?");
    $cartStmt->execute([$userId]);
    $cart = $cartStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'cart' => $cart]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!$userId) {
        echo json_encode(['status' => 'error', 'message' => 'Usuario no identificado']);
        exit();
    }
    $cartStmt = $conn->prepare("SELECT * FROM CarritoSolicitudes WHERE UsuarioID = ?");
    $cartStmt->execute([$userId]);
    $cart = $cartStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'cart' => $cart]);
    exit();
}
