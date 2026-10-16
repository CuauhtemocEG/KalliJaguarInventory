<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Configuración central de la aplicación.
 *
 * - Carga el archivo `.env` (vlucas/phpdotenv; si no estuviera instalado se
 *   usa un lector mínimo compatible).
 * - Carga los archivos de `config/*.php` y permite leerlos con notación de punto:
 *   Config::get('database.host').
 */
final class Config
{
    private static string $basePath = '';
    private static array $items = [];
    private static bool $loaded = false;

    public static function load(string $basePath): void
    {
        if (self::$loaded) {
            return;
        }

        self::$basePath = rtrim($basePath, '/\\');

        if (is_file(self::$basePath . '/.env')) {
            if (class_exists(\Dotenv\Dotenv::class)) {
                \Dotenv\Dotenv::createImmutable(self::$basePath)->safeLoad();
            } else {
                self::loadEnvFile(self::$basePath . '/.env');
            }
        }

        // Se marca como cargado antes de leer config/*.php porque estos usan env()
        self::$loaded = true;

        foreach (glob(self::$basePath . '/config/*.php') ?: [] as $file) {
            self::$items[basename($file, '.php')] = require $file;
        }
    }

    public static function basePath(string $path = ''): string
    {
        return $path === '' ? self::$basePath : self::$basePath . '/' . ltrim($path, '/\\');
    }

    /** Lee una variable de entorno con conversión de tipos (true/false/null). */
    public static function env(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        if ($value === false || $value === null) {
            return $default;
        }

        if (!is_string($value)) {
            return $value;
        }

        return match (strtolower($value)) {
            'true', '(true)' => true,
            'false', '(false)' => false,
            'null', '(null)' => null,
            'empty', '(empty)' => '',
            default => $value,
        };
    }

    /** Variable obligatoria: lanza excepción si no está definida o está vacía. */
    public static function requireEnv(string $key): string
    {
        $value = self::env($key);
        if ($value === null || $value === '') {
            throw new RuntimeException("Falta la variable de entorno obligatoria: {$key}");
        }
        return (string) $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $value = self::$items;
        foreach (explode('.', $key) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }
        return $value;
    }

    /** Lector mínimo de .env (solo se usa si phpdotenv no está disponible). */
    private static function loadEnvFile(string $file): void
    {
        foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
                continue;
            }
            [$name, $value] = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);

            if ($value !== '' && ($value[0] === '"' || $value[0] === "'")) {
                $quote = $value[0];
                $end = strrpos($value, $quote);
                $value = $end > 0 ? substr($value, 1, $end - 1) : substr($value, 1);
            } elseif (($hash = strpos($value, ' #')) !== false) {
                $value = rtrim(substr($value, 0, $hash));
            }

            if (!isset($_ENV[$name]) && getenv($name) === false) {
                $_ENV[$name] = $value;
            }
        }
    }
}
