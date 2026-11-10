<?php

declare(strict_types=1);

// Amplía Productos.Tipo con Litro y Manojo, da 3 decimales al carrito y crea la bitácora Logs_precio.
// Uso: php database/legacy_add_units.php  (idempotente)
require_once dirname(__DIR__) . '/config/bootstrap.php';

$pdo = App\Core\Database::connection();
$pdo->exec("ALTER TABLE Productos MODIFY Tipo ENUM('Unidad','Pesable','Litro','Manojo') NOT NULL");
$pdo->exec('ALTER TABLE CarritoSolicitudes MODIFY Cantidad DECIMAL(10,3) NOT NULL');
$pdo->exec(
    'CREATE TABLE IF NOT EXISTS Logs_precio (
        Id INT AUTO_INCREMENT PRIMARY KEY,
        UPC VARCHAR(70) NOT NULL,
        PrecioBefore DECIMAL(30,2) NOT NULL,
        PrecioAfter DECIMAL(30,2) NOT NULL,
        UsuarioID INT NULL,
        FechaCambio DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_logs_precio_upc (UPC),
        INDEX idx_logs_precio_fecha (FechaCambio)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
);
echo "Unidades actualizadas.\n";
