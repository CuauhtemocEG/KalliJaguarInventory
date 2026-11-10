<?php
/**
 * Ajuste rápido de stock y/o precio (PrecioUnitario sin IVA) con bitácora.
 *   GET  ?id=N                          -> historial reciente de stock y precio
 *   POST {ProductoID, Cantidad?, PrecioUnitario?}   (el precio solo lo cambia Administrador)
 */
require_once BASE_PATH . '/src/Helpers/legacy.php';

header('Content-Type: application/json; charset=utf-8');

function responder(bool $ok, array $extra = [], int $code = 200): void
{
    http_response_code($code);
    echo json_encode(['success' => $ok] + $extra, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $pid = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
    if (!$pid || $pid < 1) {
        responder(false, ['message' => 'Producto requerido'], 422);
    }
    try {
        $pdo = conexion();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $st = $pdo->prepare('SELECT UPC FROM Productos WHERE ProductoID = ?');
        $st->execute([$pid]);
        $upc = $st->fetchColumn();
        if ($upc === false) {
            responder(false, ['message' => 'Producto no encontrado'], 404);
        }
        $stock = $precio = [];
        if ($upc !== null && $upc !== '') {
            $st = $pdo->prepare('SELECT l.StockBefore AS antes, l.StockAfter AS despues, l.Fecha AS fecha, COALESCE(u.Nombre, \'-\') AS usuario
                FROM Logs_stock l LEFT JOIN Usuarios u ON u.UsuarioID = l.UsuarioID WHERE l.UPC = ? ORDER BY l.Fecha DESC LIMIT 15');
            $st->execute([$upc]);
            $stock = $st->fetchAll(PDO::FETCH_ASSOC);
            $st = $pdo->prepare('SELECT l.PrecioBefore AS antes, l.PrecioAfter AS despues, l.FechaCambio AS fecha, COALESCE(u.Nombre, \'-\') AS usuario
                FROM Logs_precio l LEFT JOIN Usuarios u ON u.UsuarioID = l.UsuarioID WHERE l.UPC = ? ORDER BY l.FechaCambio DESC LIMIT 15');
            $st->execute([$upc]);
            $precio = $st->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        error_log('gestor/productos historial: ' . $e->getMessage());
        responder(false, ['message' => 'No se pudo cargar el historial'], 500);
    }
    responder(true, ['stock' => $stock, 'precio' => $precio]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(false, ['message' => 'Método no permitido'], 405);
}

$in = json_decode(file_get_contents('php://input'), true);
if (!is_array($in)) {
    responder(false, ['message' => 'Solicitud no válida'], 400);
}

$id = filter_var($in['ProductoID'] ?? null, FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    responder(false, ['message' => 'Producto requerido'], 422);
}

$stock = array_key_exists('Cantidad', $in) && $in['Cantidad'] !== '' && $in['Cantidad'] !== null ? $in['Cantidad'] : null;
$precio = array_key_exists('PrecioUnitario', $in) && $in['PrecioUnitario'] !== '' && $in['PrecioUnitario'] !== null ? $in['PrecioUnitario'] : null;
if ($stock === null && $precio === null) {
    responder(false, ['message' => 'Indique stock o precio a actualizar'], 422);
}
if ($stock !== null && (!is_numeric($stock) || (float) $stock < 0 || (float) $stock > 1000000000)) {
    responder(false, ['message' => 'El stock debe ser un número mayor o igual a 0'], 422);
}
if ($precio !== null && (!is_numeric($precio) || (float) $precio < 0 || (float) $precio > 1000000000)) {
    responder(false, ['message' => 'El precio debe ser un número mayor o igual a 0'], 422);
}

if ($precio !== null && !\App\Core\Auth::hasRole(['Administrador'])) {
    responder(false, ['message' => 'Solo un Administrador puede cambiar precios'], 403);
}

$usuarioId = (int) ($_SESSION['id'] ?? 0) ?: null;

try {
    $pdo = conexion();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->beginTransaction();

    $stmt = $pdo->prepare('SELECT UPC, Cantidad, PrecioUnitario, Tipo FROM Productos WHERE ProductoID = :id FOR UPDATE');
    $stmt->execute([':id' => $id]);
    $p = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$p) {
        $pdo->rollBack();
        responder(false, ['message' => 'Producto no encontrado'], 404);
    }
    if ($stock !== null && \App\Services\Unidades::esEntero($p['Tipo']) && floor((float) $stock) != (float) $stock) {
        $pdo->rollBack();
        responder(false, ['message' => 'Este producto solo admite stock entero'], 422);
    }

    if ($stock !== null && (float) $stock !== (float) $p['Cantidad']) {
        $pdo->prepare('UPDATE Productos SET Cantidad = :v, FechaModificacion = NOW() WHERE ProductoID = :id')
            ->execute([':v' => round((float) $stock, 3), ':id' => $id]);
        $pdo->prepare('INSERT INTO Logs_stock (UPC, StockBefore, StockAfter, UsuarioID, Fecha) VALUES (:upc, :a, :d, :u, NOW())')
            ->execute([':upc' => $p['UPC'], ':a' => $p['Cantidad'], ':d' => round((float) $stock, 3), ':u' => $usuarioId]);
    }
    if ($precio !== null && round((float) $precio, 2) !== round((float) $p['PrecioUnitario'], 2)) {
        $pdo->prepare('UPDATE Productos SET PrecioUnitario = :v, FechaModificacion = NOW() WHERE ProductoID = :id')
            ->execute([':v' => round((float) $precio, 2), ':id' => $id]);
        $pdo->prepare('INSERT INTO Logs_precio (UPC, PrecioBefore, PrecioAfter, UsuarioID, FechaCambio) VALUES (:upc, :a, :d, :u, NOW())')
            ->execute([':upc' => $p['UPC'], ':a' => $p['PrecioUnitario'], ':d' => round((float) $precio, 2), ':u' => $usuarioId]);
    }
    $pdo->commit();
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    responder(false, ['message' => 'No se pudo actualizar el producto'], 500);
}

responder(true, ['message' => 'Producto actualizado']);
