<?php

declare(strict_types=1);

/**
 * Arranque común de la aplicación: autoload, .env, zona horaria, errores y sesión.
 */

use App\Core\Config;
use App\Core\Session;

$raiz = dirname(__DIR__);

$autoload = $raiz . '/vendor/autoload.php';
if (!is_file($autoload)) {
    http_response_code(500);
    exit('Dependencias no instaladas. Ejecute "composer install" en la raíz del proyecto.');
}
require_once $autoload;

Config::load($raiz);

date_default_timezone_set((string) config('app.timezone', 'UTC'));

if (config('app.debug')) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', config('paths.logs') . '/php-error.log');
}

// Constantes heredadas por las vistas (valores provenientes de .env)
$constantes = [
    'APP_NAME' => config('app.name'),
    'APP_SHORT_NAME' => config('app.short_name'),
    'APP_VERSION' => config('app.version'),
    'APP_BUILD' => config('app.build'),
    'APP_RELEASE_DATE' => config('app.release_date'),
    'APP_COMPANY' => config('app.company'),
    'APP_YEAR' => config('app.year'),
    'MODULE_PRODUCTS' => config('app.modules.products'),
    'MODULE_CATEGORIES' => config('app.modules.categories'),
    'MODULE_TAGS' => config('app.modules.tags'),
    'MODULE_ORDERS' => config('app.modules.orders'),
    'MODULE_USERS' => config('app.modules.users'),
    'TAX_RATE' => (float) config('app.tax_rate'),
    'TAX_MULTIPLIER' => 1 + (float) config('app.tax_rate'),
    'BASE_PATH' => $raiz,
];
foreach ($constantes as $nombre => $valor) {
    if (!defined($nombre)) {
        define($nombre, $valor);
    }
}

Session::start();
