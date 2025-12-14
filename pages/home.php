<?php
require_once "./controllers/mainController.php";

$userID = $_SESSION['id'];
$userName = $_SESSION['nombre'];
$userRole = $_SESSION['rol'];
$userUsername = $_SESSION['usuario'];

$db = conexion();

$statsStmt = $db->prepare("
    SELECT 
        COUNT(DISTINCT ComandaID) as totalComandas,
        SUM(CASE WHEN Status = 'Abierto' THEN 1 ELSE 0 END) as abiertas,
        SUM(CASE WHEN Status = 'En transito' THEN 1 ELSE 0 END) as enTransito,
        SUM(CASE WHEN Status = 'Cerrado' THEN 1 ELSE 0 END) as cerradas,
        SUM(CASE WHEN Status = 'Cancelado' THEN 1 ELSE 0 END) as canceladas,
        SUM(CASE WHEN MONTH(FechaMovimiento) = MONTH(CURRENT_DATE()) 
            AND YEAR(FechaMovimiento) = YEAR(CURRENT_DATE()) THEN 1 ELSE 0 END) as comandasMes
    FROM (
        SELECT DISTINCT ComandaID, Status, FechaMovimiento
        FROM MovimientosInventario
        WHERE UsuarioID = :userID AND TipoMovimiento = 'Salida'
    ) as subquery
");
$statsStmt->execute([':userID' => $userID]);
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

$totalMisComandas = (int) $stats['totalComandas'];
$comandasMes = (int) $stats['comandasMes'];
$misEstados = [
    'Abierto' => (int) $stats['abiertas'],
    'En transito' => (int) $stats['enTransito'],
    'Cerrado' => (int) $stats['cerradas'],
    'Cancelado' => (int) $stats['canceladas']
];

$actividadRecienteStmt = $db->prepare("
    SELECT 
        ComandaID,
        Status,
        MAX(FechaMovimiento) as Fecha,
        COUNT(DISTINCT ProductoID) as TotalProductos,
        SUM(Cantidad) as CantidadTotal,
        MAX(s.nombre) as Sucursal
    FROM MovimientosInventario mi
    LEFT JOIN Sucursales s ON mi.SucursalID = s.SucursalID
    WHERE mi.UsuarioID = :userID AND mi.TipoMovimiento = 'Salida'
    GROUP BY ComandaID, Status
    ORDER BY MAX(FechaMovimiento) DESC
    LIMIT 5
");
$actividadRecienteStmt->execute([':userID' => $userID]);
$actividadReciente = $actividadRecienteStmt->fetchAll(PDO::FETCH_ASSOC);

$topProductosStmt = $db->prepare("
    SELECT 
        p.Nombre,
        SUM(mi.Cantidad) as TotalSolicitado,
        COUNT(DISTINCT mi.ComandaID) as NumeroComandas
    FROM MovimientosInventario mi
    INNER JOIN Productos p ON mi.ProductoID = p.ProductoID
    WHERE mi.UsuarioID = :userID AND mi.TipoMovimiento = 'Salida'
    GROUP BY p.ProductoID, p.Nombre
    ORDER BY TotalSolicitado DESC
    LIMIT 5
");
$topProductosStmt->execute([':userID' => $userID]);
$topProductos = $topProductosStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="min-h-screen bg-gray-50 py-4">
	<div class="w-full mx-auto px-4 sm:px-6 lg:px-8">
		
		<div class="bg-gradient-to-r from-gray-900 via-gray-800 to-black rounded-xl shadow-2xl p-6 mb-8 text-white border border-gray-700">
			<div class="flex flex-col md:flex-row justify-between items-start md:items-center">
				<div class="mb-4 md:mb-0">
					<h1 class="text-3xl font-bold mb-2 text-white">
						¡Hola, <?php echo htmlspecialchars($userName); ?>! 👋
					</h1>
					<p class="text-gray-300 text-lg flex items-center">
						<svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
							<path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
						</svg>
						<span class="font-medium text-white"><?php echo htmlspecialchars($userRole); ?></span>
						<span class="mx-2 text-gray-500">•</span>
						<span class="text-gray-400">@<?php echo htmlspecialchars($userUsername); ?></span>
					</p>
				</div>
				<?php if ($_SESSION['id'] == '1') { ?>
				<div class="flex flex-wrap gap-3">
					<a href="index.php?page=dashboardAvanzado" class="inline-flex items-center px-4 py-2 bg-white text-gray-900 text-sm font-medium rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white transition-all duration-200 shadow-md">
						<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path>
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path>
						</svg>
						Dashboard Analítico
					</a>
				</div>
				<?php } ?>
			</div>
		</div>

		<div class="mb-8">
			<h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
				<svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
				</svg>
				Mi Actividad
			</h2>
			<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
				<div class="bg-white overflow-hidden shadow-lg rounded-lg border-l-4 border-blue-500 hover:shadow-xl transition-shadow duration-300">
					<div class="p-6">
						<div class="flex items-center">
							<div class="flex-shrink-0">
								<div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
									<svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
									</svg>
								</div>
							</div>
							<div class="ml-5 w-0 flex-1">
								<dl>
									<dt class="text-sm font-medium text-gray-500 truncate">Mis Comandas</dt>
									<dd class="text-3xl font-bold text-gray-900"><?php echo $totalMisComandas; ?></dd>
								</dl>
							</div>
						</div>
					</div>
				</div>

				<div class="bg-white overflow-hidden shadow-lg rounded-lg border-l-4 border-green-500 hover:shadow-xl transition-shadow duration-300">
					<div class="p-6">
						<div class="flex items-center">
							<div class="flex-shrink-0">
								<div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
									<svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
									</svg>
								</div>
							</div>
							<div class="ml-5 w-0 flex-1">
								<dl>
									<dt class="text-sm font-medium text-gray-500 truncate">Abiertas</dt>
									<dd class="text-3xl font-bold text-gray-900"><?php echo $misEstados['Abierto'] ?? 0; ?></dd>
								</dl>
							</div>
						</div>
					</div>
				</div>

				<div class="bg-white overflow-hidden shadow-lg rounded-lg border-l-4 border-yellow-500 hover:shadow-xl transition-shadow duration-300">
					<div class="p-6">
						<div class="flex items-center">
							<div class="flex-shrink-0">
								<div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
									<svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
									</svg>
								</div>
							</div>
							<div class="ml-5 w-0 flex-1">
								<dl>
									<dt class="text-sm font-medium text-gray-500 truncate">En Tránsito</dt>
									<dd class="text-3xl font-bold text-gray-900"><?php echo $misEstados['En transito'] ?? 0; ?></dd>
								</dl>
							</div>
						</div>
					</div>
				</div>

				<div class="bg-white overflow-hidden shadow-lg rounded-lg border-l-4 border-purple-500 hover:shadow-xl transition-shadow duration-300">
					<div class="p-6">
						<div class="flex items-center">
							<div class="flex-shrink-0">
								<div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
									<svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
									</svg>
								</div>
							</div>
							<div class="ml-5 w-0 flex-1">
								<dl>
									<dt class="text-sm font-medium text-gray-500 truncate">Este Mes</dt>
									<dd class="text-3xl font-bold text-gray-900"><?php echo $comandasMes; ?></dd>
								</dl>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
			<div class="bg-white shadow-lg rounded-lg overflow-hidden">
				<div class="p-6 border-b border-gray-200">
					<h3 class="text-lg font-semibold text-gray-900 flex items-center">
						<svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
						</svg>
						Mis Últimas Comandas
					</h3>
				</div>
				<div class="p-6">
					<?php if (count($actividadReciente) > 0): ?>
						<div class="space-y-3">
							<?php foreach ($actividadReciente as $actividad): ?>
								<a href="index.php?page=comandaDetails&comandaID=<?php echo urlencode($actividad['ComandaID']); ?>" 
								   class="flex items-center justify-between p-4 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors duration-200 border border-gray-200">
									<div class="flex-1">
										<div class="flex items-center mb-1">
											<span class="font-medium text-gray-900"><?php echo htmlspecialchars($actividad['ComandaID']); ?></span>
											<span class="ml-3 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
												<?php 
													echo $actividad['Status'] === 'Abierto' ? 'bg-blue-100 text-blue-800' : 
														($actividad['Status'] === 'En transito' ? 'bg-yellow-100 text-yellow-800' : 
														($actividad['Status'] === 'Cerrado' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'));
												?>">
												<?php echo htmlspecialchars($actividad['Status']); ?>
											</span>
										</div>
										<div class="text-sm text-gray-600">
											<span class="font-medium"><?php echo $actividad['TotalProductos']; ?></span> productos •
											<span class="font-medium"><?php echo $actividad['CantidadTotal']; ?></span> unidades
											<?php if ($actividad['Sucursal']): ?>
												• <?php echo htmlspecialchars($actividad['Sucursal']); ?>
											<?php endif; ?>
										</div>
										<div class="text-xs text-gray-500 mt-1">
											<?php echo date('d/m/Y H:i', strtotime($actividad['Fecha'])); ?>
										</div>
									</div>
									<svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
									</svg>
								</a>
							<?php endforeach; ?>
						</div>
					<?php else: ?>
						<div class="text-center py-8">
							<svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
							</svg>
							<p class="text-gray-500">No tienes comandas aún</p>
							<a href="?page=requestInsumos" class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
								Crear mi primera comanda
							</a>
						</div>
					<?php endif; ?>
				</div>
			</div>

			<div class="bg-white shadow-lg rounded-lg overflow-hidden">
				<div class="p-6 border-b border-gray-200">
					<h3 class="text-lg font-semibold text-gray-900 flex items-center">
						<svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
						</svg>
						Mis Productos Más Solicitados
					</h3>
				</div>
				<div class="p-6">
					<?php if (count($topProductos) > 0): ?>
						<div class="space-y-3">
							<?php foreach ($topProductos as $index => $producto): ?>
								<div class="flex items-center p-3 bg-gray-50 rounded-lg border border-gray-200">
									<div class="flex-shrink-0 mr-4">
										<div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold">
											<?php echo $index + 1; ?>
										</div>
									</div>
									<div class="flex-1 min-w-0">
										<p class="text-sm font-medium text-gray-900 truncate">
											<?php echo htmlspecialchars($producto['Nombre']); ?>
										</p>
										<div class="flex items-center text-xs text-gray-600">
											<span class="font-semibold text-blue-600"><?php echo number_format($producto['TotalSolicitado']); ?></span>
											<span class="mx-1">unidades en</span>
											<span class="font-semibold text-green-600"><?php echo $producto['NumeroComandas']; ?></span>
											<span class="ml-1">comandas</span>
										</div>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					<?php else: ?>
						<div class="text-center py-8">
							<svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
							</svg>
							<p class="text-gray-500">No has solicitado productos aún</p>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>


	</div>
</div>

<script>
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