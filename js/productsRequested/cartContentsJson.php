<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['INV']) && isset($_COOKIE['persist_cart'])) {
    $_SESSION['INV'] = json_decode($_COOKIE['persist_cart'], true);
}

$cart = isset($_SESSION['INV']) ? $_SESSION['INV'] : [];
$total = 0;
$totalItem = 0;
$productos = [];

foreach ($cart as $key => $item) {
    $totalItem += $item['cantidad'];
    $total += $item['cantidad'] * ($item['precio'] * 1.16);
    $productos[] = [
        'id'        => $key,
        'nombre'    => $item["nombre"],
        'cantidad'  => $item["cantidad"],
        'precio'    => $item["precio"],
        'tipo'      => $item["tipo"],
        'imagen'    => $item["imagen"],
        'precio_total' => $item["cantidad"] * $item["precio"] * 1.16
    ];
}

echo json_encode([
    'status'      => 'success',
    'cart'        => $productos,
    'total_items' => $totalItem,
    'total'       => $total
]);
?>