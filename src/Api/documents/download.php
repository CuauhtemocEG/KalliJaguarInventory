<?php
/**
 * PDF de una comanda, generado al momento desde la base de datos (requiere sesión; ver Router).
 * GET /api/documents/download.php?ComandaID=XXXX[&download=1]
 *
 * Administrador y Logistica ven cualquier comanda; el resto solo las propias.
 */

use App\Core\Auth;
use App\Services\ComandaPdf;

function pdfError(int $status, string $message): never
{
    http_response_code($status);
    header('Content-Type: text/plain; charset=utf-8');
    echo $message;
    exit;
}

$comandaID = (string) ($_GET['ComandaID'] ?? '');
if (!preg_match('/^[A-Za-z0-9_\-]{1,50}$/', $comandaID)) {
    pdfError(400, 'Folio inválido.');
}

$db = conexion();

if (!Auth::hasRole(['Administrador', 'Logistica'])) {
    $propia = $db->prepare("SELECT 1 FROM MovimientosInventario WHERE ComandaID = :c AND UsuarioID = :u AND TipoMovimiento = 'Salida' LIMIT 1");
    $propia->execute([':c' => $comandaID, ':u' => (int) ($_SESSION['id'] ?? 0)]);
    if (!$propia->fetchColumn()) {
        pdfError(404, 'Documento no encontrado.');
    }
}

try {
    $pdf = ComandaPdf::render($db, $comandaID);
} catch (RuntimeException $e) {
    pdfError(404, 'Documento no encontrado.');
}

header('Content-Type: application/pdf');
header('Content-Disposition: ' . (isset($_GET['download']) ? 'attachment' : 'inline') . '; filename="' . $comandaID . '.pdf"');
header('Content-Length: ' . strlen($pdf));
header('Cache-Control: private, no-store');
header('X-Content-Type-Options: nosniff');
echo $pdf;
