<?php

$raiz = dirname(__DIR__);
$uploadsDir = trim((string) env('UPLOADS_DIR', 'public/uploads'), '/');
$uploads = $raiz . '/' . $uploadsDir;
$productos = trim((string) env('UPLOADS_PRODUCTS_DIR', 'products'), '/');

return [
    'root' => $raiz,
    'uploads' => $uploads,
    // URL pública relativa a APP_URL: UPLOADS_URL, o UPLOADS_DIR sin el prefijo "public/".
    'uploads_url' => trim((string) env('UPLOADS_URL', preg_replace('#^public/#', '', $uploadsDir)), '/'),
    'products' => $uploads . '/' . $productos,
    'products_url' => $productos,
    'logs' => $raiz . '/' . trim((string) env('LOGS_DIR', 'storage/logs'), '/'),
    'documents' => $raiz . '/' . trim((string) env('DOCUMENTS_DIR', 'storage/documents'), '/'),
    'fonts' => $raiz . '/' . trim((string) env('FONTS_DIR', 'storage/fonts'), '/'),
    'upload_max_bytes' => (int) ((float) env('UPLOAD_MAX_SIZE_MB', 5) * 1024 * 1024),
];
