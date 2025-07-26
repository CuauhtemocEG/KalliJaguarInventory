<?php
session_start();
header('Content-Type: application/json');
require_once '../../controllers/mainController.php';
$conn = conexion();

$userId = $_SESSION['id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || !$userId) {
        echo json_encode(['status' => 'error', 'message' => 'Datos inválidos']);
        exit();
    }

    $id = $data['idProduct'];
    $precio = $data['precio'];
    $cantidad = $data['cantidad'];
    $nombre = $data['nombre'];
    $tipo = $data['tipo'];

    // Guardar en una tabla temporal de carrito
    $stmt = $conn->prepare("REPLACE INTO CarritoSolicitudes (UsuarioID, ProductoID, Cantidad, PrecioUnitario, NombreProducto, Tipo) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $id, $cantidad, $precio, $nombre, $tipo]);

    // Consultar el carrito actualizado
    $cartStmt = $conn->prepare("SELECT * FROM CarritoSolicitudes WHERE UsuarioID = ?");
    $cartStmt->execute([$userId]);
    $cart = $cartStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'cart' => $cart]);
    exit();
}

// GET para consultar el carrito
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