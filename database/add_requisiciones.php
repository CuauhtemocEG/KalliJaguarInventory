<?php

declare(strict_types=1);

// Requisiciones de compra del Supervisor y recepción en almacén; agrega Productos.CostoUltimo.
// Uso: php database/add_requisiciones.php  (idempotente)
require_once dirname(__DIR__) . '/config/bootstrap.php';

$pdo = App\Core\Database::connection();

$pdo->exec(
    "CREATE TABLE IF NOT EXISTS Requisiciones (
        RequisicionID INT AUTO_INCREMENT PRIMARY KEY,
        Folio VARCHAR(30) NOT NULL,
        SolicitanteID INT NOT NULL,
        SucursalID INT NULL,
        Estado ENUM('Enviada','Aprobada','Parcial','Recibida','Rechazada','Cancelada') NOT NULL DEFAULT 'Enviada',
        Notas VARCHAR(500) NULL,
        FechaRequerida DATE NULL,
        Proveedor VARCHAR(255) NULL,
        MotivoRechazo VARCHAR(255) NULL,
        FechaCreacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        AprobadoPor INT NULL,
        FechaAprobacion DATETIME NULL,
        FechaCierre DATETIME NULL,
        UNIQUE KEY uq_requisiciones_folio (Folio),
        INDEX idx_requisiciones_estado (Estado),
        INDEX idx_requisiciones_solicitante (SolicitanteID)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);

$pdo->exec(
    "CREATE TABLE IF NOT EXISTS RequisicionLineas (
        LineaID INT AUTO_INCREMENT PRIMARY KEY,
        RequisicionID INT NOT NULL,
        ProductoID INT NULL,
        Descripcion VARCHAR(150) NOT NULL,
        Tipo ENUM('Unidad','Pesable','Litro','Manojo') NOT NULL DEFAULT 'Unidad',
        CantidadSolicitada DECIMAL(10,3) NOT NULL,
        CantidadAprobada DECIMAL(10,3) NULL,
        CantidadRecibida DECIMAL(10,3) NOT NULL DEFAULT 0,
        CostoUnitario DECIMAL(30,2) NULL,
        INDEX idx_reqlineas_req (RequisicionID),
        INDEX idx_reqlineas_prod (ProductoID),
        CONSTRAINT fk_reqlineas_req FOREIGN KEY (RequisicionID) REFERENCES Requisiciones (RequisicionID) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);

$pdo->exec(
    'CREATE TABLE IF NOT EXISTS RequisicionRecepciones (
        RecepcionID INT AUTO_INCREMENT PRIMARY KEY,
        LineaID INT NOT NULL,
        ProductoID INT NOT NULL,
        Cantidad DECIMAL(10,3) NOT NULL,
        CostoUnitario DECIMAL(30,2) NULL,
        UsuarioID INT NULL,
        MovimientoID INT NULL,
        Fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_recepciones_linea (LineaID)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
);

$existe = $pdo->query("SHOW COLUMNS FROM Productos LIKE 'CostoUltimo'")->fetchColumn();
if (!$existe) {
    $pdo->exec('ALTER TABLE Productos ADD COLUMN CostoUltimo DECIMAL(30,2) NULL AFTER PrecioUnitario');
}

echo "Requisiciones listas.\n";
