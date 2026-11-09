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

        session_name((string) config('session.name', 'INV'));
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => self::isHttps(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
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
