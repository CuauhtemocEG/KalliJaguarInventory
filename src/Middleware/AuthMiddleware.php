<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;

/**
 * Exige una sesión iniciada.
 */
final class AuthMiddleware
{
    public static function handle(): ?array
    {
        if (Auth::check()) {
            return null;
        }
        return ['status' => 401, 'message' => 'Sesión no iniciada o expirada.'];
    }
}
