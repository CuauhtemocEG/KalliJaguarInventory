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
