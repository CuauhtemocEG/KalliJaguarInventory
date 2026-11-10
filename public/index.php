<?php

declare(strict_types=1);

/**
 * Front controller: único punto de entrada de la aplicación.
 * El servidor web debe apuntar su DocumentRoot a este directorio (public/).
 */

// Servidor embebido de PHP (php -S): servir directamente los archivos estáticos
if (PHP_SAPI === 'cli-server') {
    $ruta = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $archivo = realpath(__DIR__ . $ruta);
    if ($ruta !== '/' && $archivo !== false && is_file($archivo)
        && str_starts_with($archivo, __DIR__ . DIRECTORY_SEPARATOR)
        && basename($archivo) !== 'index.php') {
        return false;
    }
}

require dirname(__DIR__) . '/config/bootstrap.php';
require_once BASE_PATH . '/src/Helpers/legacy.php';

use App\Core\Router;

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');

$router = new Router();
$destino = $router->resolve($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'], $_GET);

switch ($destino['type']) {
    case 'page':
        $page = $destino['page'];
        require BASE_PATH . '/views/layouts/main.php';
        break;

    case 'area':
        $page = $destino['page'];
        require BASE_PATH . "/views/{$destino['area']}/layout.php";
        break;

    case 'redirect':
        header('Location: ' . $destino['location']);
        break;

    case 'api':
        require $destino['file'];
        break;

    default:
        http_response_code($destino['status'] ?? 404);
        if (!empty($destino['json'])) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => $destino['message'] ?? 'Error'], JSON_UNESCAPED_UNICODE);
        } else {
            header('Content-Type: text/plain; charset=utf-8');
            echo $destino['message'] ?? 'Error';
        }
}
