<?php
require_once "./controllers/mainController.php";

$showAdvancedDashboard = isset($_GET['advanced']) && $_GET['advanced'] == '1';

if ($showAdvancedDashboard) {
    include './pages/dashboardReportes.php';
    return;
}

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

<div class="min-h-screen bg-gray-50 py-4">
	<div class="w-full mx-auto px-4 sm:px-6 lg:px-8">
		
		<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 space-y-4 md:space-y-0">
			<div>
				<h1 class="text-3xl font-bold text-gray-900">Dashboard Principal</h1>
				<p class="text-gray-600 mt-1">Resumen general del inventario y solicitudes</p>
			</div>
			<?php if ($_SESSION['id']=== 1): ?>
			<a href="?page=dashboardAvanzado" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
				<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
				</svg>
				Dashboard Avanzado
			</a>
			<?php endif; ?>
		</div>

		<?php if ($totalCount > 0): ?>
		<div class="bg-red-50 border-l-4 border-red-400 p-4 mb-8 rounded-r-lg">
			<div class="flex items-center">
				<div class="flex-shrink-0">
					<svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
						<path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
					</svg>
				</div>
				<div class="ml-3">
					<p class="text-sm text-red-700">
						<span class="font-medium">Atención:</span> Hay <strong><?php echo $totalCount; ?></strong> productos con inventario bajo (< 5 unidades).
					</p>
				</div>
			</div>
		</div>
		<?php endif; ?>

		<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
			<div class="bg-white overflow-hidden shadow-lg rounded-lg border-l-4 border-green-500">
				<div class="p-6">
					<div class="flex items-center">
						<div class="flex-shrink-0">
							<div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
								<svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
								</svg>
							</div>
						</div>
						<div class="ml-5 w-0 flex-1">
							<dl>
								<dt class="text-sm font-medium text-gray-500 truncate">Total de productos</dt>
								<dd class="text-2xl font-bold text-gray-900"><?php echo $totalCountProd; ?></dd>
							</dl>
						</div>
					</div>
				</div>
			</div>

			<div class="bg-white overflow-hidden shadow-lg rounded-lg border-l-4 border-blue-500">
				<div class="p-6">
					<div class="flex items-center">
						<div class="flex-shrink-0">
							<div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
								<svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
								</svg>
							</div>
						</div>
						<div class="ml-5 w-0 flex-1">
							<dl>
								<dt class="text-sm font-medium text-gray-500 truncate">Abiertas</dt>
								<dd class="text-2xl font-bold text-gray-900"><?= $statusCounts['Abierto'] ?? 0 ?></dd>
							</dl>
						</div>
					</div>
				</div>
			</div>

			<div class="bg-white overflow-hidden shadow-lg rounded-lg border-l-4 border-yellow-500">
				<div class="p-6">
					<div class="flex items-center">
						<div class="flex-shrink-0">
							<div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
								<svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
								</svg>
							</div>
						</div>
						<div class="ml-5 w-0 flex-1">
							<dl>
								<dt class="text-sm font-medium text-gray-500 truncate">En tránsito</dt>
								<dd class="text-2xl font-bold text-gray-900"><?= $statusCounts['En transito'] ?? 0 ?></dd>
							</dl>
						</div>
					</div>
				</div>
			</div>

			<div class="bg-white overflow-hidden shadow-lg rounded-lg border-l-4 border-red-500">
				<div class="p-6">
					<div class="flex items-center">
						<div class="flex-shrink-0">
							<div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
								<svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
								</svg>
							</div>
						</div>
						<div class="ml-5 w-0 flex-1">
							<dl>
								<dt class="text-sm font-medium text-gray-500 truncate">Stock bajo</dt>
								<dd class="text-2xl font-bold text-gray-900"><?php echo $totalCount; ?></dd>
							</dl>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
			<div class="bg-white shadow-lg rounded-lg overflow-hidden">
				<div class="p-6">
					<h3 class="text-lg font-semibold text-gray-900 mb-6">Resumen de Solicitudes por Estado</h3>
					<div class="flex justify-center">
						<canvas id="solicitudesChart" class="max-w-sm"></canvas>
					</div>
				</div>
			</div>

			<div class="bg-white shadow-lg rounded-lg overflow-hidden">
				<div class="p-6">
					<h3 class="text-lg font-semibold text-gray-900 mb-6">Detalles por Estado</h3>
					<div class="space-y-4">
						<div class="flex items-center justify-between p-4 bg-blue-50 rounded-lg">
							<div class="flex items-center">
								<div class="w-3 h-3 bg-blue-500 rounded-full mr-3"></div>
								<span class="text-sm font-medium text-gray-700">Abierto</span>
							</div>
							<div class="text-right">
								<span class="text-lg font-bold text-blue-600"><?= $statusCounts['Abierto'] ?? 0 ?></span>
								<span class="text-sm text-gray-500 ml-2"><?= porcentaje($statusCounts['Abierto'] ?? 0, $totalSolicitudes) ?>%</span>
							</div>
						</div>

						<div class="flex items-center justify-between p-4 bg-yellow-50 rounded-lg">
							<div class="flex items-center">
								<div class="w-3 h-3 bg-yellow-500 rounded-full mr-3"></div>
								<span class="text-sm font-medium text-gray-700">En tránsito</span>
							</div>
							<div class="text-right">
								<span class="text-lg font-bold text-yellow-600"><?= $statusCounts['En transito'] ?? 0 ?></span>
								<span class="text-sm text-gray-500 ml-2"><?= porcentaje($statusCounts['En transito'] ?? 0, $totalSolicitudes) ?>%</span>
							</div>
						</div>

						<div class="flex items-center justify-between p-4 bg-green-50 rounded-lg">
							<div class="flex items-center">
								<div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
								<span class="text-sm font-medium text-gray-700">Cerrado</span>
							</div>
							<div class="text-right">
								<span class="text-lg font-bold text-green-600"><?= $statusCounts['Cerrado'] ?? 0 ?></span>
								<span class="text-sm text-gray-500 ml-2"><?= porcentaje($statusCounts['Cerrado'] ?? 0, $totalSolicitudes) ?>%</span>
							</div>
						</div>

						<div class="flex items-center justify-between p-4 bg-red-50 rounded-lg">
							<div class="flex items-center">
								<div class="w-3 h-3 bg-red-500 rounded-full mr-3"></div>
								<span class="text-sm font-medium text-gray-700">Cancelado</span>
							</div>
							<div class="text-right">
								<span class="text-lg font-bold text-red-600"><?= $statusCounts['Cancelado'] ?? 0 ?></span>
								<span class="text-sm text-gray-500 ml-2"><?= porcentaje($statusCounts['Cancelado'] ?? 0, $totalSolicitudes) ?>%</span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="bg-white shadow-lg rounded-lg overflow-hidden">
			<div class="p-6">
				<div class="flex items-center justify-between mb-6">
					<div>
						<h3 class="text-lg font-semibold text-gray-900">Productos con Stock Bajo</h3>
						<p class="text-sm text-gray-600">Productos con menos de 5 unidades en inventario</p>
					</div>
					<div class="bg-red-100 rounded-lg p-3">
						<svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
						</svg>
					</div>
				</div>

				<div class="space-y-4">
					<div class="border border-gray-200 rounded-lg">
						<button class="w-full px-4 py-3 text-left flex items-center justify-between focus:outline-none focus:bg-gray-50 hover:bg-gray-50 transition-colors duration-200" 
								onclick="toggleAccordion('pesables')">
							<div class="flex items-center">
								<svg class="w-5 h-5 text-yellow-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16l3-1m-3 1l-3-1"></path>
								</svg>
								<span class="font-medium text-gray-900">Productos Pesables</span>
							</div>
							<svg id="chevron-pesables" class="w-5 h-5 text-gray-400 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
							</svg>
						</button>
						<div id="accordion-pesables" class="hidden border-t border-gray-200">
							<div class="p-4">
								<div class="space-y-2">
									<?php
									if ($totalPesables->rowCount() > 0) {
										foreach ($totalPesables as $row) {
											echo '<div class="flex items-center justify-between py-2 px-3 bg-yellow-50 rounded-lg">
													<span class="text-sm font-medium text-gray-900">' . htmlspecialchars($row['Nombre']) . '</span>
													<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
														' . number_format($row['Cantidad'], 3) . ' Kg/grms
													</span>
												</div>';
										}
									} else {
										echo '<div class="text-center py-4 text-gray-500 text-sm">Sin productos pesables bajo stock.</div>';
									}
									?>
								</div>
							</div>
						</div>
					</div>

					<div class="border border-gray-200 rounded-lg">
						<button class="w-full px-4 py-3 text-left flex items-center justify-between focus:outline-none focus:bg-gray-50 hover:bg-gray-50 transition-colors duration-200" 
								onclick="toggleAccordion('unidades')">
							<div class="flex items-center">
								<svg class="w-5 h-5 text-blue-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
								</svg>
								<span class="font-medium text-gray-900">Productos por Unidad</span>
							</div>
							<svg id="chevron-unidades" class="w-5 h-5 text-gray-400 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
							</svg>
						</button>
						<div id="accordion-unidades" class="hidden border-t border-gray-200">
							<div class="p-4">
								<div class="space-y-2">
									<?php
									if ($totalUnidades->rowCount() > 0) {
										foreach ($totalUnidades as $row) {
											echo '<div class="flex items-center justify-between py-2 px-3 bg-blue-50 rounded-lg">
													<span class="text-sm font-medium text-gray-900">' . htmlspecialchars($row['Nombre']) . '</span>
													<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
														' . number_format($row['Cantidad'], 0) . ' uds
													</span>
												</div>';
										}
									} else {
										echo '<div class="text-center py-4 text-gray-500 text-sm">Sin productos por unidad bajo stock.</div>';
									}
									?>
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
			labels: ['Abierto', 'En tránsito', 'Cerrado', 'Cancelado'],
			datasets: [{
				data: [
					<?= $statusCounts['Abierto'] ?? 0 ?>,
					<?= $statusCounts['En transito'] ?? 0 ?>,
					<?= $statusCounts['Cerrado'] ?? 0 ?>,
					<?= $statusCounts['Cancelado'] ?? 0 ?>
				],
				backgroundColor: ['#3B82F6', '#F59E0B', '#10B981', '#EF4444'],
				borderWidth: 0,
				hoverOffset: 4
			}]
		};

		const config = {
			type: 'doughnut',
			data: data,
			options: {
				responsive: true,
				maintainAspectRatio: false,
				plugins: {
					legend: {
						position: 'bottom',
						labels: {
							padding: 20,
							usePointStyle: true,
							font: {
								size: 12
							}
						}
					},
					tooltip: {
						callbacks: {
							label: function(context) {
								let total = context.dataset.data.reduce((a, b) => a + b, 0);
								let value = context.parsed;
								let percentage = total > 0 ? ((value / total) * 100).toFixed(1) : '0.0';
								return `${context.label}: ${value} (${percentage}%)`;
							}
						}
					}
				},
				cutout: '60%'
			}
		};

		new Chart(ctx, config);
	});

	function toggleAccordion(type) {
		const accordion = document.getElementById(`accordion-${type}`);
		const chevron = document.getElementById(`chevron-${type}`);
		
		if (accordion.classList.contains('hidden')) {
			accordion.classList.remove('hidden');
			chevron.classList.add('rotate-180');
		} else {
			accordion.classList.add('hidden');
			chevron.classList.remove('rotate-180');
		}
	}
</script>