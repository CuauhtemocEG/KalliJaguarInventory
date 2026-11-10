<?php
/**
 * Gestor · Pedidos (comandas).
 * GET  ?comanda=ID            → detalle de una comanda
 * GET  (filtros: estado, sucursal, solicitante, desde, hasta, q) → lista + contadores
 * GET  ?buscar=texto          → productos activos para agregar a un pedido
 * POST {accion: transito|entregar|cancelar, comanda, picking?, motivo?}
 * POST {accion: agregar, comanda, producto_id, cantidad} / {accion: quitar, comanda, movimiento_id} (solo pedidos Abiertos)
 */
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
require_once BASE_PATH . '/src/Helpers/legacy.php';

const PEDIDO_ESTADOS = ['Abierto', 'En transito', 'Cerrado', 'Cancelado'];

function pedidoResponder(int $codigo, array $datos): void
{
    http_response_code($codigo);
    echo json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
    exit;
}

function pedidoNotificar(string $asunto, string $cuerpo): void
{
    // El correo es informativo: un fallo SMTP nunca debe revertir la operación
    try {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        \App\Services\Mailer::configure($mail);
        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body = $cuerpo;
        $mail->send();
    } catch (\Throwable $e) {
        error_log('gestor/pedidos: correo no enviado: ' . $e->getMessage());
    }
}

if (!in_array($_SESSION['rol'] ?? '', ['Administrador', 'Logistica'], true)) {
    pedidoResponder(403, ['success' => false, 'message' => 'No tienes permisos para esta acción']);
}

$db = conexion();
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        if (isset($_GET['buscar'])) {
            pedidoBuscarProductos($db, trim((string) $_GET['buscar']));
        }
        $comanda = trim((string) ($_GET['comanda'] ?? ''));
        if ($comanda !== '') {
            pedidoDetalle($db, $comanda);
        }
        pedidoLista($db);
    }

    if ($method === 'POST') {
        $in = json_decode(file_get_contents('php://input'), true);
        if (!is_array($in)) {
            $in = $_POST;
        }
        pedidoAccion($db, $in);
    }

    pedidoResponder(405, ['success' => false, 'message' => 'Método no permitido']);
} catch (\Throwable $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    error_log('gestor/pedidos: ' . $e->getMessage());
    pedidoResponder(500, ['success' => false, 'message' => 'Error interno al procesar el pedido']);
}

function pedidoBuscarProductos(PDO $db, string $q): void
{
    if (mb_strlen($q) < 2) {
        pedidoResponder(200, ['success' => true, 'productos' => []]);
    }
    $stmt = $db->prepare(
        "SELECT ProductoID, UPC, Nombre, Tipo, Cantidad AS Stock, PrecioUnitario
         FROM Productos
         WHERE COALESCE(Activo, 1) = 1 AND (Nombre LIKE :q OR UPC LIKE :q OR SKU LIKE :q)
         ORDER BY Nombre LIMIT 15"
    );
    $stmt->execute([':q' => '%' . addcslashes($q, '%_\\') . '%']);
    $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($filas as &$f) {
        $f['PrecioUnitarioIva'] = round((float) $f['PrecioUnitario'] * TAX_MULTIPLIER, 2);
    }
    unset($f);
    pedidoResponder(200, ['success' => true, 'productos' => $filas]);
}

