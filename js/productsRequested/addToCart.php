<?php
session_start();
header('Content-Type: application/json');

// Admite tanto POST clásico como JSON desde fetch
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Primero intenta obtener los datos como JSON
    $data = json_decode(file_get_contents('php://input'), true);

    if ($data) {
        // Vía JSON (fetch/app movil)
        $id       = $data['id']       ?? null;
        $precio   = $data['precio']   ?? null;
        $cantidad = $data['cantidad'] ?? null;
        $nombre   = $data['nombre']   ?? null;
        $tipo     = $data['tipo']     ?? null;
        $imagen   = $data['imagen']   ?? null;
    } else {
        // Vía POST form clásico (web)
        $id       = $_POST['idProduct']      ?? null;
        $precio   = $_POST['precioProduct']  ?? null;
        $cantidad = $_POST['cantidadProduct']?? null;
        $nombre   = $_POST['nameProduct']    ?? null;
        $tipo     = $_POST['typeProduct']    ?? null;
        $imagen   = $_POST['imageProduct']   ?? null;
    }

    if (!$id || !$precio || !$cantidad || !$nombre || !$tipo) {
        echo json_encode(['status' => 'error', 'message' => 'Faltan datos']);
        exit;
    }

    if (!isset($_SESSION['INV'])) {
        $_SESSION['INV'] = [];
    }

    // Agrega o actualiza el producto en el carrito
    $_SESSION['INV'][$id] = [
        'producto' => $id,
        'precio'   => $precio,
        'nombre'   => $nombre,
        'cantidad' => $cantidad,
        'tipo'     => $tipo,
        'imagen'   => $imagen
    ];

    // Guarda el carrito en cookie persistente (para la web)
    setcookie("persist_cart", json_encode($_SESSION['INV']), time() + 604800, "/");

    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
}
?>