<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Csrf;

/**
 * Exige un token CSRF válido en peticiones que modifican datos.
 */
final class CsrfMiddleware
{
    private const SAFE_METHODS = ['GET', 'HEAD', 'OPTIONS'];

    public static function handle(string $method): ?array
    {
        if (in_array($method, self::SAFE_METHODS, true)) {
            return null;
        }
        if (Csrf::validate(Csrf::fromRequest())) {
            return null;
        }
        return ['status' => 419, 'message' => 'Token CSRF inválido o expirado. Recargue la página.'];
    }
}
