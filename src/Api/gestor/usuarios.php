<?php
header('Content-Type: application/json; charset=utf-8');
require_once BASE_PATH . '/src/Helpers/legacy.php';
function usuariosRespuesta(int $code, array $data): void { http_response_code($code); echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE); exit; }
function usuariosError(string $message, int $code = 422): void { usuariosRespuesta($code, ['success' => false, 'message' => $message]); }
try {
    $pdo = conexion();
    $method = $_SERVER['REQUEST_METHOD'];
    if ($method === 'GET') {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (($_GET['action'] ?? '') === 'actividad') {
            if (!$id || $id < 1) usuariosError('Usuario inválido.');
            $stmt = $pdo->prepare('SELECT UsuarioID, Nombre, ultimo_acceso, ip_ultimo_acceso, fecha_creacion, fecha_ultima_modificacion FROM Usuarios WHERE UsuarioID = ?');
            $stmt->execute([$id]); $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$user) usuariosError('Usuario no encontrado.', 404);
            $stmt = $pdo->prepare('SELECT MovimientoID, ComandaID, TipoMovimiento, Cantidad, FechaMovimiento, Status FROM MovimientosInventario WHERE UsuarioID = ? ORDER BY FechaMovimiento DESC, MovimientoID DESC LIMIT 50');
            $stmt->execute([$id]);
            usuariosRespuesta(200, ['success' => true, 'usuario' => $user, 'movimientos' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        }
        $where = []; $params = [];
        $q = trim((string)($_GET['q'] ?? ''));
        if ($q !== '') { $where[] = '(Nombre LIKE ? OR Username LIKE ? OR email LIKE ?)'; $params = array_fill(0, 3, '%' . $q . '%'); }
        foreach (['rol' => ['Rol', ['Administrador','Logistica','Supervisor']], 'estado' => ['estado', ['activo','inactivo','suspendido']]] as $key => [$column, $allowed]) {
            $value = (string)($_GET[$key] ?? '');
            if ($value !== '') { if (!in_array($value, $allowed, true)) usuariosError('Filtro inválido.'); $where[] = "$column = ?"; $params[] = $value; }
        }
        $sql = 'SELECT UsuarioID, Nombre, Username, email, Rol, estado, telefono, ultimo_acceso FROM Usuarios' . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY Nombre, UsuarioID';
        $stmt = $pdo->prepare($sql); $stmt->execute($params);
        usuariosRespuesta(200, ['success' => true, 'usuarios' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }
    if (!in_array($method, ['POST','PUT','DELETE'], true)) usuariosError('Método no permitido.', 405);
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) usuariosError('JSON inválido.', 400);
    $id = filter_var($input['id'] ?? null, FILTER_VALIDATE_INT); $action = $input['action'] ?? 'guardar';
    if ($method !== 'POST' && (!$id || $id < 1)) usuariosError('Usuario inválido.');
    if ($method !== 'POST') {
        $stmt = $pdo->prepare('SELECT UsuarioID, estado FROM Usuarios WHERE UsuarioID = ?'); $stmt->execute([$id]); $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$existing) usuariosError('Usuario no encontrado.', 404);
    }
    $self = $id && (int)$id === (int)($_SESSION['id'] ?? 0);
    if ($method === 'DELETE') {
        if ($self) usuariosError('No puedes eliminar tu propia cuenta.', 403);
        $pdo->beginTransaction();
        $stmt = $pdo->prepare('SELECT UsuarioID FROM Usuarios WHERE UsuarioID = ? FOR UPDATE'); $stmt->execute([$id]);
        foreach (['MovimientosInventario', 'Productos', 'Logs_stock', 'Logs_precio', 'PickingLog', 'EstadosComanda', 'CarritoSolicitudes', 'MaintenanceConfigLog'] as $table) {
            $stmt = $pdo->prepare("SELECT 1 FROM $table WHERE UsuarioID = ? LIMIT 1"); $stmt->execute([$id]);
            if ($stmt->fetchColumn()) { $pdo->rollBack(); usuariosError('El usuario tiene movimientos o registros asociados; desactívalo en su lugar.', 409); }
        }
        $stmt = $pdo->prepare('DELETE FROM Usuarios WHERE UsuarioID = ?'); $stmt->execute([$id]); $pdo->commit();
        usuariosRespuesta(200, ['success' => true, 'message' => 'Usuario eliminado.']);
    }
    if ($method === 'PUT' && $action === 'password') {
        $password = $input['password'] ?? null;
        if (!is_string($password) || preg_match_all('/./us', $password) < max(1, (int)config('app.security.password_min_length', 8)) || strlen($password) > 72) usuariosError('La contraseña no cumple la longitud permitida (máximo 72 bytes).');
        $stmt = $pdo->prepare('UPDATE Usuarios SET Password = ?, fecha_ultimo_cambio_password = NOW(), token_reset_password = NULL, token_reset_expira = NULL, intentos_fallidos = 0, modificado_por = ?, fecha_ultima_modificacion = NOW() WHERE UsuarioID = ?');
        $stmt->execute([password_hash($password, PASSWORD_DEFAULT), $_SESSION['id'], $id]);
        usuariosRespuesta(200, ['success' => true, 'message' => 'Contraseña restablecida.']);
    }
    if ($method === 'PUT' && $action === 'estado') {
        $state = $input['estado'] ?? '';
        if (!in_array($state, ['activo','inactivo','suspendido'], true)) usuariosError('Estado inválido.');
        if ($self && $state !== 'activo') usuariosError('No puedes desactivar tu propia cuenta.', 403);
        $stmt = $pdo->prepare('UPDATE Usuarios SET estado = ?, modificado_por = ?, fecha_ultima_modificacion = NOW() WHERE UsuarioID = ?'); $stmt->execute([$state, $_SESSION['id'], $id]);
        usuariosRespuesta(200, ['success' => true, 'message' => 'Estado actualizado.']);
    }
    if ($action !== 'guardar' || !in_array($method, ['POST','PUT'], true)) usuariosError('Acción inválida.', 400);
    foreach (['nombre','username','email','rol','estado','telefono'] as $field) { if (isset($input[$field]) && !is_string($input[$field])) usuariosError('Datos inválidos.'); $$field = trim($input[$field] ?? ''); }
    if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,40}$/u', $nombre) || !preg_match('/^[a-zA-Z0-9]{4,20}$/D', $username)) usuariosError('Nombre o usuario inválido (usuario: 4–20 letras o números).');
    if ($email !== '' && (strlen($email) > 150 || !filter_var($email, FILTER_VALIDATE_EMAIL))) usuariosError('Correo inválido.');
    if (strlen($telefono) > 30 || !in_array($rol, ['Administrador','Logistica','Supervisor'], true) || !in_array($estado, ['activo','inactivo','suspendido'], true)) usuariosError('Rol, estado o teléfono inválido.');
    if ($self && $estado !== 'activo') usuariosError('No puedes desactivar tu propia cuenta.', 403);
    $stmt = $pdo->prepare('SELECT UsuarioID FROM Usuarios WHERE Username = ? AND UsuarioID <> ?'); $stmt->execute([$username, $id ?: 0]); if ($stmt->fetchColumn()) usuariosError('El usuario ya está registrado.', 409);
    if ($email !== '') { $stmt = $pdo->prepare('SELECT UsuarioID FROM Usuarios WHERE email = ? AND UsuarioID <> ?'); $stmt->execute([$email, $id ?: 0]); if ($stmt->fetchColumn()) usuariosError('El correo ya está registrado.', 409); }
    if ($method === 'POST') {
        $password = $input['password'] ?? null;
        if (!is_string($password) || preg_match_all('/./us', $password) < max(1, (int)config('app.security.password_min_length', 8)) || strlen($password) > 72) usuariosError('La contraseña no cumple la longitud permitida (máximo 72 bytes).');
        $stmt = $pdo->prepare('INSERT INTO Usuarios (Nombre, Username, email, Rol, estado, telefono, Password, creado_por, fecha_ultimo_cambio_password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())');
        $stmt->execute([$nombre, $username, $email ?: null, $rol, $estado, $telefono ?: null, password_hash($password, PASSWORD_DEFAULT), $_SESSION['id']]);
        usuariosRespuesta(201, ['success' => true, 'message' => 'Usuario creado.']);
    }
    $stmt = $pdo->prepare('UPDATE Usuarios SET Nombre = ?, Username = ?, email = ?, Rol = ?, estado = ?, telefono = ?, modificado_por = ?, fecha_ultima_modificacion = NOW() WHERE UsuarioID = ?');
    $stmt->execute([$nombre, $username, $email ?: null, $rol, $estado, $telefono ?: null, $_SESSION['id'], $id]);
    usuariosRespuesta(200, ['success' => true, 'message' => 'Usuario actualizado.']);
} catch (Throwable $e) { if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack(); error_log('Usuarios: ' . $e->getMessage()); usuariosError('No se pudo completar la operación.', 500); }
