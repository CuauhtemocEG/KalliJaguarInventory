<?php
header('Content-Type: application/json; charset=utf-8');
require_once BASE_PATH . '/src/Helpers/legacy.php';
function sucursalesRespuesta(int $code, array $data): void { http_response_code($code); echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE); exit; }
function sucursalesError(string $message, int $code = 422): void { sucursalesRespuesta($code, ['success' => false, 'message' => $message]); }
try {
    $pdo = conexion(); $method = $_SERVER['REQUEST_METHOD'];
    if ($method === 'GET') {
        $where = []; $params = []; $q = trim((string)($_GET['q'] ?? ''));
        if ($q !== '') { $where[] = '(nombre LIKE ? OR direccion LIKE ? OR gerente LIKE ?)'; $params = array_fill(0, 3, '%' . $q . '%'); }
        foreach (['estado' => ['activa','inactiva','mantenimiento'], 'tipo' => ['principal','sucursal','almacen','punto_venta']] as $field => $allowed) {
            $value = (string)($_GET[$field] ?? ''); if ($value !== '') { if (!in_array($value, $allowed, true)) sucursalesError('Filtro inválido.'); $where[] = "$field = ?"; $params[] = $value; }
        }
        $stmt = $pdo->prepare('SELECT SucursalID, nombre, direccion, telefono, email, gerente, tipo, estado, horario_apertura, horario_cierre, notas FROM Sucursales' . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY nombre, SucursalID'); $stmt->execute($params);
        sucursalesRespuesta(200, ['success' => true, 'sucursales' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }
    if (!in_array($method, ['POST','PUT','DELETE'], true)) sucursalesError('Método no permitido.', 405);
    $input = json_decode(file_get_contents('php://input'), true); if (!is_array($input)) sucursalesError('JSON inválido.', 400);
    $id = filter_var($input['id'] ?? null, FILTER_VALIDATE_INT);
    if ($method !== 'POST') {
        if (!$id || $id < 1) sucursalesError('Sucursal inválida.');
        $stmt = $pdo->prepare('SELECT SucursalID FROM Sucursales WHERE SucursalID = ?'); $stmt->execute([$id]); if (!$stmt->fetchColumn()) sucursalesError('Sucursal no encontrada.', 404);
    }
    if ($method === 'DELETE') {
        $pdo->beginTransaction(); $stmt = $pdo->prepare('SELECT SucursalID FROM Sucursales WHERE SucursalID = ? FOR UPDATE'); $stmt->execute([$id]);
        $stmt = $pdo->prepare('SELECT 1 FROM MovimientosInventario WHERE SucursalID = ? LIMIT 1'); $stmt->execute([$id]);
        if ($stmt->fetchColumn()) { $pdo->rollBack(); sucursalesError('La sucursal tiene pedidos o movimientos; desactívala en su lugar.', 409); }
        $stmt = $pdo->prepare('DELETE FROM Sucursales WHERE SucursalID = ?'); $stmt->execute([$id]); $pdo->commit(); sucursalesRespuesta(200, ['success' => true, 'message' => 'Sucursal eliminada.']);
    }
    foreach (['nombre','direccion','telefono','email','gerente','tipo','estado','horario_apertura','horario_cierre','notas'] as $field) { if (isset($input[$field]) && !is_string($input[$field])) sucursalesError('Datos inválidos.'); $$field = trim($input[$field] ?? ''); }
    foreach (['nombre' => 120, 'direccion' => 255, 'telefono' => 30, 'email' => 150, 'gerente' => 120, 'notas' => 65535] as $field => $max) { if (strlen($$field) > $max) sucursalesError('El campo ' . $field . ' es demasiado largo.'); }
    if ($nombre === '' || ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL))) sucursalesError('Nombre obligatorio o correo inválido.');
    if (!in_array($tipo, ['principal','sucursal','almacen','punto_venta'], true) || !in_array($estado, ['activa','inactiva','mantenimiento'], true)) sucursalesError('Tipo o estado inválido.');
    foreach ([$horario_apertura, $horario_cierre] as $time) if ($time !== '' && !preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9](?::[0-5][0-9])?$/D', $time)) sucursalesError('Horario inválido.');
    $stmt = $pdo->prepare('SELECT SucursalID FROM Sucursales WHERE nombre = ? AND SucursalID <> ?'); $stmt->execute([$nombre, $id ?: 0]); if ($stmt->fetchColumn()) sucursalesError('Ya existe una sucursal con ese nombre.', 409);
    $values = [$nombre, $direccion ?: null, $telefono ?: null, $email ?: null, $gerente ?: null, $tipo, $estado, $horario_apertura ?: null, $horario_cierre ?: null, $notas ?: null];
    if ($method === 'POST') { $stmt = $pdo->prepare('INSERT INTO Sucursales (nombre, direccion, telefono, email, gerente, tipo, estado, horario_apertura, horario_cierre, notas, fecha_creacion, creado_por) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)'); $values[] = $_SESSION['id']; }
    else { $stmt = $pdo->prepare('UPDATE Sucursales SET nombre = ?, direccion = ?, telefono = ?, email = ?, gerente = ?, tipo = ?, estado = ?, horario_apertura = ?, horario_cierre = ?, notas = ?, fecha_actualizacion = NOW() WHERE SucursalID = ?'); $values[] = $id; }
    $stmt->execute($values); sucursalesRespuesta($method === 'POST' ? 201 : 200, ['success' => true, 'message' => 'Sucursal guardada.']);
} catch (Throwable $e) { if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack(); error_log('Sucursales: ' . $e->getMessage()); sucursalesError('No se pudo completar la operación.', 500); }
