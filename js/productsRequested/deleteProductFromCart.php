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

if (isset($_POST['id'])) {
    $id = $_POST['id'];

    if (isset($_SESSION['INV'][$id])) {
        unset($_SESSION['INV'][$id]);
        echo json_encode(['status' => 'Success']);
    } else {
        echo json_encode(['status' => 'Not_Found']);
    }
} else {
    echo json_encode(['status' => 'Resquest Invalid']);
}
?>
