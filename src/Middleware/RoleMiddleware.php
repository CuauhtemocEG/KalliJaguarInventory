<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;

/**
 * Restringe el acceso por rol (Administrador, Logistica, Supervisor).
 */
final class RoleMiddleware
{
    /** @param string[] $roles */
    public static function handle(array $roles): ?array
    {
        if ($roles === [] || Auth::hasRole($roles)) {
            return null;
        }
        return ['status' => 403, 'message' => 'No tiene permisos para realizar esta acción.'];
    }
}
