-- 004: comandas (cabecera), detalle e historial de estados
-- Sustituye al ComandaID varchar repetido en MovimientosInventario/PickingLog/EstadosComanda.

CREATE TABLE comandas (
    id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    folio          VARCHAR(50) NOT NULL COMMENT 'Antiguo ComandaID (identificador visible)',
    sucursal_id    INT UNSIGNED NOT NULL,
    solicitante_id INT UNSIGNED NULL,
    estado_id      TINYINT UNSIGNED NOT NULL,
    fecha_entrega  DATETIME NULL,
    notas          TEXT NULL,
    created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by     INT UNSIGNED NULL,
    updated_by     INT UNSIGNED NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_comandas_folio (folio),
    KEY idx_comandas_sucursal (sucursal_id),
    KEY idx_comandas_solicitante (solicitante_id),
    KEY idx_comandas_estado_fecha (estado_id, created_at),
    CONSTRAINT fk_comandas_sucursal    FOREIGN KEY (sucursal_id)    REFERENCES sucursales (id)       ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_comandas_solicitante FOREIGN KEY (solicitante_id) REFERENCES usuarios (id)         ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_comandas_estado      FOREIGN KEY (estado_id)      REFERENCES estados_comanda (id)  ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_comandas_created_by  FOREIGN KEY (created_by)     REFERENCES usuarios (id)         ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_comandas_updated_by  FOREIGN KEY (updated_by)     REFERENCES usuarios (id)         ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE comanda_detalles (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    comanda_id      INT UNSIGNED NOT NULL,
    producto_id     INT UNSIGNED NOT NULL,
    cantidad        DECIMAL(12,3) NOT NULL,
    precio_unitario DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Precio sin impuestos al momento de la solicitud',
    precio_final    DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Total de la línea con impuestos',
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_comanda_detalles_comanda_producto (comanda_id, producto_id),
    KEY idx_comanda_detalles_producto (producto_id),
    CONSTRAINT chk_comanda_detalles_cantidad CHECK (cantidad > 0),
    CONSTRAINT fk_comanda_detalles_comanda  FOREIGN KEY (comanda_id)  REFERENCES comandas (id)  ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_comanda_detalles_producto FOREIGN KEY (producto_id) REFERENCES productos (id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE comanda_estado_historial (
    id                 BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    comanda_id         INT UNSIGNED NOT NULL,
    estado_anterior_id TINYINT UNSIGNED NULL,
    estado_nuevo_id    TINYINT UNSIGNED NOT NULL,
    usuario_id         INT UNSIGNED NULL,
    observaciones      VARCHAR(255) NULL,
    created_at         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_comanda_historial_comanda (comanda_id, created_at),
    KEY idx_comanda_historial_usuario (usuario_id),
    CONSTRAINT fk_comanda_historial_comanda  FOREIGN KEY (comanda_id)         REFERENCES comandas (id)        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_comanda_historial_anterior FOREIGN KEY (estado_anterior_id) REFERENCES estados_comanda (id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_comanda_historial_nuevo    FOREIGN KEY (estado_nuevo_id)    REFERENCES estados_comanda (id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_comanda_historial_usuario  FOREIGN KEY (usuario_id)         REFERENCES usuarios (id)        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
