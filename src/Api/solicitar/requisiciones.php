<?php
/**
 * Requisiciones del Supervisor (lista de requerimientos).
 *   GET  (sin params)           -> mis requisiciones
 *   GET  ?id=N                  -> detalle (solo propias; Administrador/Logistica ven cualquiera)
 *   GET  ?buscar=texto          -> productos del catálogo para elegir
 *   POST {accion:'interpretar', texto}                          -> líneas propuestas a partir de texto libre
 *   POST {accion:'crear', sucursal_id?, fecha_requerida?, notas?, lineas:[{producto_id?|descripcion, cantidad, tipo?}]}
 *   POST {accion:'cancelar', id}                                -> cancela una propia aún sin aprobar
 */

use App\Core\Auth;
use App\Services\RequisicionException;
use App\Services\Requisiciones;

require_once BASE_PATH . '/src/Helpers/legacy.php';
header('Content-Type: application/json; charset=utf-8');

function reqResponder(bool $ok, array $extra = [], int $code = 200): never
{
    http_response_code($code);
    echo json_encode(['success' => $ok] + $extra, JSON_UNESCAPED_UNICODE);
    exit;
}

$db = conexion();
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$uid = (int) ($_SESSION['id'] ?? 0);
$esGestor = Auth::hasRole(['Administrador', 'Logistica']);

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['buscar'])) {
            $q = trim((string) $_GET['buscar']);
            $st = $db->prepare('SELECT ProductoID, Nombre, Tipo, Cantidad FROM Productos WHERE Activo = 1 AND Nombre LIKE :q ORDER BY Nombre LIMIT 15');
            $st->execute([':q' => '%' . addcslashes($q, '%_\\') . '%']);
            reqResponder(true, ['productos' => $st->fetchAll(PDO::FETCH_ASSOC)]);
        }
        if (isset($_GET['id'])) {
            $r = Requisiciones::detalle($db, (int) $_GET['id']);
            if (!$r || (!$esGestor && (int) $r['SolicitanteID'] !== $uid)) {
                reqResponder(false, ['message' => 'Requisición no encontrada'], 404);
            }
            reqResponder(true, ['requisicion' => $r]);
        }
        reqResponder(true, ['requisiciones' => Requisiciones::listar($db, $uid)]);
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        reqResponder(false, ['message' => 'Método no permitido'], 405);
    }
    $in = json_decode((string) file_get_contents('php://input'), true);
    if (!is_array($in)) {
        reqResponder(false, ['message' => 'Solicitud no válida'], 400);
    }

    switch ($in['accion'] ?? '') {
        case 'interpretar':
            $lineas = Requisiciones::parsear($db, (string) ($in['texto'] ?? ''));
            if (!$lineas) {
                reqResponder(false, ['message' => 'Escribe tu lista, por ejemplo: 10 lechugas, 2 col, 5 kg de queso'], 422);
            }
            reqResponder(true, ['lineas' => $lineas]);
        case 'crear':
            $r = Requisiciones::crear($db, $uid, $in);
            reqResponder(true, ['message' => 'Requisición ' . $r['folio'] . ' enviada', 'data' => $r], 201);
        case 'cancelar':
            Requisiciones::cancelar($db, (int) ($in['id'] ?? 0), $uid);
            reqResponder(true, ['message' => 'Requisición cancelada']);
        default:
            reqResponder(false, ['message' => 'Acción no válida'], 400);
    }
} catch (RequisicionException $e) {
    reqResponder(false, ['message' => $e->getMessage()], $e->getCode() ?: 422);
} catch (Throwable $e) {
    error_log('requisiciones: ' . $e->getMessage());
    reqResponder(false, ['message' => 'Error interno al procesar la requisición'], 500);
}
