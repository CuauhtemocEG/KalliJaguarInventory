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
