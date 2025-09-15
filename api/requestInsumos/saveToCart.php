<?php
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '.kallijaguar-inventory.com',  // Con punto inicial para incluir subdominios
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

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

// Debug para ver qué hay en la sesión
error_log("saveToCart - Sesión completa: " . print_r($_SESSION, true));
error_log("saveToCart - Usuario ID encontrado: " . ($userId ?? 'NULL'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    error_log("saveToCart - Datos recibidos: " . print_r($data, true));
    
    if (!$data || !$userId) {
        error_log("saveToCart - Error: datos=" . ($data ? 'OK' : 'NULL') . ", userId=" . ($userId ?? 'NULL'));
        echo json_encode(['status' => 'error', 'message' => 'Datos inválidos']);
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
