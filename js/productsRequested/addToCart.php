<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if ($data) {
        $id       = $data['id']       ?? null;
        $cantidad = $data['cantidad'] ?? null;
        $precio   = $data['precio']   ?? null;
        $nombre   = $data['nombre']   ?? null;
        $tipo     = $data['tipo']     ?? null;
        $imagen   = $data['imagen']   ?? null;
    } else {
        $id       = $_POST['id']       ?? null;
        $cantidad = $_POST['cantidad'] ?? null;
        $precio   = $_POST['precio']   ?? null;
        $nombre   = $_POST['nombre']   ?? null;
        $tipo     = $_POST['tipo']     ?? null;
        $imagen   = $_POST['imagen']   ?? null;
    }

    if (!$id || !$cantidad) {
        echo json_encode(['status' => 'error', 'message' => 'Faltan datos']);
        exit;
    }

    if (!isset($_SESSION['INV'])) $_SESSION['INV'] = [];

    if (isset($_SESSION['INV'][$id])) {
        $_SESSION['INV'][$id]['cantidad'] += $cantidad;
    } else {
        $_SESSION['INV'][$id] = [
            'producto' => $id,
            'precio'   => $precio,
            'nombre'   => $nombre,
            'cantidad' => $cantidad,
            'tipo'     => $tipo,
            'imagen'   => $imagen
        ];
    }

    setcookie("persist_cart", json_encode($_SESSION['INV']), time() + 604800, "/");
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
}
?>