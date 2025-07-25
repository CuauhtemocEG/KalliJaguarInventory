<?php
session_start();
header('Content-Type: application/json');
$id = $_POST['id'] ?? null;

if ($id && isset($_SESSION['INV'][$id])) {
    unset($_SESSION['INV'][$id]);
    setcookie("persist_cart", json_encode($_SESSION['INV']), time() + 604800, "/");
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'No existe el producto']);
}
?>