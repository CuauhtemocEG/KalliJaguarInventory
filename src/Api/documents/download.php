<?php
/**
 * Entrega un PDF de comanda almacenado en storage/documents (requiere sesión; ver Router).
 * GET /api/documents/download.php?ComandaID=XXXX
 */

$comandaID = basename((string) ($_GET['ComandaID'] ?? ''));
$archivo = config('paths.documents') . '/' . $comandaID . '.pdf';

if ($comandaID === '' || !is_file($archivo)) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Documento no encontrado.';
    exit;
}

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . $comandaID . '.pdf"');
header('Content-Length: ' . filesize($archivo));
header('X-Content-Type-Options: nosniff');
readfile($archivo);
