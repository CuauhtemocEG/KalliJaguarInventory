INSERT INTO Usuarios (Nombre, Rol, Username, email, Password, estado, token_reset_password, token_reset_expira, creado_por) VALUES
 ('Admin Demo','Administrador','admin','admin@example.com','$2y$10$abcdefghijklmnopqrstuuabcdefghijklmnopqrstuvwxyz012345','activo',NULL,NULL,NULL),
 ('Logistica Demo','Logistica','logi','logi@example.com','clave-en-texto-plano','activo','tok123','2030-01-01 00:00:00',1),
 ('Super Demo','Supervisor','super','super@example.com','$2y$10$abcdefghijklmnopqrstuuabcdefghijklmnopqrstuvwxyz012345','suspendido',NULL,NULL,1);
INSERT INTO Categorias (Nombre) VALUES ('Abarrotes'),('Limpieza'),('Abarrotes');
INSERT INTO Sucursales (nombre, direccion, estado) VALUES ('Centro','Calle 1','activa'),('Norte','Calle 2','activa');
INSERT INTO Tags (Nombre, Color, CreadoPor) VALUES ('Frio','#00f',1),('Seco','#f00',1);
INSERT INTO Productos (UPC, Nombre, Cantidad, Tipo, PrecioUnitario, CategoriaID, UsuarioID, Tag, SKU, Proveedor) VALUES
 ('111','Arroz',50.5,'Pesable',20.00,1,1,'Seco, Nuevo','SKU-1','Proveedor Uno'),
 ('222','Cloro',10,'Unidad',35.50,2,1,'Frio','','proveedor uno '),
 ('333','Jabon',5,'Unidad',12.00,3,1,NULL,NULL,'Otro');
INSERT INTO ProductoTags (ProductoID, TagID, AsignadoPor) VALUES (1,2,1),(2,1,1);
INSERT INTO MovimientosInventario (ComandaID, SucursalID, ProductoID, TipoMovimiento, Cantidad, PrecioFinal, UsuarioID, Status, FechaDelivery) VALUES
 (NULL,NULL,1,'Entrada',100,NULL,1,'Cerrado',NULL),
 ('CMD-1',1,1,'Salida',2,46.4,2,'Abierto',NULL),
 ('CMD-1',1,2,'Salida',1,41.18,2,'Abierto',NULL),
 ('CMD-2',2,1,'Salida',3,69.6,2,'En transito','2030-01-02 00:00:00'),
 ('CMD-2',2,1,'Salida',1,23.2,2,'En transito',NULL);
INSERT INTO CarritoSolicitudes VALUES (2,1,4,20,'Arroz','Pesable','x.jpg');
INSERT INTO Logs_stock (UPC, StockBefore, StockAfter, UsuarioID) VALUES ('111',40,50.5,1),('999',1,2,1);
INSERT INTO Logs_precio (UPC, PrecioBefore, PrecioAfter, UsuarioID) VALUES ('111',18,20,1);
INSERT INTO PickingLog (ComandaID, ProductoID, UPC, CantidadSolicitada, CantidadPickeada, FechaPicking, UsuarioID) VALUES ('CMD-2',1,'111',4,4,NOW(),2);
INSERT INTO EstadosComanda (ComandaID, EstadoAnterior, EstadoNuevo, FechaCambio, UsuarioID, Observaciones) VALUES ('CMD-2','Abierto','En Tránsito',NOW(),2,'ok');
INSERT INTO MaintenanceConfig (ConfigKey, ConfigValue, ConfigType, Descripcion) VALUES ('MAINTENANCE_MODE_ENABLED','false','boolean','x');
INSERT INTO MaintenanceConfigLog (ConfigKey, ValorAnterior, ValorNuevo, UsuarioID, IPAddress) VALUES ('MAINTENANCE_MODE_ENABLED','true','false',1,'127.0.0.1');
