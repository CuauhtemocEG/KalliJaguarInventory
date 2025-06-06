<?php
require_once "../../config/database.php";

$campos = "Productos.ProductoID,Productos.UPC,Productos.Nombre as productName,Productos.Descripcion,Productos.PrecioUnitario,Productos.Cantidad,Productos.image,Productos.CategoriaID,Productos.UsuarioID,Productos.Tipo,Categorias.CategoriaID,Categorias.Nombre as CatName,Usuarios.UsuarioID,Usuarios.Nombre,Usuarios.Username";

$checkInventory = conexion();
$checkInventory = $checkInventory->query("SELECT $campos FROM Productos INNER JOIN Categorias ON Productos.CategoriaID=Categorias.CategoriaID INNER JOIN Usuarios ON Productos.UsuarioID=Usuarios.UsuarioID");
$datos = $checkInventory->fetchAll();

$total = conexion();
$total = $total->query("SELECT COUNT(*) FROM Productos WHERE Cantidad < 5");
$totalCount = (int) $total->fetchColumn();

$productsDown = conexion();
$productsDown = $productsDown->query("SELECT * FROM Productos WHERE Cantidad < 5");
$products = $productsDown->fetchAll();

$totalProd = conexion();
$totalProd = $totalProd->query("SELECT COUNT(*) FROM Productos");
$totalCountProd = (int) $totalProd->fetchColumn();

$totalPesables = conexion();
$totalPesables = $totalPesables->query("SELECT Nombre, Cantidad, Tipo FROM Productos WHERE Tipo='Pesable' AND Cantidad < 5");

$totalUnidades = conexion();
$totalUnidades = $totalUnidades->query("SELECT Nombre, Cantidad, Tipo FROM Productos WHERE Tipo='Unidad' AND Cantidad < 5");
?>
<div class="container-fluid">
	<div class="d-flex justify-content-center row">
		<div class="col-md-12">
			<div class="row">

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

				<div class="card border-left-danger shadow h-100 py-2">
					<div class="card-body">
						<div class="row no-gutters align-items-center">
							<div class="col mr-2">
								<div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
									Productos con Inventario menor a 5 de stock </div>
								<div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalCount; ?></div>
							</div>
							<div class="col-auto">
								<i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
							</div>
						</div>

						<div id="accordion">
							<div class="card">
								<div class="card-header" id="headingOne">
									<h5 class="mb-0">
										<button class="btn" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
											Ver productos - Pesables
										</button>
									</h5>
								</div>

								<div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordion">
									<div class="card-body">
										<?php if ($totalPesables->rowCount() > 0) {
											$datosPesables = $totalPesables->fetchAll();
											foreach ($datosPesables as $row) {

												if ($row['Tipo'] == "Pesable") {
													$res = "Kg";
													$unidades = number_format($row['Cantidad'], 2, '.', '');
												}

												echo '<p>' . $row['Nombre'] . ' - ' . $unidades . ' ' . $res . '</p>';
											}
										} ?>
									</div>
								</div>

								<div class="card-header" id="headingTwo">
									<h5 class="mb-0">
										<button class="btn" type="button" data-toggle="collapse" data-target="#collapseTwoHome" aria-expanded="true" aria-controls="collapseOne">
											Ver productos - Unidad
										</button>
									</h5>
								</div>

								<div id="collapseTwoHome" class="collapse" aria-labelledby="headingTwo" data-parent="#accordion">
									<div class="card-body">
										<?php if ($totalUnidades->rowCount() > 0) {
											$datosUnidades = $totalUnidades->fetchAll();
											foreach ($datosUnidades as $row) {

												if ($row['Tipo'] == "Unidad") {
													$res = "Unidades";
													$unidades = number_format($row['Cantidad'], 0, '.', '');
												}

												echo '<p>' . $row['Nombre'] . ' - ' . $unidades . ' ' . $res . '</p>';
											}
										} ?>
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
</div>
</div>