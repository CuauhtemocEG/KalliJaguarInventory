<?php

declare(strict_types=1);

use App\Core\Config;

/**
 * Funciones globales de ayuda (cargadas por Composer: "autoload.files").
 */

if (!function_exists('env')) {
    /** Lee una variable de entorno (.env). */
    function env(string $key, mixed $default = null): mixed
    {
        return Config::env($key, $default);
    }
}

if (!function_exists('config')) {
    /** Lee un valor de config/*.php con notación de punto. */
    function config(string $key, mixed $default = null): mixed
    {
        return Config::get($key, $default);
    }
}

if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        return Config::basePath($path);
    }
}

if (!function_exists('e')) {
    /** Escapa una cadena para salida HTML. */
    function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('app_url')) {
    /** URL absoluta basada en APP_URL (o relativa al sitio si no está definida). */
    function app_url(string $path = ''): string
    {
        return rtrim((string) config('app.url', ''), '/') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    /** URL de un recurso estático en public/assets. */
    function asset(string $path): string
    {
        return app_url('assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('upload_url')) {
    /** URL de un archivo subido (public/uploads). */
    function upload_url(string $path): string
    {
        return app_url(trim((string) config('paths.uploads_url', 'uploads'), '/') . '/' . ltrim($path, '/'));
    }
}

if (!function_exists('product_upload_url')) {
    /** URL base (o de un archivo concreto) de las imágenes de productos subidas. */
    function product_upload_url(string $file = ''): string
    {
        return upload_url(config('paths.products_url') . '/' . ($file === '' ? '' : rawurlencode(basename($file))));
    }
}

if (!function_exists('product_image_url')) {
    /** URL de la imagen de un producto o del marcador de posición. */
    function product_image_url(?string $image): string
    {
        $image = $image !== null ? basename($image) : '';
        if ($image !== '' && is_file(config('paths.products') . '/' . $image)) {
            return product_upload_url($image);
        }
        return asset('img/producto.png');
    }
}

if (!function_exists('csrf_field')) {
    /** Campo oculto con el token CSRF para formularios. */
    function csrf_field(): string
    {
        return '<input type="hidden" name="_csrf" value="' . e(\App\Core\Csrf::token()) . '">';
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return \App\Core\Csrf::token();
    }
}
