<?php
\App\Core\Session::start();
header('Content-Type: application/json; charset=UTF-8');

require_once BASE_PATH . '/src/Helpers/legacy.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit();
}

$userId = (int) ($_SESSION['id'] ?? 0);
$data = json_decode(file_get_contents('php://input'), true);
$idSucursal = filter_var($data['idSucursal'] ?? null, FILTER_VALIDATE_INT);
$fechaEntrega = is_string($data['fechaEntrega'] ?? null) ? $data['fechaEntrega'] : '';

if ($userId <= 0 || !$idSucursal || $fechaEntrega === '') {
    http_response_code(422);
    echo json_encode(['status' => 'error', 'message' => 'Selecciona la sucursal y la fecha de entrega']);
    exit();
}

require_once BASE_PATH . '/src/Helpers/maintenanceHelper.php';
$mantenimiento = getMaintenanceInfo();
if ($mantenimiento !== null) {
    logBlockedAccess($userId, $mantenimiento['reason'], 'confirmRequest');
    http_response_code(423);
    echo json_encode(['status' => 'error', 'message' => $mantenimiento['message'] . ' ' . $mantenimiento['reason_details']], JSON_UNESCAPED_UNICODE);
    exit();
}

try {
    $comandaId = \App\Services\Solicitud::confirmar(conexion(), $userId, $idSucursal, $fechaEntrega, TAX_MULTIPLIER);
    echo json_encode(['status' => 'success', 'message' => 'Solicitud confirmada correctamente', 'comandaID' => $comandaId]);
} catch (InvalidArgumentException $e) {
    http_response_code(422);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    error_log('[confirmRequest] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'No se pudo procesar la solicitud. Intenta de nuevo.']);
}
