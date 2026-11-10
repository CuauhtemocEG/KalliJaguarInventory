<?php
/**
 * PDF de una requisición. GET ?id=N[&download=1]
 * El solicitante solo ve las suyas; Administrador y Logística ven cualquiera (y los costos).
 */

use App\Core\Auth;
use App\Services\RequisicionPdf;
use App\Services\Requisiciones;

require_once BASE_PATH . '/src/Helpers/legacy.php';

function reqPdfError(int $status, string $mensaje): never
{
    http_response_code($status);
    header('Content-Type: text/plain; charset=utf-8');
    echo $mensaje;
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    reqPdfError(405, 'Método no permitido');
}

$db = conexion();
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$uid = (int) ($_SESSION['id'] ?? 0);
$esGestor = Auth::hasRole(['Administrador', 'Logistica']);

try {
    $r = Requisiciones::detalle($db, (int) ($_GET['id'] ?? 0));
    if (!$r || (!$esGestor && (int) $r['SolicitanteID'] !== $uid)) {
        reqPdfError(404, 'Requisición no encontrada');
    }
    $pdf = RequisicionPdf::generar($db, $r, $esGestor);
} catch (Throwable $e) {
    error_log('requisicionPdf: ' . $e->getMessage());
    reqPdfError(500, 'No se pudo generar el PDF');
}

header('Content-Type: application/pdf');
header('Content-Disposition: ' . (($_GET['download'] ?? '') === '1' ? 'attachment' : 'inline') . '; filename="' . $r['Folio'] . '.pdf"');
header('Content-Length: ' . strlen($pdf));
header('Cache-Control: private, no-store');
echo $pdf;