/** Agrega o quita una partida de un pedido Abierto; el stock se descuenta/repone y el total se recalcula. */
function pedidoEditarPartida(PDO $db, string $accion, string $comanda, array $in, int $userId): void
{
    if ($comanda === '') {
        pedidoResponder(400, ['success' => false, 'message' => 'Datos incompletos']);
    }

    $db->beginTransaction();
    $stmt = $db->prepare(
        "SELECT MovimientoID, ProductoID, Cantidad, PrecioFinal, SucursalID, UsuarioID, Status FROM MovimientosInventario
         WHERE ComandaID = :c AND TipoMovimiento = 'Salida' FOR UPDATE"
    );
    $stmt->execute([':c' => $comanda]);
    $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!$filas) {
        $db->rollBack();
        pedidoResponder(404, ['success' => false, 'message' => 'El pedido no existe']);
    }
    if (count(array_unique(array_column($filas, 'Status'))) !== 1 || $filas[0]['Status'] !== 'Abierto') {
        $db->rollBack();
        pedidoResponder(409, ['success' => false, 'message' => 'Solo se pueden modificar pedidos en estado Abierto']);
    }

    $stock = $db->prepare('SELECT ProductoID, UPC, Nombre, Tipo, Cantidad, PrecioUnitario, COALESCE(Activo, 1) AS Activo FROM Productos WHERE ProductoID = :p FOR UPDATE');
    $logStock = $db->prepare('INSERT INTO Logs_stock (UPC, StockBefore, StockAfter, UsuarioID, Fecha) VALUES (:u, :a, :d, :usr, NOW())');
    $est = $db->prepare(
        'INSERT INTO EstadosComanda (ComandaID, EstadoAnterior, EstadoNuevo, FechaCambio, UsuarioID, Observaciones)
         VALUES (:c, \'Abierto\', \'Abierto\', NOW(), :u, :o)'
    );

    if ($accion === 'agregar') {
        $productoId = (int) ($in['producto_id'] ?? 0);
        $stock->execute([':p' => $productoId]);
        $prod = $stock->fetch(PDO::FETCH_ASSOC);
        if (!$prod || !(int) $prod['Activo']) {
            $db->rollBack();
            pedidoResponder(404, ['success' => false, 'message' => 'El producto no existe o está inactivo']);
        }
        [$cantidad, $error] = \App\Services\Unidades::validar($prod['Tipo'], $in['cantidad'] ?? null);
        if ($error !== null) {
            $db->rollBack();
            pedidoResponder(422, ['success' => false, 'message' => $error]);
        }
        if ($cantidad > (float) $prod['Cantidad'] + 0.0001) {
            $db->rollBack();
            pedidoResponder(422, ['success' => false, 'message' => 'Stock insuficiente de ' . $prod['Nombre'] . ': quedan ' . \App\Services\Unidades::formato($prod['Tipo'], (float) $prod['Cantidad'])]);
        }

        $existente = null;
        foreach ($filas as $f) {
            if ((int) $f['ProductoID'] === $productoId) {
                $existente = $f;
                break;
            }
        }
        if ($existente) {
            // La cantidad ya apartada conserva su precio; solo lo agregado se cotiza al precio vigente
            $previa = (float) $existente['Cantidad'];
            $total = $previa + $cantidad;
            $importe = round((float) $existente['PrecioFinal'] + $cantidad * (float) $prod['PrecioUnitario'] * TAX_MULTIPLIER, 2);
            $db->prepare('UPDATE MovimientosInventario SET Cantidad = :q, PrecioFinal = :i WHERE MovimientoID = :m')
                ->execute([':q' => $total, ':i' => $importe, ':m' => $existente['MovimientoID']]);
        } else {
            $importe = round($cantidad * (float) $prod['PrecioUnitario'] * TAX_MULTIPLIER, 2);
            $db->prepare(
                "INSERT INTO MovimientosInventario (ComandaID, SucursalID, ProductoID, TipoMovimiento, Cantidad, FechaMovimiento, PrecioFinal, UsuarioID, Status)
                 VALUES (:c, :s, :p, 'Salida', :q, NOW(), :i, :u, 'Abierto')"
            )->execute([':c' => $comanda, ':s' => $filas[0]['SucursalID'], ':p' => $productoId, ':q' => $cantidad, ':i' => $importe, ':u' => $filas[0]['UsuarioID']]);
        }
        $db->prepare('UPDATE Productos SET Cantidad = Cantidad - :q WHERE ProductoID = :p')->execute([':q' => $cantidad, ':p' => $productoId]);
        $logStock->execute([':u' => $prod['UPC'], ':a' => $prod['Cantidad'], ':d' => round((float) $prod['Cantidad'] - $cantidad, 3), ':usr' => $userId]);
        $obs = 'Se agregó ' . $prod['Nombre'] . ' (' . \App\Services\Unidades::formato($prod['Tipo'], $cantidad) . ')';
    } else {
        $movId = (int) ($in['movimiento_id'] ?? 0);
        $fila = null;
        foreach ($filas as $f) {
            if ((int) $f['MovimientoID'] === $movId) {
                $fila = $f;
                break;
            }
        }
        if (!$fila) {
            $db->rollBack();
            pedidoResponder(404, ['success' => false, 'message' => 'La partida no pertenece a este pedido']);
        }
        if (count($filas) === 1) {
            $db->rollBack();
            pedidoResponder(409, ['success' => false, 'message' => 'Es la única partida: cancela el pedido en lugar de quitarla']);
        }
        $stock->execute([':p' => $fila['ProductoID']]);
        $prod = $stock->fetch(PDO::FETCH_ASSOC);
        $db->prepare('DELETE FROM MovimientosInventario WHERE MovimientoID = :m')->execute([':m' => $movId]);
        if ($prod) {
            $db->prepare('UPDATE Productos SET Cantidad = Cantidad + :q WHERE ProductoID = :p')->execute([':q' => $fila['Cantidad'], ':p' => $fila['ProductoID']]);
            $logStock->execute([':u' => $prod['UPC'], ':a' => $prod['Cantidad'], ':d' => round((float) $prod['Cantidad'] + (float) $fila['Cantidad'], 3), ':usr' => $userId]);
        }
        $obs = 'Se quitó ' . ($prod['Nombre'] ?? 'un producto') . ' (' . \App\Services\Unidades::formato($prod['Tipo'] ?? null, (float) $fila['Cantidad']) . ')';
    }

    $est->execute([':c' => $comanda, ':u' => $userId, ':o' => mb_substr($obs, 0, 240)]);
    $db->commit();
    pedidoResponder(200, ['success' => true, 'message' => 'Pedido actualizado']);
}

