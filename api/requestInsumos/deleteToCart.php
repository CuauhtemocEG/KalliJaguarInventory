<?php
session_start();
header('Content-Type: application/json');
require_once '../../controllers/mainController.php';
$conn = conexion();

$userId = $_SESSION['id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $idProducto = $data['idProduct'] ?? null;

    if (!$userId || !$idProducto) {
        echo json_encode(['status' => 'error', 'message' => 'Datos inválidos']);
        exit();
    }

    $stmt = $conn->prepare("DELETE FROM CarritoSolicitudes WHERE UsuarioID = ? AND ProductoID = ?");
    $stmt->execute([$userId, $idProducto]);

    echo json_encode(['status' => 'success', 'message' => 'Producto eliminado']);
    exit();
}