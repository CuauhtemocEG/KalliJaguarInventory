<?php
header('Content-Type: application/json; charset=utf-8');
require_once BASE_PATH . '/src/Helpers/legacy.php';
function configuracionRespuesta(int $code, array $data): void { http_response_code($code); echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE); exit; }
$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'GET') configuracionRespuesta(200, ['success' => true, 'tax_rate' => (float)config('app.tax_rate')]);
if ($method !== 'PUT') configuracionRespuesta(405, ['success' => false, 'message' => 'Método no permitido.']);
$input = json_decode(file_get_contents('php://input'), true);
if (is_array($input) && ($input['action'] ?? '') === 'mantenimiento') {
    $key = $input['key'] ?? null; $value = $input['value'] ?? null;
    $allowed = ['MAINTENANCE_MODE_ENABLED' => 'boolean', 'BLOCKED_WEEKDAYS' => 'array', 'BLOCKED_DATES' => 'array', 'BLOCKED_TIME_START' => 'string', 'BLOCKED_TIME_END' => 'string', 'MAINTENANCE_MESSAGE' => 'string', 'MAINTENANCE_SUBMESSAGE' => 'string'];
    if (!is_string($key) || !isset($allowed[$key])) configuracionRespuesta(422, ['success' => false, 'message' => 'Configuración inválida.']);
    $valid = match ($key) {
        'MAINTENANCE_MODE_ENABLED' => is_bool($value),
        'BLOCKED_WEEKDAYS' => is_array($value) && array_is_list($value) && count($value) <= 7 && count(array_filter($value, static fn($day) => !is_int($day) || $day < 0 || $day > 6)) === 0 && count(array_unique($value, SORT_REGULAR)) === count($value),
        'BLOCKED_DATES' => is_array($value) && array_is_list($value) && count($value) <= 366 && count(array_filter($value, static function ($date) { if (!is_string($date) || !preg_match('/^(\d{4})-(\d{2})-(\d{2})$/D', $date, $m)) return true; return !checkdate((int)$m[2], (int)$m[3], (int)$m[1]); })) === 0,
        'BLOCKED_TIME_START', 'BLOCKED_TIME_END' => is_string($value) && ($value === '' || preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/D', $value)),
        default => is_string($value) && strlen($value) <= 65535,
    };
    if (!$valid) configuracionRespuesta(422, ['success' => false, 'message' => 'Valor de mantenimiento inválido.']);
    try {
        $pdo = conexion(); $pdo->beginTransaction();
        $stmt = $pdo->prepare('SELECT ConfigValue, ConfigType FROM MaintenanceConfig WHERE ConfigKey = ? AND Activo = 1 FOR UPDATE'); $stmt->execute([$key]); $current = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$current) { $pdo->rollBack(); configuracionRespuesta(404, ['success' => false, 'message' => 'Configuración no encontrada.']); }
        if (($allowed[$key] === 'array' || $allowed[$key] === 'boolean') && $current['ConfigType'] !== $allowed[$key]) { $pdo->rollBack(); configuracionRespuesta(409, ['success' => false, 'message' => 'Tipo de configuración incompatible.']); }
        $newValue = is_bool($value) ? ($value ? 'true' : 'false') : (is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) : $value);
        $stmt = $pdo->prepare('UPDATE MaintenanceConfig SET ConfigValue = ?, FechaModificacion = NOW() WHERE ConfigKey = ? AND Activo = 1'); $stmt->execute([$newValue, $key]);
        $stmt = $pdo->prepare('INSERT INTO MaintenanceConfigLog (ConfigKey, ValorAnterior, ValorNuevo, UsuarioID, IPAddress, UserAgent) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$key, $current['ConfigValue'], $newValue, $_SESSION['id'] ?? null, substr($_SERVER['REMOTE_ADDR'] ?? '', 0, 45), substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255)]);
        $pdo->commit(); configuracionRespuesta(200, ['success' => true, 'message' => 'Configuración actualizada.']);
    } catch (Throwable $e) { if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack(); error_log('Mantenimiento: ' . $e->getMessage()); configuracionRespuesta(500, ['success' => false, 'message' => 'No se pudo actualizar mantenimiento.']); }
}
$percent = $input['porcentaje'] ?? null;
if ((!is_string($percent) && !is_int($percent) && !is_float($percent)) || !is_numeric($percent) || !is_finite((float)$percent) || (float)$percent < 0 || (float)$percent > 100) configuracionRespuesta(422, ['success' => false, 'message' => 'Indica un porcentaje entre 0 y 100.']);
$rate = rtrim(rtrim(sprintf('%.8F', (float)$percent / 100), '0'), '.'); if ($rate === '') $rate = '0';
$envPath = BASE_PATH . '/.env'; $logDir = config('paths.logs');
if (!is_file($envPath) || !is_dir($logDir)) configuracionRespuesta(500, ['success' => false, 'message' => 'No existe el archivo .env o el directorio de registros.']);
$env = @fopen($envPath, 'r+'); $log = @fopen(rtrim($logDir, '/\\') . '/tax-rate.log', 'ab');
if (!$env || !$log) { if ($env) fclose($env); if ($log) fclose($log); configuracionRespuesta(500, ['success' => false, 'message' => 'No se pudo abrir la configuración o el registro.']); }
$ok = false;
try {
    if (!flock($env, LOCK_EX)) throw new RuntimeException('No se pudo bloquear .env');
    if (!flock($log, LOCK_EX)) throw new RuntimeException('No se pudo bloquear el registro');
    rewind($env); $original = stream_get_contents($env); if ($original === false) throw new RuntimeException('No se pudo leer .env');
    $previousRate = (float)config('app.tax_rate');
    if (preg_match('/^[ \t]*(?:export[ \t]+)?TAX_RATE[ \t]*=[ \t]*[\"\']?([0-9]+(?:\.[0-9]+)?)/m', $original, $oldMatch)) $previousRate = (float)$oldMatch[1];
    $count = 0;
    $updated = preg_replace_callback('/^([ \t]*(?:export[ \t]+)?TAX_RATE[ \t]*=[ \t]*)([^\r\n]*)(\r?\n|$)/m', static function ($match) use ($rate, &$count) {
        $count++; $comment = preg_match('/([ \t]+#.*)$/', $match[2], $m) ? $m[1] : '';
        return $match[1] . $rate . $comment . $match[3];
    }, $original);
    if ($updated === null) throw new RuntimeException('No se pudo procesar .env');
    if (!$count) $updated .= ($updated !== '' && !preg_match('/\r?\n$/', $updated) ? "\n" : '') . 'TAX_RATE=' . $rate . "\n";
    rewind($env);
    if (!ftruncate($env, 0) || fwrite($env, $updated) !== strlen($updated) || !fflush($env)) { rewind($env); ftruncate($env, 0); fwrite($env, $original); fflush($env); throw new RuntimeException('No se pudo escribir .env'); }
    $entry = json_encode(['fecha' => date(DATE_ATOM), 'usuario_id' => $_SESSION['id'] ?? null, 'usuario' => $_SESSION['nombre'] ?? '', 'tasa_anterior' => $previousRate, 'tasa_nueva' => (float)$rate], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE) . "\n";
    if (fwrite($log, $entry) !== strlen($entry) || !fflush($log)) { rewind($env); ftruncate($env, 0); fwrite($env, $original); fflush($env); throw new RuntimeException('No se pudo registrar el cambio'); }
    $ok = true;
} catch (Throwable $e) { error_log('Configuración IVA: ' . $e->getMessage()); }
flock($log, LOCK_UN); flock($env, LOCK_UN); fclose($log); fclose($env);
if (!$ok) configuracionRespuesta(500, ['success' => false, 'message' => 'No se pudo guardar o registrar el cambio de IVA.']);
configuracionRespuesta(200, ['success' => true, 'message' => 'IVA actualizado.', 'tax_rate' => (float)$rate]);