function pedidoLista(PDO $db): void
{
    $where = ["m.TipoMovimiento = 'Salida'", 'm.ComandaID IS NOT NULL'];
    $params = [];

    $sucursal = (int) ($_GET['sucursal'] ?? 0);
    if ($sucursal > 0) {
        $where[] = 'm.SucursalID = :sucursal';
        $params[':sucursal'] = $sucursal;
    }
    $solicitante = (int) ($_GET['solicitante'] ?? 0);
    if ($solicitante > 0) {
        $where[] = 'm.UsuarioID = :solicitante';
        $params[':solicitante'] = $solicitante;
    }
    $desde = (string) ($_GET['desde'] ?? '');
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $desde)) {
        $where[] = 'm.FechaMovimiento >= :desde';
        $params[':desde'] = $desde . ' 00:00:00';
    }
    $hasta = (string) ($_GET['hasta'] ?? '');
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $hasta)) {
        $where[] = 'm.FechaMovimiento <= :hasta';
        $params[':hasta'] = $hasta . ' 23:59:59';
    }
    $q = trim((string) ($_GET['q'] ?? ''));
    if ($q !== '') {
        $where[] = 'm.ComandaID LIKE :q';
        $params[':q'] = '%' . addcslashes($q, '%_\\') . '%';
    }
    $base = implode(' AND ', $where);

    $cont = $db->prepare("SELECT estado, COUNT(*) AS n FROM (
            SELECT MAX(m.Status) AS estado FROM MovimientosInventario m WHERE $base GROUP BY m.ComandaID
        ) t GROUP BY estado");
    $cont->execute($params);
    $contadores = array_fill_keys(PEDIDO_ESTADOS, 0);
    foreach ($cont->fetchAll(PDO::FETCH_ASSOC) as $r) {
        if (isset($contadores[$r['estado']])) {
            $contadores[$r['estado']] = (int) $r['n'];
        }
    }

    $having = '';
    $estado = (string) ($_GET['estado'] ?? '');
    if (in_array($estado, PEDIDO_ESTADOS, true)) {
        $having = 'HAVING MAX(m.Status) = :estado';
        $params[':estado'] = $estado;
    }

    $stmt = $db->prepare(
        "SELECT m.ComandaID, MIN(m.FechaMovimiento) AS fecha, MAX(m.Status) AS estado,
                MAX(m.FechaDelivery) AS entrega, MAX(m.SucursalID) AS SucursalID, s.nombre AS sucursal,
                MAX(m.UsuarioID) AS UsuarioID, u.Nombre AS solicitante,
                COUNT(*) AS articulos, SUM(m.PrecioFinal) AS total
         FROM MovimientosInventario m
         LEFT JOIN Sucursales s ON s.SucursalID = m.SucursalID
         LEFT JOIN Usuarios u ON u.UsuarioID = m.UsuarioID
         WHERE $base
         GROUP BY m.ComandaID, s.nombre, u.Nombre
         $having
         ORDER BY fecha DESC
         LIMIT 300"
    );
    $stmt->execute($params);
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sucursales = $db->query('SELECT SucursalID, nombre FROM Sucursales ORDER BY nombre')->fetchAll(PDO::FETCH_ASSOC);
    $solicitantes = $db->query(
        "SELECT DISTINCT u.UsuarioID, u.Nombre FROM Usuarios u
         INNER JOIN MovimientosInventario m ON m.UsuarioID = u.UsuarioID AND m.TipoMovimiento = 'Salida'
         ORDER BY u.Nombre"
    )->fetchAll(PDO::FETCH_ASSOC);

    pedidoResponder(200, [
        'success' => true,
        'pedidos' => $pedidos,
        'contadores' => $contadores,
        'sucursales' => $sucursales,
        'solicitantes' => $solicitantes,
        'iva' => TAX_MULTIPLIER,
    ]);
}

