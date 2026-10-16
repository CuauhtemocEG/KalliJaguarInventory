<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Protección CSRF con token por sesión.
 * El token se envía en el campo "_csrf" o en la cabecera "X-CSRF-Token".
 */
final class Csrf
{
    public static function token(): string
    {
        Session::start();
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'];
    }

    public static function validate(?string $token): bool
    {
        Session::start();
        return is_string($token)
            && $token !== ''
            && !empty($_SESSION['_csrf'])
            && hash_equals($_SESSION['_csrf'], $token);
    }

    /** Token recibido en la petición actual (formulario o cabecera). */
    public static function fromRequest(): ?string
    {
        return $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
    }
}
