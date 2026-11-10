<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Manejo de sesión (nombre y cookie seguros definidos desde .env).
 */
final class Session
{
    public static function start(): void
    {
        if (session_status() !== PHP_SESSION_NONE) {
            return;
        }

        $lifetime = max(3600, (int) config('session.lifetime', 2592000));

        // Directorio propio: en hosting compartido el de PHP es común y se purga a los ~24 min.
        $dir = BASE_PATH . '/storage/sessions';
        if (!is_dir($dir)) {
            @mkdir($dir, 0700, true);
        }
        if (is_dir($dir) && is_writable($dir)) {
            session_save_path($dir);
        }
        ini_set('session.gc_maxlifetime', (string) $lifetime);

        session_name((string) config('session.name', 'INV'));
        session_set_cookie_params([
            'lifetime' => $lifetime,
            'path' => '/',
            'domain' => '',
            'secure' => self::isHttps(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();

        // Sesión deslizante: cada visita renueva la cookie, así solo caduca por inactividad o al cerrar sesión.
        if (!headers_sent() && isset($_COOKIE[session_name()])) {
            setcookie(session_name(), session_id(), [
                'expires' => time() + $lifetime,
                'path' => '/',
                'secure' => self::isHttps(),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }
    }

    public static function isHttps(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    }

    public static function isAuthenticated(): bool
    {
        return !empty($_SESSION['id']) && !empty($_SESSION['usuario']);
    }

    public static function role(): ?string
    {
        return $_SESSION['rol'] ?? null;
    }
}
