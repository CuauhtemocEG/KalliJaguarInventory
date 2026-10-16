-- Esquema ANTERIOR reconstruido a partir de las consultas del código (no existía dump en el repositorio).
-- Solo se usa para probar database/migrate_legacy.php. Datos de ejemplo ficticios.

CREATE TABLE Usuarios (
  UsuarioID INT AUTO_INCREMENT PRIMARY KEY,
  Nombre VARCHAR(100), Rol ENUM('Administrador','Logistica','Supervisor'),
  Username VARCHAR(50), email VARCHAR(150), Password VARCHAR(255),
  estado ENUM('activo','inactivo','suspendido') DEFAULT 'activo',
  fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP, fecha_ultima_modificacion DATETIME NULL,
  ultimo_acceso DATETIME NULL, fecha_ultimo_cambio_password DATETIME NULL,
  intentos_fallidos INT DEFAULT 0, ip_ultimo_acceso VARCHAR(45) NULL,
  token_reset_password VARCHAR(255) NULL, token_reset_expira DATETIME NULL,
  avatar VARCHAR(255) NULL, telefono VARCHAR(30) NULL, notas_admin TEXT NULL,
  creado_por INT NULL, modificado_por INT NULL
);
CREATE TABLE Categorias (CategoriaID INT AUTO_INCREMENT PRIMARY KEY, Nombre VARCHAR(100));
CREATE TABLE Sucursales (
  SucursalID INT AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(120), direccion VARCHAR(255),
  telefono VARCHAR(30), email VARCHAR(150), gerente VARCHAR(120), estado VARCHAR(20), tipo VARCHAR(40),
  horario_apertura TIME NULL, horario_cierre TIME NULL, notas TEXT NULL,
  fecha_creacion DATETIME NULL, fecha_actualizacion DATETIME NULL, creado_por INT NULL
);
CREATE TABLE Tags (TagID INT AUTO_INCREMENT PRIMARY KEY, Nombre VARCHAR(80), Color VARCHAR(20), Descripcion VARCHAR(255),
  Icono VARCHAR(60), CreadoPor INT NULL, FechaCreacion DATETIME NULL, FechaModificacion DATETIME NULL, Activo TINYINT(1) DEFAULT 1);
CREATE TABLE Productos (
  ProductoID INT AUTO_INCREMENT PRIMARY KEY, UPC VARCHAR(50), Nombre VARCHAR(200), Descripcion TEXT,
  Cantidad DECIMAL(30,3) DEFAULT 0, Tipo ENUM('Unidad','Pesable') DEFAULT 'Unidad', PrecioUnitario DECIMAL(30,2) DEFAULT 0,
  CategoriaID INT NULL, UsuarioID INT NULL, image VARCHAR(255) NULL, Tag VARCHAR(255) NULL,
  FechaCreacion DATETIME DEFAULT CURRENT_TIMESTAMP, FechaModificacion DATETIME NULL,
  StockMinimo DECIMAL(30,3) DEFAULT 0, Activo TINYINT(1) DEFAULT 1, SKU VARCHAR(50) NULL, Proveedor VARCHAR(150) NULL
);
CREATE TABLE ProductoTags (ProductoID INT, TagID INT, AsignadoPor INT NULL, FechaAsignacion DATETIME DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (ProductoID, TagID));
CREATE TABLE MovimientosInventario (
  MovimientoID INT AUTO_INCREMENT PRIMARY KEY, ComandaID VARCHAR(50) NULL, SucursalID INT NULL, ProductoID INT,
  TipoMovimiento ENUM('Entrada','Salida'), Cantidad DECIMAL(30,3), FechaMovimiento DATETIME DEFAULT CURRENT_TIMESTAMP,
  PrecioFinal DECIMAL(30,2) NULL, UsuarioID INT NULL, Status ENUM('Abierto','En transito','Cerrado','Cancelado') DEFAULT 'Abierto',
  FechaDelivery DATETIME NULL
);
CREATE TABLE CarritoSolicitudes (UsuarioID INT, ProductoID INT, Cantidad DECIMAL(30,3), PrecioUnitario DECIMAL(30,2),
  NombreProducto VARCHAR(200), Tipo VARCHAR(20), Imagen VARCHAR(255), PRIMARY KEY (UsuarioID, ProductoID));
CREATE TABLE Logs_stock (id INT AUTO_INCREMENT PRIMARY KEY, UPC VARCHAR(50), StockBefore DECIMAL(30,3), StockAfter DECIMAL(30,3), UsuarioID INT NULL, Fecha DATETIME DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE Logs_precio (id INT AUTO_INCREMENT PRIMARY KEY, UPC VARCHAR(50), PrecioBefore DECIMAL(30,2), PrecioAfter DECIMAL(30,2), UsuarioID INT NULL, FechaCambio DATETIME DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE PickingLog (id INT AUTO_INCREMENT PRIMARY KEY, ComandaID VARCHAR(50), ProductoID INT, UPC VARCHAR(50), CantidadSolicitada DECIMAL(30,3), CantidadPickeada DECIMAL(30,3), FechaPicking DATETIME, UsuarioID INT NULL);
CREATE TABLE EstadosComanda (id INT AUTO_INCREMENT PRIMARY KEY, ComandaID VARCHAR(50), EstadoAnterior VARCHAR(30), EstadoNuevo VARCHAR(30), FechaCambio DATETIME, UsuarioID INT NULL, Observaciones VARCHAR(255) NULL);
CREATE TABLE MaintenanceConfig (ConfigID INT AUTO_INCREMENT PRIMARY KEY, ConfigKey VARCHAR(100), ConfigValue TEXT, ConfigType VARCHAR(20), Descripcion VARCHAR(255), FechaModificacion DATETIME NULL, Activo TINYINT(1) DEFAULT 1);
CREATE TABLE MaintenanceConfigLog (LogID INT AUTO_INCREMENT PRIMARY KEY, ConfigKey VARCHAR(100), ValorAnterior TEXT, ValorNuevo TEXT, UsuarioID INT NULL, IPAddress VARCHAR(45), UserAgent VARCHAR(255), Fecha DATETIME DEFAULT CURRENT_TIMESTAMP);
