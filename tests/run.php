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

echo "Áreas /gestor y /solicitar" . PHP_EOL;
$_SESSION = [];
$r = $router->resolve('GET', '/gestor');
prueba('/gestor sin sesión -> login', $r['type'] === 'redirect' && str_contains($r['location'], 'page=login'));
$_SESSION = ['id' => 5, 'usuario' => 'x', 'rol' => 'Supervisor'];
$r = $router->resolve('GET', '/gestor/dashboard');
prueba('Supervisor no entra al Gestor', $r['type'] === 'redirect' && str_ends_with($r['location'], '/solicitar'));
$r = $router->resolve('GET', '/solicitar');
prueba('Supervisor entra a Solicitar', $r['type'] === 'area' && $r['area'] === 'solicitar' && $r['page'] === 'catalogo');
$r = $router->resolve('GET', '/solicitar/../config');
prueba('área con traversal -> 404', $r['type'] === 'error' && $r['status'] === 404);
$_SESSION['rol'] = 'Logistica';
$r = $router->resolve('GET', '/gestor/pedidos');
prueba('Logistica entra a pedidos', $r['type'] === 'area' && $r['page'] === 'pedidos');
$r = $router->resolve('GET', '/gestor/usuarios');
prueba('Logistica no entra a usuarios', $r['type'] === 'redirect');
$_SESSION['rol'] = 'Administrador';
$r = $router->resolve('GET', '/gestor');
prueba('Administrador entra al panel', $r['type'] === 'area' && $r['page'] === 'dashboard');
foreach (['dashboard', 'pedidos', 'productos', 'categorias', 'etiquetas', 'inventario', 'sucursales', 'usuarios', 'configuracion'] as $p) {
    prueba("vista del Gestor existe: {$p}", is_file(base_path("views/gestor/pages/{$p}.php")));
}

echo "Unidades" . PHP_EOL;
use App\Services\Unidades;
prueba('tipos: Unidad, Pesable, Litro, Manojo', Unidades::tipos() === ['Unidad', 'Pesable', 'Litro', 'Manojo']);
prueba('Manojo es entero y Litro decimal', Unidades::esEntero('Manojo') && !Unidades::esEntero('Litro'));
prueba('Manojo rechaza 1.5', Unidades::validar('Manojo', 1.5)[0] === null);
prueba('Litro acepta 2.5', Unidades::validar('Litro', '2.5')[0] === 2.5);
prueba('rechaza 0, negativos y texto', Unidades::validar('Litro', 0)[0] === null && Unidades::validar('Pesable', -1)[0] === null && Unidades::validar('Unidad', 'x')[0] === null);
prueba('formato "3 manojos" y "1 L"', Unidades::formato('Manojo', 3) === '3 manojos' && Unidades::formato('Litro', 1) === '1 L');
prueba('tipo inválido no existe', !Unidades::esValido('Galon'));

echo "Requisiciones (interpretación de texto)" . PHP_EOL;
use App\Services\Requisiciones;
$caso = static fn (string $t) => Requisiciones::interpretarLinea($t);
prueba('"10 lechugas" -> 10 / lechugas', ($c = $caso('10 lechugas')) && $c['cantidad'] === 10.0 && $c['tipo'] === null && $c['nombre'] === 'lechugas');
prueba('"2 col" no confunde la unidad', ($c = $caso('2 col')) && $c['cantidad'] === 2.0 && $c['nombre'] === 'col');
prueba('"5kg de queso" -> 5 Pesable queso', ($c = $caso('5kg de queso')) && $c['cantidad'] === 5.0 && $c['tipo'] === 'Pesable' && $c['nombre'] === 'queso');
prueba('"queso 5 kg" (cantidad al final)', ($c = $caso('queso 5 kg')) && $c['cantidad'] === 5.0 && $c['tipo'] === 'Pesable' && $c['nombre'] === 'queso');
prueba('"1,5 L leche" decimal con coma', ($c = $caso('1,5 L leche')) && $c['cantidad'] === 1.5 && $c['tipo'] === 'Litro');
prueba('"500 g de jamón" -> 0.5 kg', ($c = $caso('500 g de jamón')) && $c['cantidad'] === 0.5 && $c['tipo'] === 'Pesable');
prueba('"medio kilo de queso" -> 0.5', ($c = $caso('medio kilo de queso')) && $c['cantidad'] === 0.5);
prueba('"3 manojos de cilantro"', ($c = $caso('3 manojos de cilantro')) && $c['tipo'] === 'Manojo' && $c['nombre'] === 'cilantro');
prueba('texto sin cantidad o con 0 se rechaza', $caso('queso') === null && $caso('0 queso') === null);
prueba('dividir: comas, saltos y decimales', Requisiciones::dividir("10 lechugas, 2 col\n1,5 kg queso; 3 limones") === ['10 lechugas', '2 col', '1,5 kg queso', '3 limones']);
prueba('puntuar: plural y palabra completa', Requisiciones::puntuar('Lechuga', 'lechugas') === 0 && Requisiciones::puntuar('Lechuga Romana', 'lechugas') === 1);
prueba('puntuar: "col" no es coliflor segura', Requisiciones::puntuar('Coliflor', 'col') === 2 && Requisiciones::puntuar('Col morada', 'col') === 1);
prueba('puntuar: sin relación', Requisiciones::puntuar('Aceite', 'queso') === 9);

echo "Estructura" . PHP_EOL;
foreach (($rutas = require base_path('config/routes.php'))['page_roles'] as $pagina => $_) {
    prueba("page_roles apunta a una vista real: {$pagina}", is_file(base_path("views/pages/{$pagina}.php")));
}
foreach ($rutas['api_roles'] as $regla) {
    prueba('api_roles apunta a un endpoint real: ' . $regla['prefix'], glob(base_path('src/Api/' . $regla['prefix'] . '*')) !== []);
}

echo PHP_EOL . ($total - $fallos) . "/{$total} pruebas correctas" . PHP_EOL;
exit($fallos === 0 ? 0 : 1);
