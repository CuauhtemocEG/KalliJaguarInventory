<?php
\App\Core\Session::start();
header('Content-Type: application/json; charset=UTF-8');

require_once BASE_PATH . '/src/Helpers/legacy.php';

$userId = (int) ($_SESSION['id'] ?? 0);
if ($userId <= 0) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Usuario no identificado']);
    exit();
}

echo json_encode(['status' => 'success', 'cart' => \App\Services\Solicitud::carrito(conexion(), $userId)], JSON_UNESCAPED_UNICODE);
