<?php
session_start();
header('Content-Type: application/json');
error_log('SESION CONSULTA: ' . session_id());
error_log('CARRITO CONSULTA: ' . print_r($_SESSION['INV'], true));

// Recupera el carrito desde la sesión o la cookie persistente
if (!isset($_SESSION['INV']) && isset($_COOKIE['persist_cart'])) {
    $_SESSION['INV'] = json_decode($_COOKIE['persist_cart'], true);
}

$cart = isset($_SESSION['INV']) ? $_SESSION['INV'] : [];

if (count($cart) > 0) {
    $total = 0;
    $totalItem = 0;
    $productos = [];

    foreach ($cart as $key => $item) {
        $unidadesRes = '';
        $res = 0;

        if ($item['tipo'] == "Pesable") {
            if ($item['cantidad'] >= 1.0) {
                $unidadesRes = 'Kg';
                $res = number_format($item["cantidad"], 3);
            } else {
                $unidadesRes = 'grs';
                $res = number_format($item["cantidad"], 3);
            }
        } else {
            $unidadesRes = 'Un';
            $res = number_format($item["cantidad"], 0);
        }

        $totalItem += $item['cantidad'];
        $percentage = $item['precio'] * (1 + 0.16);
        $total += $item['cantidad'] * $percentage;

        // Construye la URL de la imagen (opcional, ajústalo si tienes imagen en tus items)
        $nombreImagen = !empty($item['imagen']) && is_file("../../img/producto/" . $item['imagen'])
            ? $item['imagen']
            : 'producto.png';
        $urlImagen = 'https://stagging.kallijaguar-inventory.com/img/producto/' . $nombreImagen;

        $productos[] = [
            'id'        => $key,
            'nombre'    => $item["nombre"],
            'cantidad'  => $item["cantidad"],
            'cantidad_formateada' => $res,
            'unidad'    => $unidadesRes,
            'precio'    => $item["precio"],
            'precio_total' => $item["precio"] * $item["cantidad"],
            'tipo'      => $item["tipo"],
            'imagen'    => $urlImagen
        ];
    }

    echo json_encode([
        'status'      => 'success',
        'cart'        => $productos,
        'total_items' => $totalItem,
        'total'       => $total
    ]);
    exit;
} else {
    echo json_encode([
        'status' => 'success',
        'cart'   => [],
        'total_items' => 0,
        'total'  => 0.0
    ]);
    exit;
}
?>