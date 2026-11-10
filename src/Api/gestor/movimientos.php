<?php
/**
 * Inventario del Gestor.
 *   GET  ?vista=movimientos|stock|precio&desde&hasta&q&tipo&page&limit  -> listado paginado
 *   POST {ProductoID, Cantidad}                                        -> entrada de mercancía
 */
require_once BASE_PATH . '/src/Helpers/legacy.php';

header('Content-Type: application/json; charset=utf-8');

function responder(bool $ok, array $extra = [], int $code = 200): void
{
    http_response_code($code);
    echo json_encode(['success' => $ok] + $extra, JSON_UNESCAPED_UNICODE);
    exit;
}

function fechaValida(?string $f): bool
{
    $d = $f ? DateTime::createFromFormat('Y-m-d', $f) : false;
    return $d && $d->format('Y-m-d') === $f;
}

try {
    $pdo = conexion();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Throwable $e) {
    responder(false, ['message' => 'Error de conexión a la base de datos'], 500);
}

$metodo = $_SERVER['REQUEST_METHOD'];
if ($metodo === 'GET') {
    listar($pdo);
} elseif ($metodo === 'POST') {
    registrarEntrada($pdo);
}
responder(false, ['message' => 'Método no permitido'], 405);

function listar(PDO $pdo): void
{
    $vista = $_GET['vista'] ?? 'movimientos';
    if (!in_array($vista, ['movimientos', 'stock', 'precio'], true)) {
        responder(false, ['message' => 'Vista no válida'], 422);
    }

    $desde = trim($_GET['desde'] ?? '');
    $hasta = trim($_GET['hasta'] ?? '');
    $q = trim($_GET['q'] ?? '');
    $tipo = trim($_GET['tipo'] ?? '');
    if (($desde !== '' && !fechaValida($desde)) || ($hasta !== '' && !fechaValida($hasta))) {
        responder(false, ['message' => 'Fecha no válida (use AAAA-MM-DD)'], 422);
    }
    if ($tipo !== '' && !in_array($tipo, ['Entrada', 'Salida'], true)) {
        responder(false, ['message' => 'Tipo de movimiento no válido'], 422);
    }

    $limit = min(100, max(1, (int) ($_GET['limit'] ?? 25)));
    $page = max(1, (int) ($_GET['page'] ?? 1));
    $offset = ($page - 1) * $limit;

    if ($vista === 'movimientos') {
        $from = "FROM MovimientosInventario m
                 LEFT JOIN Productos p ON p.ProductoID = m.ProductoID
                 LEFT JOIN Usuarios u ON u.UsuarioID = m.UsuarioID
                 LEFT JOIN Sucursales s ON s.SucursalID = m.SucursalID";
        $select = "SELECT m.MovimientoID, m.ComandaID, m.ProductoID, m.TipoMovimiento, m.Cantidad,
                          m.FechaMovimiento AS Fecha, m.PrecioFinal, m.Status,
                          p.Nombre AS ProductoNombre, p.UPC, p.Tipo AS ProductoTipo,
                          u.nombre AS UsuarioNombre, s.nombre AS SucursalNombre";
        $colFecha = 'm.FechaMovimiento';
        $orden = 'm.FechaMovimiento DESC, m.MovimientoID DESC';
    } elseif ($vista === 'stock') {
        $from = "FROM Logs_stock l
                 LEFT JOIN Productos p ON p.UPC = l.UPC
                 LEFT JOIN Usuarios u ON u.UsuarioID = l.UsuarioID";
        $select = "SELECT l.id, l.UPC, l.StockBefore, l.StockAfter, l.Fecha,
                          p.ProductoID, p.Nombre AS ProductoNombre, p.Tipo AS ProductoTipo, u.nombre AS UsuarioNombre";
        $colFecha = 'l.Fecha';
        $orden = 'l.Fecha DESC, l.id DESC';
    } else {
        $from = "FROM Logs_precio l
                 LEFT JOIN Productos p ON p.UPC = l.UPC
                 LEFT JOIN Usuarios u ON u.UsuarioID = l.UsuarioID";
        $select = "SELECT l.UPC, l.PrecioBefore, l.PrecioAfter, l.FechaCambio AS Fecha,
                          p.ProductoID, p.Nombre AS ProductoNombre, u.nombre AS UsuarioNombre";
        $colFecha = 'l.FechaCambio';
        $orden = 'l.FechaCambio DESC';
    }

    $where = ['1=1'];
    $params = [];
    if ($desde !== '') {
        $where[] = "$colFecha >= :desde";
        $params[':desde'] = $desde . ' 00:00:00';
    }
    if ($hasta !== '') {
        $where[] = "$colFecha <= :hasta";
        $params[':hasta'] = $hasta . ' 23:59:59';
    }
    if ($q !== '') {
        $where[] = '(p.Nombre LIKE :q OR p.UPC LIKE :q2' . ($vista !== 'movimientos' ? ' OR l.UPC LIKE :q3' : '') . ')';
        $params[':q'] = $params[':q2'] = "%$q%";
        if ($vista !== 'movimientos') {
            $params[':q3'] = "%$q%";
        }
    }
    if ($tipo !== '' && $vista === 'movimientos') {
        $where[] = 'm.TipoMovimiento = :tipo';
        $params[':tipo'] = $tipo;
    }
    $cond = ' WHERE ' . implode(' AND ', $where);

    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) $from $cond");
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $stmt = $pdo->prepare("$select $from $cond ORDER BY $orden LIMIT :limit OFFSET :offset");
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        responder(false, ['message' => 'Error al consultar el inventario'], 500);
    }

    responder(true, [
        'items' => $items,
        'total' => $total,
        'page' => $page,
        'limit' => $limit,
        'pages' => max(1, (int) ceil($total / $limit)),
    ]);
}

