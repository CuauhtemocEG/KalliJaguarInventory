<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Consultas sobre el usuario autenticado (datos guardados en $_SESSION).
 */
final class Auth
{
    public const ROLES = ['Administrador', 'Logistica', 'Supervisor'];

    public static function check(): bool
    {
        return Session::isAuthenticated();
    }

    public static function role(): ?string
    {
        return Session::role();
    }

    /** @param string[] $roles */
    public static function hasRole(array $roles): bool
    {
        return in_array(Session::role(), $roles, true);
    }
}
