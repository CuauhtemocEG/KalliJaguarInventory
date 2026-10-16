-- Roles del sistema (idempotente)
INSERT INTO roles (nombre, descripcion) VALUES
    ('Administrador', 'Acceso total al sistema'),
    ('Logistica',     'Gestión de comandas, picking y envíos'),
    ('Supervisor',    'Solicitud de insumos y consulta')
ON DUPLICATE KEY UPDATE descripcion = VALUES(descripcion);
