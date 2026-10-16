-- 005: bitácoras inmutables de inventario, picking y carrito
-- Las tablas *_logs y movimientos_inventario son solo de inserción (sin updated_at/updated_by).

CREATE TABLE movimientos_inventario (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    producto_id INT UNSIGNED NOT NULL,
    tipo        ENUM('entrada','salida') NOT NULL,
    cantidad    DECIMAL(12,3) NOT NULL,
    precio_final DECIMAL(12,2) NULL,
    comanda_id  INT UNSIGNED NULL,
    sucursal_id INT UNSIGNED NULL,
    usuario_id  INT UNSIGNED NULL,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_movimientos_producto_fecha (producto_id, created_at),
    KEY idx_movimientos_comanda (comanda_id),
    KEY idx_movimientos_sucursal (sucursal_id),
    KEY idx_movimientos_usuario (usuario_id),
    KEY idx_movimientos_tipo_fecha (tipo, created_at),
    CONSTRAINT chk_movimientos_cantidad CHECK (cantidad > 0),
    CONSTRAINT fk_movimientos_producto FOREIGN KEY (producto_id) REFERENCES productos (id)   ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_movimientos_comanda  FOREIGN KEY (comanda_id)  REFERENCES comandas (id)    ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_movimientos_sucursal FOREIGN KEY (sucursal_id) REFERENCES sucursales (id)  ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_movimientos_usuario  FOREIGN KEY (usuario_id)  REFERENCES usuarios (id)    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE stock_logs (
    id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    producto_id   INT UNSIGNED NOT NULL,
    stock_antes   DECIMAL(12,3) NOT NULL,
    stock_despues DECIMAL(12,3) NOT NULL,
    usuario_id    INT UNSIGNED NULL,
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_stock_logs_producto_fecha (producto_id, created_at),
    KEY idx_stock_logs_usuario (usuario_id),
    CONSTRAINT fk_stock_logs_producto FOREIGN KEY (producto_id) REFERENCES productos (id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_stock_logs_usuario  FOREIGN KEY (usuario_id)  REFERENCES usuarios (id)  ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE precio_logs (
    id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    producto_id   INT UNSIGNED NOT NULL,
    precio_antes  DECIMAL(12,2) NOT NULL,
    precio_despues DECIMAL(12,2) NOT NULL,
    usuario_id    INT UNSIGNED NULL,
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_precio_logs_producto_fecha (producto_id, created_at),
    KEY idx_precio_logs_usuario (usuario_id),
    CONSTRAINT fk_precio_logs_producto FOREIGN KEY (producto_id) REFERENCES productos (id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_precio_logs_usuario  FOREIGN KEY (usuario_id)  REFERENCES usuarios (id)  ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE picking_logs (
    id                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    comanda_id          INT UNSIGNED NOT NULL,
    producto_id         INT UNSIGNED NOT NULL,
    cantidad_solicitada DECIMAL(12,3) NOT NULL,
    cantidad_pickeada   DECIMAL(12,3) NOT NULL,
    usuario_id          INT UNSIGNED NULL,
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_picking_logs_comanda (comanda_id),
    KEY idx_picking_logs_producto (producto_id),
    KEY idx_picking_logs_usuario (usuario_id),
    CONSTRAINT chk_picking_logs_cantidades CHECK (cantidad_solicitada >= 0 AND cantidad_pickeada >= 0),
    CONSTRAINT fk_picking_logs_comanda  FOREIGN KEY (comanda_id)  REFERENCES comandas (id)  ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_picking_logs_producto FOREIGN KEY (producto_id) REFERENCES productos (id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_picking_logs_usuario  FOREIGN KEY (usuario_id)  REFERENCES usuarios (id)  ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Carrito: ya no duplica nombre/tipo/imagen (se obtienen de productos)
CREATE TABLE carrito_items (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id  INT UNSIGNED NOT NULL,
    producto_id INT UNSIGNED NOT NULL,
    cantidad    DECIMAL(12,3) NOT NULL,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_carrito_usuario_producto (usuario_id, producto_id),
    KEY idx_carrito_producto (producto_id),
    CONSTRAINT chk_carrito_cantidad CHECK (cantidad > 0),
    CONSTRAINT fk_carrito_usuario  FOREIGN KEY (usuario_id)  REFERENCES usuarios (id)  ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_carrito_producto FOREIGN KEY (producto_id) REFERENCES productos (id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
