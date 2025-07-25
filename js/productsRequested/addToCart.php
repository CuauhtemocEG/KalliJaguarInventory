<?php
session_start();
header('Content-Type: application/json');

// Permite POST clásico (web) y JSON (móvil)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Intenta obtener los datos como JSON
    $data = json_decode(file_get_contents('php://input'), true);

    if ($data) {
        $id       = $data['id']       ?? null;
        $precio   = $data['precio']   ?? null;
        $cantidad = $data['cantidad'] ?? null;
        $nombre   = $data['nombre']   ?? null;
        $tipo     = $data['tipo']     ?? null;
        $imagen   = $data['imagen']   ?? null;
    } else {
        $id       = $_POST['idProduct']       ?? null;
        $precio   = $_POST['precioProduct']   ?? null;
        $cantidad = $_POST['cantidadProduct'] ?? null;
        $nombre   = $_POST['nameProduct']     ?? null;
        $tipo     = $_POST['typeProduct']     ?? null;
        $imagen   = $_POST['imageProduct']    ?? null;
    }

    if (!$id || !$precio || !$cantidad || !$nombre || !$tipo) {
        echo json_encode(['status' => 'error', 'message' => 'Faltan datos']);
        exit;
    }

    if (!isset($_SESSION['INV'])) {
        $_SESSION['INV'] = [];
    }

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
