<?php

$raiz = dirname(__DIR__);
$uploads = $raiz . '/' . trim((string) env('UPLOADS_DIR', 'public/uploads'), '/');
$productos = trim((string) env('UPLOADS_PRODUCTS_DIR', 'products'), '/');

return [
    'root' => $raiz,
    'uploads' => $uploads,
    'uploads_url' => 'uploads',
    'products' => $uploads . '/' . $productos,
    'products_url' => $productos,
    'logs' => $raiz . '/' . trim((string) env('LOGS_DIR', 'storage/logs'), '/'),
    'documents' => $raiz . '/' . trim((string) env('DOCUMENTS_DIR', 'storage/documents'), '/'),
    'fonts' => $raiz . '/' . trim((string) env('FONTS_DIR', 'storage/fonts'), '/'),
    'upload_max_bytes' => (int) ((float) env('UPLOAD_MAX_SIZE_MB', 5) * 1024 * 1024),
];