function pedidoDetalle(PDO $db, string $comanda): void
{
    $cab = $db->prepare(
        "SELECT m.ComandaID, MIN(m.FechaMovimiento) AS fecha, MAX(m.Status) AS estado,
                MAX(m.FechaDelivery) AS entrega, s.nombre AS sucursal, u.Nombre AS solicitante,
                SUM(m.PrecioFinal) AS total
         FROM MovimientosInventario m
         LEFT JOIN Sucursales s ON s.SucursalID = m.SucursalID
         LEFT JOIN Usuarios u ON u.UsuarioID = m.UsuarioID
         WHERE m.ComandaID = :c AND m.TipoMovimiento = 'Salida'
         GROUP BY m.ComandaID, s.nombre, u.Nombre"
    );
    $cab->execute([':c' => $comanda]);
    $pedido = $cab->fetch(PDO::FETCH_ASSOC);
    if (!$pedido) {
        pedidoResponder(404, ['success' => false, 'message' => 'El pedido no existe']);
    }

    $det = $db->prepare(
        "SELECT m.MovimientoID, m.ProductoID, p.UPC, p.Nombre, p.Tipo, m.Cantidad, m.PrecioFinal
         FROM MovimientosInventario m
         INNER JOIN Productos p ON p.ProductoID = m.ProductoID
         WHERE m.ComandaID = :c AND m.TipoMovimiento = 'Salida'
         ORDER BY p.Nombre"
    );
    $det->execute([':c' => $comanda]);
    $partidas = $det->fetchAll(PDO::FETCH_ASSOC);

    $pick = $db->prepare('SELECT ProductoID, CantidadSolicitada, CantidadPickeada FROM PickingLog WHERE ComandaID = :c ORDER BY FechaPicking');
    $pick->execute([':c' => $comanda]);
    $pickeado = [];
    $solicitado = [];
    foreach ($pick->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $pickeado[$r['ProductoID']] = $r['CantidadPickeada'];
        $solicitado[$r['ProductoID']] = $r['CantidadSolicitada'];
    }
    foreach ($partidas as &$p) {
        $p['CantidadPickeada'] = $pickeado[$p['ProductoID']] ?? null;
        $p['CantidadSolicitada'] = $solicitado[$p['ProductoID']] ?? $p['Cantidad'];
        $p['PrecioUnitarioIva'] = (float) $p['Cantidad'] > 0 ? round((float) $p['PrecioFinal'] / (float) $p['Cantidad'], 6) : 0;
    }
    unset($p);

    $hist = $db->prepare(
        'SELECT e.EstadoAnterior, e.EstadoNuevo, e.FechaCambio, e.Observaciones, u.Nombre AS usuario
         FROM EstadosComanda e LEFT JOIN Usuarios u ON u.UsuarioID = e.UsuarioID
         WHERE e.ComandaID = :c ORDER BY e.FechaCambio'
    );
    $hist->execute([':c' => $comanda]);

    // PrecioFinal ya incluye IVA (precio unitario × TAX_MULTIPLIER × cantidad)
    $total = round((float) $pedido['total'], 2);
    $subtotal = round($total / TAX_MULTIPLIER, 2);

    pedidoResponder(200, [
        'success' => true,
        'pedido' => $pedido,
        'partidas' => $partidas,
        'historial' => $hist->fetchAll(PDO::FETCH_ASSOC),
        'totales' => ['subtotal' => $subtotal, 'iva' => round($total - $subtotal, 2), 'total' => $total],
        'pdf' => true,
    ]);
}

