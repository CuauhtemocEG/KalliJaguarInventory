-- KalliJaguarInventory: esquema completo (resultado de aplicar database/migrations/*.sql en orden).
-- Para instalaciones nuevas: mysql -u USUARIO -p BASE < database/schema.sql && php database/seed.php
-- (migrate.php y schema.sql son alternativas: use una u otra para una misma base.)

-- ===== 001_catalogos.sql =====
-- 001: catálogos base (roles, categorías, proveedores, tags, estados de comanda)
-- Convención: tablas en plural y snake_case (español), PK `id`, InnoDB, utf8mb4.

CREATE TABLE roles (
    id          TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre      VARCHAR(30)  NOT NULL,
    descripcion VARCHAR(150) NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_roles_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE estados_comanda (
    id     TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    codigo VARCHAR(20) NOT NULL,
    nombre VARCHAR(40) NOT NULL,
    orden  TINYINT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY uq_estados_comanda_codigo (codigo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE categorias (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre     VARCHAR(100) NOT NULL,
    descripcion VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT UNSIGNED NULL,
    updated_by INT UNSIGNED NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_categorias_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE proveedores (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre     VARCHAR(150) NOT NULL,
    contacto   VARCHAR(150) NULL,
    telefono   VARCHAR(30)  NULL,
    email      VARCHAR(150) NULL,
    activo     TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT UNSIGNED NULL,
    updated_by INT UNSIGNED NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_proveedores_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tags (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre      VARCHAR(80) NOT NULL,
    color       VARCHAR(20) NULL,
    descripcion VARCHAR(255) NULL,
    icono       VARCHAR(60) NULL,
    activo      TINYINT(1) NOT NULL DEFAULT 1,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by  INT UNSIGNED NULL,
    updated_by  INT UNSIGNED NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_tags_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== 002_usuarios.sql =====
-- 002: usuarios, restablecimiento de contraseña y accesos

CREATE TABLE usuarios (
    id                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre              VARCHAR(100) NOT NULL,
    username            VARCHAR(50)  NOT NULL,
    email               VARCHAR(150) NOT NULL,
    password_hash       VARCHAR(255) NOT NULL COMMENT 'Resultado de password_hash(); nunca texto plano',
    rol_id              TINYINT UNSIGNED NOT NULL,
    estado              ENUM('activo','inactivo','suspendido') NOT NULL DEFAULT 'activo',
    telefono            VARCHAR(30)  NULL,
    avatar              VARCHAR(255) NULL,
    notas_admin         TEXT NULL,
    intentos_fallidos   TINYINT UNSIGNED NOT NULL DEFAULT 0,
    ultimo_acceso_at    DATETIME NULL,
    ultimo_acceso_ip    VARCHAR(45) NULL,
    password_cambiada_at DATETIME NULL,
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by          INT UNSIGNED NULL,
    updated_by          INT UNSIGNED NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_usuarios_username (username),
    UNIQUE KEY uq_usuarios_email (email),
    KEY idx_usuarios_rol (rol_id),
    KEY idx_usuarios_estado (estado),
    CONSTRAINT fk_usuarios_rol        FOREIGN KEY (rol_id)     REFERENCES roles (id)    ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_usuarios_created_by FOREIGN KEY (created_by) REFERENCES usuarios (id) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_usuarios_updated_by FOREIGN KEY (updated_by) REFERENCES usuarios (id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE password_resets (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id INT UNSIGNED NOT NULL,
    token_hash CHAR(64) NOT NULL COMMENT 'SHA-256 del token; el token en claro solo viaja por correo',
    expires_at DATETIME NOT NULL,
    used_at    DATETIME NULL,
    ip         VARCHAR(45) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_password_resets_token (token_hash),
    KEY idx_password_resets_usuario (usuario_id),
    KEY idx_password_resets_expires (expires_at),
    CONSTRAINT fk_password_resets_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE accesos_logs (
    id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id INT UNSIGNED NULL,
    evento     ENUM('login','login_fallido','logout') NOT NULL DEFAULT 'login',
    ip         VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_accesos_logs_usuario_fecha (usuario_id, created_at),
    CONSTRAINT fk_accesos_logs_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Las columnas de auditoría de los catálogos se enlazan a usuarios una vez creada la tabla
ALTER TABLE categorias  ADD CONSTRAINT fk_categorias_created_by  FOREIGN KEY (created_by) REFERENCES usuarios (id) ON UPDATE CASCADE ON DELETE SET NULL,
                        ADD CONSTRAINT fk_categorias_updated_by  FOREIGN KEY (updated_by) REFERENCES usuarios (id) ON UPDATE CASCADE ON DELETE SET NULL;
ALTER TABLE proveedores ADD CONSTRAINT fk_proveedores_created_by FOREIGN KEY (created_by) REFERENCES usuarios (id) ON UPDATE CASCADE ON DELETE SET NULL,
                        ADD CONSTRAINT fk_proveedores_updated_by FOREIGN KEY (updated_by) REFERENCES usuarios (id) ON UPDATE CASCADE ON DELETE SET NULL;
ALTER TABLE tags        ADD CONSTRAINT fk_tags_created_by        FOREIGN KEY (created_by) REFERENCES usuarios (id) ON UPDATE CASCADE ON DELETE SET NULL,
                        ADD CONSTRAINT fk_tags_updated_by        FOREIGN KEY (updated_by) REFERENCES usuarios (id) ON UPDATE CASCADE ON DELETE SET NULL;

-- ===== 003_sucursales_productos.sql =====
-- 003: sucursales, productos y etiquetas de producto

CREATE TABLE sucursales (
    id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre           VARCHAR(120) NOT NULL,
    direccion        VARCHAR(255) NULL,
    telefono         VARCHAR(30)  NULL,
    email            VARCHAR(150) NULL,
    gerente          VARCHAR(120) NULL,
    estado           VARCHAR(20)  NOT NULL DEFAULT 'activa',
    tipo             VARCHAR(40)  NULL,
    horario_apertura TIME NULL,
    horario_cierre   TIME NULL,
    notas            TEXT NULL,
    created_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by       INT UNSIGNED NULL,
    updated_by       INT UNSIGNED NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_sucursales_nombre (nombre),
    KEY idx_sucursales_estado (estado),
    CONSTRAINT fk_sucursales_created_by FOREIGN KEY (created_by) REFERENCES usuarios (id) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_sucursales_updated_by FOREIGN KEY (updated_by) REFERENCES usuarios (id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE productos (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    upc             VARCHAR(50)  NOT NULL,
    sku             VARCHAR(50)  NULL,
    nombre          VARCHAR(200) NOT NULL,
    descripcion     TEXT NULL,
    tipo            ENUM('unidad','pesable') NOT NULL DEFAULT 'unidad',
    precio_unitario DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    cantidad        DECIMAL(12,3) NOT NULL DEFAULT 0.000,
    stock_minimo    DECIMAL(12,3) NOT NULL DEFAULT 0.000,
    categoria_id    INT UNSIGNED NULL,
    proveedor_id    INT UNSIGNED NULL,
    imagen          VARCHAR(255) NULL COMMENT 'Solo nombre de archivo dentro de UPLOADS_PRODUCTS_DIR',
    activo          TINYINT(1) NOT NULL DEFAULT 1,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by      INT UNSIGNED NULL,
    updated_by      INT UNSIGNED NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_productos_upc (upc),
    UNIQUE KEY uq_productos_sku (sku),
    KEY idx_productos_nombre (nombre),
    KEY idx_productos_categoria (categoria_id),
    KEY idx_productos_proveedor (proveedor_id),
    KEY idx_productos_activo (activo),
    CONSTRAINT chk_productos_cantidad CHECK (cantidad >= 0),
    CONSTRAINT chk_productos_stock_minimo CHECK (stock_minimo >= 0),
    CONSTRAINT chk_productos_precio CHECK (precio_unitario >= 0),
    CONSTRAINT fk_productos_categoria  FOREIGN KEY (categoria_id) REFERENCES categorias (id)  ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_productos_proveedor  FOREIGN KEY (proveedor_id) REFERENCES proveedores (id) ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_productos_created_by FOREIGN KEY (created_by)   REFERENCES usuarios (id)    ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_productos_updated_by FOREIGN KEY (updated_by)   REFERENCES usuarios (id)    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE producto_tags (
    producto_id INT UNSIGNED NOT NULL,
    tag_id      INT UNSIGNED NOT NULL,
    asignado_por INT UNSIGNED NULL,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (producto_id, tag_id),
    KEY idx_producto_tags_tag (tag_id),
    CONSTRAINT fk_producto_tags_producto FOREIGN KEY (producto_id)  REFERENCES productos (id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_producto_tags_tag      FOREIGN KEY (tag_id)       REFERENCES tags (id)      ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_producto_tags_usuario  FOREIGN KEY (asignado_por) REFERENCES usuarios (id)  ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== 004_comandas.sql =====
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

-- ===== 005_inventario.sql =====
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

-- ===== 006_sistema.sql =====
-- 006: mantenimiento, auditoría administrativa y notificaciones

CREATE TABLE configuracion_mantenimiento (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    clave       VARCHAR(100) NOT NULL,
    valor       TEXT NULL,
    tipo        VARCHAR(20) NOT NULL DEFAULT 'string',
    descripcion VARCHAR(255) NULL,
    activo      TINYINT(1) NOT NULL DEFAULT 1,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by  INT UNSIGNED NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_config_mantenimiento_clave (clave),
    CONSTRAINT fk_config_mantenimiento_updated_by FOREIGN KEY (updated_by) REFERENCES usuarios (id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE configuracion_mantenimiento_logs (
    id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    clave          VARCHAR(100) NOT NULL,
    valor_anterior TEXT NULL,
    valor_nuevo    TEXT NULL,
    usuario_id     INT UNSIGNED NULL,
    ip             VARCHAR(45) NULL,
    user_agent     VARCHAR(255) NULL,
    created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_config_mantenimiento_logs_clave (clave, created_at),
    KEY idx_config_mantenimiento_logs_usuario (usuario_id),
    CONSTRAINT fk_config_mantenimiento_logs_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE mantenimiento_accesos_logs (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id      INT UNSIGNED NULL,
    pagina          VARCHAR(150) NULL,
    razon           VARCHAR(100) NULL,
    detalles_razon  VARCHAR(255) NULL,
    ip              VARCHAR(45) NULL,
    user_agent      VARCHAR(255) NULL,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_mantenimiento_accesos_usuario (usuario_id, created_at),
    CONSTRAINT fk_mantenimiento_accesos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE auditoria_logs (
    id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id INT UNSIGNED NULL COMMENT 'Quien ejecutó la acción',
    accion     VARCHAR(100) NOT NULL,
    detalles   TEXT NULL,
    ip         VARCHAR(45) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_auditoria_usuario_fecha (usuario_id, created_at),
    KEY idx_auditoria_accion (accion),
    CONSTRAINT fk_auditoria_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE notificaciones (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id  INT UNSIGNED NOT NULL,
    titulo      VARCHAR(255) NOT NULL,
    mensaje     TEXT NOT NULL,
    tipo        ENUM('info','warning','error','admin_message') NOT NULL DEFAULT 'info',
    enviado_por INT UNSIGNED NULL,
    leida_at    DATETIME NULL,
    expira_at   DATE NULL,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_notificaciones_usuario (usuario_id, leida_at),
    CONSTRAINT fk_notificaciones_usuario     FOREIGN KEY (usuario_id)  REFERENCES usuarios (id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_notificaciones_enviado_por FOREIGN KEY (enviado_por) REFERENCES usuarios (id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ===== registro de migraciones (para que migrate.php no las reaplique) =====
CREATE TABLE IF NOT EXISTS schema_migrations (version VARCHAR(100) NOT NULL PRIMARY KEY, applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT IGNORE INTO schema_migrations (version) VALUES ('001_catalogos'),('002_usuarios'),('003_sucursales_productos'),('004_comandas'),('005_inventario'),('006_sistema');
