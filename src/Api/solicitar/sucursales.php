<?php
header('Content-Type: application/json; charset=utf-8');
require_once BASE_PATH . '/src/Helpers/legacy.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$db = conexion();
$stmt = $db->query("SELECT SucursalID, nombre FROM Sucursales WHERE LOWER(COALESCE(estado, '')) NOT IN ('inactiva', 'inactivo') ORDER BY nombre");

echo json_encode(['success' => true, 'sucursales' => $stmt->fetchAll(PDO::FETCH_ASSOC)], JSON_UNESCAPED_UNICODE);
