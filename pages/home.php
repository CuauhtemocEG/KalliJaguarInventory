<?php
require_once "./controllers/mainController.php";

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

$db = conexion();
$statusCountsStmt = $db->query("
    SELECT Status, COUNT(DISTINCT ComandaID) AS total
    FROM MovimientosInventario
    WHERE TipoMovimiento = 'Salida'
    GROUP BY Status
");

$statusCounts = $statusCountsStmt->fetchAll(PDO::FETCH_KEY_PAIR);

$totalSolicitudes = array_sum($statusCounts);
function porcentaje($valor, $total)
{
	return $total > 0 ? number_format(($valor / $total) * 100, 1) : '0.0';
}
?>
<div class="container-fluid py-4">
	<?php if ($totalCount > 0): ?>
		<div class="alert alert-danger d-flex align-items-center" role="alert">
			<p><i class="fas fa-exclamation-circle me-2"></i> Hay <strong><?php echo $totalCount; ?></strong> productos con inventario bajo (< 5 unidades).</p>
		</div>
	<?php endif; ?>

	<div class="row mb-4">
		<div class="col-md-6 mb-4 mb-md-0">
			<div class="card shadow h-100">
				<div class="card-body d-flex flex-column justify-content-center align-items-center">
					<h5 class="card-title text-center">Resumen de Solicitudes por Estado</h5>
					<canvas id="solicitudesChart"></canvas>
				</div>
			</div>
		</div>

		<div class="col-md-6">
			<div class="card shadow h-100">
				<div class="card-body">
					<h5 class="card-title text-center">Totales por Estado</h5>
					<ul class="list-group list-group-flush">
						<li class="list-group-item d-flex justify-content-between align-items-center">
							<span><i class="fas fa-info-circle text-info mr-2"></i> Abierto</span>
							<span class="badge badge-info badge-pill">
								<?= $statusCounts['Abierto'] ?? 0 ?> (<?= porcentaje($statusCounts['Abierto'] ?? 0, $totalSolicitudes) ?>%)
							</span>
						</li>
						<li class="list-group-item d-flex justify-content-between align-items-center">
							<span><i class="fas fa-shipping-fast text-warning mr-2"></i> En transito</span>
							<span class="badge badge-warning badge-pill">
								<?= $statusCounts['En transito'] ?? 0 ?> (<?= porcentaje($statusCounts['En transito'] ?? 0, $totalSolicitudes) ?>%)
							</span>
						</li>
						<li class="list-group-item d-flex justify-content-between align-items-center">
							<span><i class="fas fa-check-circle text-success mr-2"></i> Cerrado</span>
							<span class="badge badge-success badge-pill">
								<?= $statusCounts['Cerrado'] ?? 0 ?> (<?= porcentaje($statusCounts['Cerrado'] ?? 0, $totalSolicitudes) ?>%)
							</span>
						</li>
						<li class="list-group-item d-flex justify-content-between align-items-center">
							<span><i class="fas fa-times-circle text-danger mr-2"></i> Cancelado</span>
							<span class="badge badge-danger badge-pill">
								<?= $statusCounts['Cancelado'] ?? 0 ?> (<?= porcentaje($statusCounts['Cancelado'] ?? 0, $totalSolicitudes) ?>%)
							</span>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>

	<div class="row">

		<div class="col-12 mb-4">
			<div class="card shadow border-left-success">
				<div class="card-body d-flex align-items-center justify-content-between">
					<div>
						<h6 class="text-success text-uppercase mb-1">Total de productos en Sistema</h6>
						<h4 class="text-dark font-weight-bold mb-0"><?php echo $totalCountProd; ?></h4>
					</div>
					<i class="fas fa-boxes fa-2x text-success"></i>
				</div>
			</div>
		</div>

		<div class="col-12 mb-4">
			<div class="card shadow border-left-danger">
				<div class="card-body">
					<div class="d-flex align-items-center justify-content-between mb-3">
						<div>
							<h6 class="text-danger text-uppercase mb-1">Productos con inventario bajo</h6>
							<h5 class="text-dark font-weight-bold mb-0">
								<?php echo $totalCount; ?>
								<span class="badge badge-danger ml-2">Bajo stock</span>
							</h5>
						</div>
						<i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
					</div>

					<div class="accordion" id="stockAccordion">

						<div class="card mb-2">
							<div class="card-header p-2" id="headingPesables">
								<h2 class="mb-0">
									<button class="btn btn-link text-dark" type="button" data-toggle="collapse" data-target="#collapsePesables" aria-expanded="true" aria-controls="collapsePesables">
										<i class="fas fa-balance-scale-left text-warning mr-2"></i> Ver productos - Pesables
									</button>
								</h2>
							</div>
							<div id="collapsePesables" class="collapse" aria-labelledby="headingPesables" data-parent="#stockAccordion">
								<div class="card-body">
									<ul class="list-group list-group-flush">
										<?php
										if ($totalPesables->rowCount() > 0) {
											foreach ($totalPesables as $row) {
												echo '<li class="list-group-item d-flex justify-content-between align-items-center">
                                                        ' . $row['Nombre'] . '
                                                        <span class="badge badge-warning badge-pill">' . number_format($row['Cantidad'], 3) . ' Kg/grms</span>
                                                      </li>';
											}
										} else {
											echo '<li class="list-group-item text-muted">Sin productos pesables bajo stock.</li>';
										}
										?>
									</ul>
								</div>
							</div>
						</div>

						<div class="card">
							<div class="card-header p-2" id="headingUnidades">
								<h2 class="mb-0">
									<button class="btn btn-link text-dark" type="button" data-toggle="collapse" data-target="#collapseUnidades" aria-expanded="false" aria-controls="collapseUnidades">
										<i class="fas fa-cube text-primary mr-2"></i> Ver productos - Unidad
									</button>
								</h2>
							</div>
							<div id="collapseUnidades" class="collapse" aria-labelledby="headingUnidades" data-parent="#stockAccordion">
								<div class="card-body">
									<ul class="list-group list-group-flush">
										<?php
										if ($totalUnidades->rowCount() > 0) {
											foreach ($totalUnidades as $row) {
												echo '<li class="list-group-item d-flex justify-content-between align-items-center">
                                                        ' . $row['Nombre'] . '
                                                        <span class="badge badge-primary badge-pill">' . number_format($row['Cantidad'], 0) . ' uds</span>
                                                      </li>';
											}
										} else {
											echo '<li class="list-group-item text-muted">Sin productos por unidad bajo stock.</li>';
										}
										?>
									</ul>
								</div>
							</div>
						</div>

					</div>

				</div>
			</div>
		</div>
	</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
	document.addEventListener('DOMContentLoaded', function() {
		const ctx = document.getElementById('solicitudesChart').getContext('2d');
		const data = {
			labels: ['Abierto', 'En transito', 'Cerrado', 'Cancelado'],
			datasets: [{
				data: [
					<?= $statusCounts['Abierto'] ?? 0 ?>,
					<?= $statusCounts['En transito'] ?? 0 ?>,
					<?= $statusCounts['Cerrado'] ?? 0 ?>,
					<?= $statusCounts['Cancelado'] ?? 0 ?>
				],
				backgroundColor: ['#17a2b8', '#ffc107', '#28a745', '#dc3545']
			}]
		};

		const config = {
			type: 'pie',
			data: data,
			options: {
				responsive: true,
				plugins: {
					legend: {
						position: 'bottom'
					},
					tooltip: {
						callbacks: {
							label: function(context) {
								let total = context.dataset.data.reduce((a, b) => a + b, 0);
								let value = context.parsed;
								let percentage = ((value / total) * 100).toFixed(1);
								return `${context.label}: ${value} (${percentage}%)`;
							}
						}
					}
				}
			}
		};

		new Chart(ctx, config);
	});
</script>