function registrarEntrada(PDO $pdo): void
{
    $in = json_decode(file_get_contents('php://input'), true);
    if (!is_array($in)) {
        responder(false, ['message' => 'Solicitud no válida'], 400);
    }

    $productoId = filter_var($in['ProductoID'] ?? null, FILTER_VALIDATE_INT);
    $cantidad = $in['Cantidad'] ?? null;
    if (!$productoId || $productoId < 1) {
        responder(false, ['message' => 'Producto requerido'], 422);
    }
    if (!is_numeric($cantidad) || (float) $cantidad <= 0 || (float) $cantidad > 1000000000) {
        responder(false, ['message' => 'La cantidad debe ser mayor a 0'], 422);
    }
    $cantidad = round((float) $cantidad, 3);
    $usuarioId = (int) ($_SESSION['id'] ?? 0) ?: null;

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare('SELECT UPC, Nombre, Cantidad, Tipo FROM Productos WHERE ProductoID = :id FOR UPDATE');
        $stmt->execute([':id' => $productoId]);
        $p = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$p) {
            $pdo->rollBack();
            responder(false, ['message' => 'Producto no encontrado'], 404);
        }
        if (\App\Services\Unidades::esEntero($p['Tipo']) && floor($cantidad) != $cantidad) {
            $pdo->rollBack();
            responder(false, ['message' => 'Este producto solo admite cantidades enteras'], 422);
        }

        $antes = (float) $p['Cantidad'];
        $despues = $antes + $cantidad;

        $pdo->prepare('UPDATE Productos SET Cantidad = :n, FechaModificacion = NOW() WHERE ProductoID = :id')
            ->execute([':n' => $despues, ':id' => $productoId]);
        $pdo->prepare("INSERT INTO MovimientosInventario (ProductoID, TipoMovimiento, Cantidad, FechaMovimiento, UsuarioID, Status)
                       VALUES (:p, 'Entrada', :c, NOW(), :u, 'Cerrado')")
            ->execute([':p' => $productoId, ':c' => $cantidad, ':u' => $usuarioId]);
        $pdo->prepare('INSERT INTO Logs_stock (UPC, StockBefore, StockAfter, UsuarioID, Fecha) VALUES (:upc, :a, :d, :u, NOW())')
            ->execute([':upc' => $p['UPC'], ':a' => $antes, ':d' => $despues, ':u' => $usuarioId]);
        $pdo->commit();
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        responder(false, ['message' => 'No se pudo registrar la entrada'], 500);
    }

    responder(true, [
        'message' => 'Entrada registrada',
        'data' => ['ProductoID' => $productoId, 'StockBefore' => $antes, 'StockAfter' => $despues],
    ], 201);
}
