<?php

declare(strict_types=1);

// Bitácora de accesos bloqueados por el modo mantenimiento (la usa maintenanceHelper::logBlockedAccess).
// Uso: php database/add_maintenance_log.php  (idempotente)
require_once dirname(__DIR__) . '/config/bootstrap.php';

$pdo = App\Core\Database::connection();

$pdo->exec(
    "CREATE TABLE IF NOT EXISTS MaintenanceAccessLog (
        LogID INT AUTO_INCREMENT PRIMARY KEY,
        UsuarioID INT NULL,
        Pagina VARCHAR(100) NOT NULL,
        Razon VARCHAR(30) NOT NULL,
        DetallesRazon VARCHAR(255) NULL,
        IPAddress VARCHAR(45) NULL,
        UserAgent VARCHAR(500) NULL,
        Fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_maint_fecha (Fecha),
        INDEX idx_maint_usuario (UsuarioID)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);

echo "Bitácora de mantenimiento lista.\n";
