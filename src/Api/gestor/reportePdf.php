<?php
/**
 * Reportes PDF del Gestor, generados al momento.
 * GET /api/gestor/reportePdf.php?reporte=comandas|consolidado|inventario[&desde&hasta&sucursal&estado&tag&tipo&bajo][&download=1]
 */

use App\Services\Reportes;
use App\Services\Unidades;

require_once BASE_PATH . '/src/Helpers/legacy.php';

function reporteError(int $status, string $mensaje): never
{
    http_response_code($status);
    header('Content-Type: text/plain; charset=utf-8');
    echo $mensaje;
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    reporteError(405, 'Método no permitido');
}

$fecha = static function (string $clave, string $porDefecto): string {
    $v = (string) ($_GET[$clave] ?? '');
    $d = DateTime::createFromFormat('Y-m-d', $v);
    return $d && $d->format('Y-m-d') === $v ? $v : $porDefecto;
};
$desde = $fecha('desde', date('Y-m-01'));
$hasta = $fecha('hasta', date('Y-m-d'));
if ($desde > $hasta) {
    [$desde, $hasta] = [$hasta, $desde];
}
if ((strtotime($hasta) - strtotime($desde)) / 86400 > 731) {
    reporteError(422, 'El rango máximo es de 2 años');
}

$sucursal = (int) ($_GET['sucursal'] ?? 0) ?: null;
$estado = (string) ($_GET['estado'] ?? '');
if ($estado !== '' && !in_array($estado, ['Abierto', 'En transito', 'Cerrado', 'Cancelado'], true)) {
    reporteError(422, 'Estado inválido');
}
$tipo = (string) ($_GET['tipo'] ?? '');
if ($tipo !== '' && !Unidades::esValido($tipo)) {
    reporteError(422, 'Tipo de producto inválido');
}
$tag = trim((string) ($_GET['tag'] ?? ''));

$db = conexion();
try {
    $pdf = match ((string) ($_GET['reporte'] ?? '')) {
        'comandas' => Reportes::comandas($db, $desde, $hasta, $sucursal, $estado ?: null),
        'consolidado' => Reportes::consolidado($db, $desde, $hasta, $sucursal),
        'inventario' => Reportes::inventario($db, $tag ?: null, $tipo ?: null, ($_GET['bajo'] ?? '') === '1'),
        default => reporteError(404, 'Reporte no encontrado'),
    };
} catch (Throwable $e) {
    error_log('reportePdf: ' . $e->getMessage());
    reporteError(500, 'No se pudo generar el reporte');
}

$nombre = $_GET['reporte'] . '_' . date('Ymd_His') . '.pdf';
header('Content-Type: application/pdf');
header('Content-Disposition: ' . (($_GET['download'] ?? '') === '1' ? 'attachment' : 'inline') . '; filename="' . $nombre . '"');
header('Content-Length: ' . strlen($pdf));
header('Cache-Control: private, no-store');
echo $pdf;
