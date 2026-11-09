-- Catálogo de estados de comanda (idempotente)
INSERT INTO estados_comanda (codigo, nombre, orden) VALUES
    ('abierto',     'Abierto',      1),
    ('en_transito', 'En tránsito',  2),
    ('cerrado',     'Cerrado',      3),
    ('cancelado',   'Cancelado',    4)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), orden = VALUES(orden);
