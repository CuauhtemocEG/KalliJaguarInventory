<?php
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['idProduct'];
    $precio = $_POST['precioProduct'];
    $cantidad = $_POST['cantidadProduct'];
    $nombre = $_POST['nameProduct'];
    $tipo = $_POST['typeProduct'];

    if (!isset($_SESSION['INV'])) {
        $_SESSION['INV'] = [];
    }

    //$productoExistente = false;
    //foreach ($_SESSION['INV'] as $key => $item) {
    //    if ($item['producto'] == $producto) {
    //        $_SESSION['INV'][$key]['cantidad'] += $cantidad;
    //        $productoExistente = true;
    //        break;
    //    }
    //}
    //if (!$productoExistente) {
    $_SESSION['INV'][$id] = [
        'producto' => $id,
        'precio' => $precio,
        'nombre' => $nombre,
        'cantidad' => $cantidad,
        'tipo' => $tipo
    ];
    //}

    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error']);
}