function pedidoAccion(PDO $db, array $in): void
{
    $accion = (string) ($in['accion'] ?? '');
    $comanda = trim((string) ($in['comanda'] ?? ''));
    $userId = (int) $_SESSION['id'];

    if (in_array($accion, ['agregar', 'quitar'], true)) {
        pedidoEditarPartida($db, $accion, $comanda, $in, $userId);
    }

    if ($comanda === '' || !in_array($accion, ['transito', 'entregar', 'cancelar'], true)) {
        pedidoResponder(400, ['success' => false, 'message' => 'Datos incompletos o acción no válida']);
    }
    $motivo = trim((string) ($in['motivo'] ?? ''));
    if ($accion === 'cancelar' && $motivo === '') {
        pedidoResponder(422, ['success' => false, 'message' => 'Indica el motivo de la cancelación']);
    }
    $motivo = mb_substr($motivo, 0, 240);

    $db->beginTransaction();

    $stmt = $db->prepare(
        "SELECT MovimientoID, ProductoID, Cantidad, Status FROM MovimientosInventario
         WHERE ComandaID = :c AND TipoMovimiento = 'Salida' FOR UPDATE"
    );
    $stmt->execute([':c' => $comanda]);
    $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!$filas) {
        $db->rollBack();
        pedidoResponder(404, ['success' => false, 'message' => 'El pedido no existe']);
    }

    $estados = array_unique(array_column($filas, 'Status'));
    if (count($estados) !== 1) {
        $db->rollBack();
        pedidoResponder(409, ['success' => false, 'message' => 'El pedido tiene partidas en estados inconsistentes']);
    }
    $actual = $estados[0];

    // Cada acción solo es válida desde su estado de origen
    $origen = ['transito' => 'Abierto', 'entregar' => 'En transito', 'cancelar' => null];
    $valido = $accion === 'cancelar' ? in_array($actual, ['Abierto', 'En transito'], true) : $actual === $origen[$accion];
    if (!$valido) {
        $db->rollBack();
        pedidoResponder(409, ['success' => false, 'message' => "No se puede aplicar la acción sobre un pedido en estado '$actual'"]);
    }

    $observacion = '';
    switch ($accion) {
        case 'transito':
            $picking = $in['picking'] ?? null;
            if (!is_array($picking)) {
                $db->rollBack();
                pedidoResponder(422, ['success' => false, 'message' => 'Captura las cantidades surtidas de cada partida']);
            }
            $info = $db->prepare(
                'SELECT m.MovimientoID, m.ProductoID, m.Cantidad, m.PrecioFinal, p.UPC, p.Nombre, p.Tipo, p.Cantidad AS Stock
                 FROM MovimientosInventario m INNER JOIN Productos p ON p.ProductoID = m.ProductoID
                 WHERE m.ComandaID = :c AND m.TipoMovimiento = \'Salida\'
                 ORDER BY m.ProductoID FOR UPDATE'
            );
            $info->execute([':c' => $comanda]);
            $partidas = $info->fetchAll(PDO::FETCH_ASSOC);
            if (count($partidas) !== count($filas)) {
                $db->rollBack();
                pedidoResponder(409, ['success' => false, 'message' => 'El pedido contiene productos que ya no existen en el catálogo']);
            }

            $errores = [];
            $cambios = [];
            $totalSurtido = 0.0;
            foreach ($partidas as $f) {
                $raw = $picking[$f['MovimientoID']] ?? null;
                $nombre = $f['Nombre'];
                if ($raw === null || !is_numeric($raw) || !is_finite((float) $raw)) {
                    $errores[] = "'$nombre' no tiene cantidad surtida";
                    continue;
                }
                $surtida = round((float) $raw, 3);
                $pedida = (float) $f['Cantidad'];
                if ($surtida < 0) {
                    $errores[] = "'$nombre' no puede ser negativa";
                    continue;
                }
                if (\App\Services\Unidades::esEntero($f['Tipo']) && abs($surtida - round($surtida)) > 0.0001) {
                    $errores[] = "'$nombre' solo admite cantidades enteras";
                    continue;
                }
                if ($surtida > $pedida * 1.5 + 0.0001) {
                    $errores[] = "'$nombre': no puedes surtir más de 50% sobre lo pedido (" . \App\Services\Unidades::formato($f['Tipo'], $pedida) . ')';
                    continue;
                }
                $extra = $surtida - $pedida;
                if ($extra > 0 && $extra > (float) $f['Stock'] + 0.0001) {
                    $errores[] = "'$nombre': solo quedan " . \App\Services\Unidades::formato($f['Tipo'], (float) $f['Stock']) . ' extra en inventario';
                    continue;
                }
                // Precio unitario con IVA tal como se cotizó al crear el pedido
                $unitario = $pedida > 0 ? (float) $f['PrecioFinal'] / $pedida : 0.0;
                $f['surtida'] = $surtida;
                $f['importe'] = round($unitario * $surtida, 2);
                $totalSurtido += $surtida;
                $cambios[] = $f;
            }
            if (!$errores && $totalSurtido <= 0) {
                $errores[] = 'Debes surtir al menos un producto; si no hay nada que enviar, cancela el pedido';
            }
            if ($errores) {
                $db->rollBack();
                pedidoResponder(422, ['success' => false, 'message' => 'Surtido inválido: ' . implode('; ', $errores)]);
            }

            $ins = $db->prepare(
                'INSERT INTO PickingLog (ComandaID, ProductoID, UPC, CantidadSolicitada, CantidadPickeada, FechaPicking, UsuarioID)
                 VALUES (:c, :p, :u, :s, :k, NOW(), :usr)'
            );
            $updMov = $db->prepare('UPDATE MovimientosInventario SET Cantidad = :q, PrecioFinal = :i WHERE MovimientoID = :m');
            $updStock = $db->prepare('UPDATE Productos SET Cantidad = Cantidad - :d WHERE ProductoID = :p');
            $logStock = $db->prepare('INSERT INTO Logs_stock (UPC, StockBefore, StockAfter, UsuarioID, Fecha) VALUES (:u, :a, :d, :usr, NOW())');
            $ajustes = 0;
            foreach ($cambios as $f) {
                $pedida = (float) $f['Cantidad'];
                $ins->execute([':c' => $comanda, ':p' => $f['ProductoID'], ':u' => $f['UPC'] ?: 'SIN-UPC-' . $f['ProductoID'], ':s' => $pedida, ':k' => $f['surtida'], ':usr' => $userId]);
                if (abs($f['surtida'] - $pedida) > 0.0001) {
                    $ajustes++;
                    // Sale más de lo apartado -> baja stock; sale menos -> se devuelve la diferencia
                    $dif = round($f['surtida'] - $pedida, 3);
                    $updStock->execute([':d' => $dif, ':p' => $f['ProductoID']]);
                    $logStock->execute([':u' => $f['UPC'], ':a' => $f['Stock'], ':d' => round((float) $f['Stock'] - $dif, 3), ':usr' => $userId]);
                }
                $updMov->execute([':q' => $f['surtida'], ':i' => $f['importe'], ':m' => $f['MovimientoID']]);
            }
            $nuevo = 'En transito';
            $observacion = $ajustes > 0 ? "Surtido con $ajustes ajuste(s) de cantidad; total recalculado" : 'Surtido completo, sin cambios';
            break;

        case 'entregar':
            $nuevo = 'Cerrado';
            $observacion = 'Pedido entregado';
            break;

        default:
            // El stock se descontó al crear la comanda: al cancelar se repone
            $repone = $db->prepare('UPDATE Productos SET Cantidad = Cantidad + :q WHERE ProductoID = :p');
            foreach ($filas as $f) {
                $repone->execute([':q' => $f['Cantidad'], ':p' => $f['ProductoID']]);
            }
            $nuevo = 'Cancelado';
            $observacion = 'Cancelado: ' . $motivo;
    }

    $upd = $db->prepare(
        "UPDATE MovimientosInventario SET Status = :n
         WHERE ComandaID = :c AND TipoMovimiento = 'Salida' AND Status = :a"
    );
    $upd->execute([':n' => $nuevo, ':c' => $comanda, ':a' => $actual]);

    $est = $db->prepare(
        'INSERT INTO EstadosComanda (ComandaID, EstadoAnterior, EstadoNuevo, FechaCambio, UsuarioID, Observaciones)
         VALUES (:c, :a, :n, NOW(), :u, :o)'
    );
    $est->execute([':c' => $comanda, ':a' => $actual, ':n' => $nuevo, ':u' => $userId, ':o' => $observacion]);

    $db->commit();

    $id = htmlspecialchars($comanda, ENT_QUOTES, 'UTF-8');
    if ($nuevo === 'En transito') {
        pedidoNotificar("Comanda: $comanda en transito", "<p>La siguiente comanda: <strong>$id</strong></p><p>Ya va en camino, recuerda verificar tu pedido en cuanto llegue a sucursal.</p>");
    } elseif ($nuevo === 'Cerrado') {
        pedidoNotificar("Comanda entregada: $comanda", "<p>La comanda <strong>$id</strong> fue marcada como entregada.</p>");
    } else {
        pedidoNotificar("Comanda Cancelada: $comanda", "<p>Se ha generado una cancelacion de la siguiente comanda: <strong>$id</strong></p><p>Motivo: " . htmlspecialchars($motivo, ENT_QUOTES, 'UTF-8') . '</p><p>El stock reservado pasara a ser disponible nuevamente.</p>');
    }

    pedidoResponder(200, ['success' => true, 'message' => 'Pedido actualizado', 'estado' => $nuevo]);
}
