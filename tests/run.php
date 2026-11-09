<?php

declare(strict_types=1);

/**
 * Pruebas ligeras sin dependencias externas (php tests/run.php).
 * Cubren Config/env, Router (rutas, traversal, roles) y CSRF. No requieren base de datos.
 */

use App\Core\Config;
use App\Core\Csrf;

putenv('APP_ENV=testing');
$_ENV['TEST_BOOL'] = 'true';
$_ENV['TEST_NULL'] = 'null';
$_ENV['TEST_TEXT'] = 'hola';

require dirname(__DIR__) . '/config/bootstrap.php';

$fallos = 0;
$total = 0;

function prueba(string $nombre, bool $ok): void
{
    global $fallos, $total;
    $total++;
    if (!$ok) {
        $fallos++;
    }
    echo ($ok ? '  ok   ' : '  FAIL ') . $nombre . PHP_EOL;
}

echo "Config / env" . PHP_EOL;
prueba('env() convierte "true" a bool', env('TEST_BOOL') === true);
prueba('env() convierte "null" a null', env('TEST_NULL') === null);
prueba('env() devuelve texto', env('TEST_TEXT') === 'hola');
prueba('env() usa el valor por defecto', env('NO_EXISTE_XYZ', 'x') === 'x');
prueba('config() lee config/app.php', config('app.name') !== null);
prueba('base_path() apunta a la raíz', is_file(base_path('composer.json')));
prueba('IVA coherente', abs(TAX_MULTIPLIER - (1 + TAX_RATE)) < 1e-9);

echo "Escape de salida" . PHP_EOL;
prueba('e() escapa HTML', e('<script>"x"</script>') === '&lt;script&gt;&quot;x&quot;&lt;/script&gt;');

echo "CSRF" . PHP_EOL;
$_SESSION = [];
$token = Csrf::token();
prueba('token no vacío', strlen($token) >= 32);
prueba('token estable en la sesión', Csrf::token() === $token);
prueba('valida token correcto', Csrf::validate($token));
prueba('rechaza token incorrecto', !Csrf::validate('incorrecto'));
prueba('rechaza token vacío', !Csrf::validate(''));

echo "Router y middleware" . PHP_EOL;
$router = new \App\Core\Router();
$_SESSION = [];
$_SERVER['REQUEST_METHOD'] = 'GET';

$r = $router->resolve('GET', '/index.php', ['page' => 'login']);
prueba('login es público', $r['type'] === 'page' && $r['page'] === 'login');
$r = $router->resolve('GET', '/', ['page' => '../../etc/passwd']);
prueba('page con traversal -> 404', $r['page'] === '404');
$r = $router->resolve('GET', '/', ['page' => 'noExiste']);
prueba('page inexistente -> 404', $r['page'] === '404');
$r = $router->resolve('GET', '/', ['page' => 'home']);
prueba('sin sesión -> logout', $r['page'] === 'logout');
$r = $router->resolve('GET', '/api/../config/app.php');
prueba('API con ".." -> 404', $r['type'] === 'error' && $r['status'] === 404);
$r = $router->resolve('GET', '/api/%2e%2e/config/app.php');
prueba('API con %2e%2e -> 404', $r['type'] === 'error' && $r['status'] === 404);
$r = $router->resolve('GET', '/api/AuthController/login.php');
prueba('login API accesible', $r['type'] === 'api');
$r = $router->resolve('GET', '/api/updateStock.php');
prueba('API privada sin sesión -> 401', $r['type'] === 'error' && $r['status'] === 401);
$r = $router->resolve('POST', '/api/AuthController/login.php');
prueba('POST sin CSRF -> 419', $r['type'] === 'error' && $r['status'] === 419);

$_SESSION = ['id' => 5, 'usuario' => 'x', 'rol' => 'Supervisor'];
$r = $router->resolve('GET', '/', ['page' => 'userManagement']);
prueba('Supervisor no entra a userManagement', $r['type'] === 'error' && $r['status'] === 403);
$r = $router->resolve('GET', '/', ['page' => 'home']);
prueba('Supervisor entra a home', $r['page'] === 'home');
$_SESSION['rol'] = 'Administrador';
$r = $router->resolve('GET', '/', ['page' => 'userManagement']);
prueba('Administrador entra a userManagement', $r['page'] === 'userManagement');

echo "Estructura" . PHP_EOL;
foreach (($rutas = require base_path('config/routes.php'))['page_roles'] as $pagina => $_) {
    prueba("page_roles apunta a una vista real: {$pagina}", is_file(base_path("views/pages/{$pagina}.php")));
}
foreach ($rutas['api_roles'] as $regla) {
    prueba('api_roles apunta a un endpoint real: ' . $regla['prefix'], glob(base_path('src/Api/' . $regla['prefix'] . '*')) !== []);
}

echo PHP_EOL . ($total - $fallos) . "/{$total} pruebas correctas" . PHP_EOL;
exit($fallos === 0 ? 0 : 1);
