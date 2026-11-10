<?php
/**
 * Requisiciones en el Gestor (Administrador y Logística).
 *   GET  ?estado&q               -> listado
 *   GET  ?id=N                   -> detalle con líneas, stock y último costo
 *   GET  ?buscar=texto           -> productos del catálogo (para vincular líneas libres)
 *   POST {accion:'aprobar', id, proveedor?, lineas:[{id, cantidad_aprobada}]}
 *   POST {accion:'rechazar', id, motivo}
 *   POST {accion:'cancelar', id}
 *   POST {accion:'vincular', linea_id, producto_id}
 *   POST {accion:'recibir', id, lineas:[{id, cantidad, costo?}]}   -> da entrada al inventario
 *   POST {accion:'cerrar', id}                                     -> cierra una recepción parcial
 */

use App\Services\RequisicionException;
use App\Services\Requisiciones;

require_once BASE_PATH . '/src/Helpers/legacy.php';
header('Content-Type: application/json; charset=utf-8');

function gReqResponder(bool $ok, array $extra = [], int $code = 200): never
{
    http_response_code($code);
    echo json_encode(['success' => $ok] + $extra, JSON_UNESCAPED_UNICODE);
    exit;
}

$db = conexion();
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$uid = (int) ($_SESSION['id'] ?? 0);

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['buscar'])) {
            $st = $db->prepare('SELECT ProductoID, Nombre, Tipo, Cantidad FROM Productos WHERE Activo = 1 AND Nombre LIKE :q ORDER BY Nombre LIMIT 15');
            $st->execute([':q' => '%' . addcslashes(trim((string) $_GET['buscar']), '%_\\') . '%']);
            gReqResponder(true, ['productos' => $st->fetchAll(PDO::FETCH_ASSOC)]);
        }
        if (isset($_GET['id'])) {
            $r = Requisiciones::detalle($db, (int) $_GET['id']);
            $r ? gReqResponder(true, ['requisicion' => $r]) : gReqResponder(false, ['message' => 'Requisición no encontrada'], 404);
        }
        $estado = (string) ($_GET['estado'] ?? '');
        if ($estado !== '' && !in_array($estado, ['Enviada', 'Aprobada', 'Parcial', 'Recibida', 'Rechazada', 'Cancelada'], true)) {
            gReqResponder(false, ['message' => 'Estado inválido'], 422);
        }
        gReqResponder(true, ['requisiciones' => Requisiciones::listar($db, null, $estado, trim((string) ($_GET['q'] ?? '')), 300)]);
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        gReqResponder(false, ['message' => 'Método no permitido'], 405);
    }
    $in = json_decode((string) file_get_contents('php://input'), true);
    if (!is_array($in)) {
        gReqResponder(false, ['message' => 'Solicitud no válida'], 400);
    }
    $id = (int) ($in['id'] ?? 0);
    $lineas = is_array($in['lineas'] ?? null) ? $in['lineas'] : [];

    switch ($in['accion'] ?? '') {
        case 'aprobar':
            Requisiciones::aprobar($db, $id, $uid, $in['proveedor'] ?? null, $lineas);
            $msg = 'Requisición aprobada';
            break;
        case 'rechazar':
            Requisiciones::rechazar($db, $id, (string) ($in['motivo'] ?? ''));
            $msg = 'Requisición rechazada';
            break;
        case 'cancelar':
            Requisiciones::cancelar($db, $id, null);
            $msg = 'Requisición cancelada';
            break;
        case 'vincular':
            $linea = (int) ($in['linea_id'] ?? 0);
            Requisiciones::vincular($db, $linea, (int) ($in['producto_id'] ?? 0));
            $st = $db->prepare('SELECT RequisicionID FROM RequisicionLineas WHERE LineaID = ?');
            $st->execute([$linea]);
            $id = (int) $st->fetchColumn();
            $msg = 'Producto vinculado';
            break;
        case 'recibir':
            $estado = Requisiciones::recibir($db, $id, $uid, $lineas);
            $msg = $estado === 'Recibida' ? 'Recepción completa. Inventario actualizado.' : 'Recepción parcial registrada. Inventario actualizado.';
            break;
        case 'cerrar':
            Requisiciones::cerrar($db, $id);
            $msg = 'Requisición cerrada';
            break;
        default:
            gReqResponder(false, ['message' => 'Acción no válida'], 400);
    }
    gReqResponder(true, ['message' => $msg, 'requisicion' => Requisiciones::detalle($db, $id)]);
} catch (RequisicionException $e) {
    gReqResponder(false, ['message' => $e->getMessage()], $e->getCode() ?: 422);
} catch (Throwable $e) {
    error_log('gestor/requisiciones: ' . $e->getMessage());
    gReqResponder(false, ['message' => 'Error interno al procesar la requisición'], 500);
}
