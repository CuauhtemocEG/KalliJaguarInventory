<?php

declare(strict_types=1);

namespace App\Core;

use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\RoleMiddleware;

/**
 * Router simple del front controller (public/index.php).
 *
 *  - "/" y "/index.php?page=xxx"  -> views/pages/xxx.php dentro del layout principal
 *  - "/api/<ruta>.php"            -> src/Api/<ruta>.php
 *
 * Aplica CSRF, autenticación y control de roles según config/routes.php.
 * resolve() devuelve un arreglo descriptivo: el front controller es quien
 * incluye el archivo, así los scripts se ejecutan en el ámbito global.
 */
final class Router
{
    private array $routes;

    public function __construct(?array $routes = null)
    {
        $this->routes = $routes ?? (array) Config::get('routes', []);
    }

    /** Ruta de la petición sin el prefijo de instalación (subcarpeta de APP_URL). */
    public static function requestPath(string $uri): string
    {
        $path = rawurldecode((string) parse_url($uri, PHP_URL_PATH));
        $prefix = rtrim((string) parse_url((string) config('app.url', ''), PHP_URL_PATH), '/');
        if ($prefix !== '' && str_starts_with($path, $prefix . '/')) {
            $path = substr($path, strlen($prefix));
        }
        return '/' . trim($path, '/');
    }

    /**
     * @return array{type:string, file?:string, page?:string, status?:int, message?:string, json?:bool}
     */
    public function resolve(string $method, string $uri, array $query = []): array
    {
        $method = strtoupper($method);
        $path = self::requestPath($uri);

        if ($path === '/' || $path === '/index.php') {
            return $this->resolvePage($method, (string) ($query['page'] ?? ''));
        }

        if (str_starts_with($path, '/api/')) {
            return $this->resolveApi($method, substr($path, 5));
        }

        return ['type' => 'error', 'status' => 404, 'message' => 'Recurso no encontrado.', 'json' => false];
    }

    private function resolvePage(string $method, string $page): array
    {
        $page = $page === '' ? (string) ($this->routes['default_page'] ?? 'login') : $page;

        // Solo nombres simples: evita path traversal ("../") y rutas arbitrarias
        if (!preg_match('/^[A-Za-z0-9_]+$/', $page)) {
            return ['type' => 'page', 'page' => '404'];
        }

        if (!is_file(base_path("views/pages/{$page}.php"))) {
            return ['type' => 'page', 'page' => '404'];
        }

        if ($error = CsrfMiddleware::handle($method)) {
            return $this->error($error, false);
        }

        if (in_array($page, (array) ($this->routes['public_pages'] ?? []), true)) {
            return ['type' => 'page', 'page' => $page];
        }

        // Sin sesión: se mantiene el comportamiento histórico (cerrar sesión y volver al login)
        if (AuthMiddleware::handle() !== null) {
            return ['type' => 'page', 'page' => 'logout'];
        }

        $roles = $this->routes['page_roles'][$page] ?? [];
        if ($error = RoleMiddleware::handle($roles)) {
            return $this->error($error, false);
        }

        return ['type' => 'page', 'page' => $page];
    }

    private function resolveApi(string $method, string $relative): array
    {
        // Solo "<carpeta>/<archivo>.php" con caracteres seguros; sin ".." ni rutas absolutas
        if (!preg_match('#^[A-Za-z0-9_\-]+(/[A-Za-z0-9_\-]+)*\.php$#', $relative)) {
            return ['type' => 'error', 'status' => 404, 'message' => 'Endpoint no encontrado.', 'json' => true];
        }

        $root = realpath(base_path('src/Api'));
        $file = realpath(base_path('src/Api/' . $relative));
        if ($root === false || $file === false || !str_starts_with($file, $root . DIRECTORY_SEPARATOR)) {
            return ['type' => 'error', 'status' => 404, 'message' => 'Endpoint no encontrado.', 'json' => true];
        }

        if ($method === 'OPTIONS') {
            return ['type' => 'api', 'file' => $file];
        }

        if ($error = CsrfMiddleware::handle($method)) {
            return $this->error($error, true);
        }

        $isPublic = in_array($relative, (array) ($this->routes['public_api'] ?? []), true)
            || ($method === 'GET' && in_array($relative, (array) ($this->routes['public_api_get'] ?? []), true));

        if (!$isPublic) {
            if ($error = AuthMiddleware::handle()) {
                return $this->error($error, true);
            }

            foreach ((array) ($this->routes['api_roles'] ?? []) as $rule) {
                $matches = str_starts_with($relative, $rule['prefix']);
                $methods = $rule['methods'] ?? null;
                if ($matches && ($methods === null || in_array($method, $methods, true))) {
                    if ($error = RoleMiddleware::handle($rule['roles'])) {
                        return $this->error($error, true);
                    }
                }
            }
        }

        return ['type' => 'api', 'file' => $file];
    }

    private function error(array $error, bool $json): array
    {
        return ['type' => 'error', 'status' => $error['status'], 'message' => $error['message'], 'json' => $json];
    }
}
