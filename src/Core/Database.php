<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use RuntimeException;

/**
 * Fábrica de conexiones PDO (una por petición). Credenciales desde .env.
 */
final class Database
{
    private static ?PDO $connection = null;

    /** Conexión compartida durante la petición. */
    public static function connection(): PDO
    {
        return self::$connection ??= self::make();
    }

    /**
     * Nueva conexión independiente (necesaria para transacciones aisladas).
     * Con $baseDatos se conecta a otra base del mismo servidor (scripts de database/).
     */
    public static function make(?string $baseDatos = null): PDO
    {
        $cfg = Config::get('database');
        if ($baseDatos !== null) {
            $cfg['name'] = $baseDatos;
        }
        if (empty($cfg['name']) || empty($cfg['user'])) {
            throw new RuntimeException('Configuración de base de datos incompleta: revise DB_NAME y DB_USER en .env');
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $cfg['host'],
            $cfg['port'],
            $cfg['name'],
            $cfg['charset']
        );

        // Si falla, la PDOException se propaga (los scripts existentes ya la capturan)
        $opciones = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_BOTH,
            PDO::ATTR_EMULATE_PREPARES => true,
        ];
        // Compatibilidad: las consultas heredadas usan GROUP BY no estricto (MySQL 5.x / MariaDB)
        if (!empty($cfg['legacy_group_by'])) {
            $opciones[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET SESSION sql_mode = REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', '')";
        }
        $pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], $opciones);

        return $pdo;
    }
}
