-- Claves de mantenimiento por defecto (idempotente; mismas claves que el sistema anterior)
INSERT INTO configuracion_mantenimiento (clave, valor, tipo, descripcion) VALUES
    ('MAINTENANCE_MODE_ENABLED', 'false', 'boolean', 'Activa el modo mantenimiento'),
    ('BLOCKED_WEEKDAYS',         '[]',    'array',   'Días de la semana bloqueados (0=domingo)'),
    ('BLOCKED_DATES',            '[]',    'array',   'Fechas bloqueadas (YYYY-MM-DD)'),
    ('BLOCKED_TIME_START',       '00:00', 'string',  'Inicio de la ventana bloqueada'),
    ('BLOCKED_TIME_END',         '00:00', 'string',  'Fin de la ventana bloqueada')
ON DUPLICATE KEY UPDATE descripcion = VALUES(descripcion);
