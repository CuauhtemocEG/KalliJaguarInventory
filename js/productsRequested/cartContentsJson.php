<?php
session_start();
header('Content-Type: application/json');

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
        $percentage = $item['precio'] * 1.16;
        $total += $item['cantidad'] * $percentage;

        $nombreImagen = !empty($item['imagen']) ? $item['imagen'] : 'producto.png';
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