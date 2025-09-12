<?php
session_start();

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
