<?php
require_once "./controllers/mainController.php";

$campos = "Productos.ProductoID,Productos.UPC,Productos.Nombre as productName,Productos.Descripcion,Productos.PrecioUnitario,Productos.Cantidad,Productos.image,Productos.CategoriaID,Productos.UsuarioID,Productos.Tipo,Categorias.CategoriaID,Categorias.Nombre as CatName,Usuarios.UsuarioID,Usuarios.Nombre,Usuarios.Username";

$checkInventory = conexion();
$checkInventory = $checkInventory->query("SELECT $campos FROM Productos INNER JOIN Categorias ON Productos.CategoriaID=Categorias.CategoriaID INNER JOIN Usuarios ON Productos.UsuarioID=Usuarios.UsuarioID");
$datos = $checkInventory->fetchAll();

$total = conexion();
$total = $total->query("SELECT COUNT(*) FROM Productos WHERE Cantidad < 5");
$totalCount = (int) $total->fetchColumn();

$totalProd = conexion();
$totalProd = $totalProd->query("SELECT COUNT(*) FROM Productos");
$totalCountProd = (int) $totalProd->fetchColumn();

$sucursalId='2';

$dataSucursal = conexion();
$dataSucursal = $dataSucursal->query("SELECT nombre FROM Sucursales WHERE SucursalID = '$sucursalId'");
$nameSucursal = $dataSucursal->fetchColumn();


?>
<div class="container-fluid">
	<div class="d-flex justify-content-center row">
		<div class="col-md-12">
			<div class="row">
				<div class="col-xl-3 col-md-6 mb-4">
					<div class="card border-left-danger shadow h-100 py-2">
						<div class="card-body">
							<div class="row no-gutters align-items-center">
								<div class="col mr-2">
									<div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
										Productos con Inventario < 0 </div>
											<div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalCount; ?></div>
									</div>
									<div class="col-auto">
										<i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="col-xl-3 col-md-6 mb-4">
						<div class="card border-left-success shadow h-100 py-2">
							<div class="card-body">
								<div class="row no-gutters align-items-center">
									<div class="col mr-2">
										<div class="text-xs font-weight-bold text-success text-uppercase mb-1">
											Total de productos en Sistema</div>
										<div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalCountProd; ?></div>
									</div>
									<div class="col-auto">
										<i class="fas fa-archive fa-2x text-gray-300"></i>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>