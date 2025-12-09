<?php
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'Administrador') {
    echo "<script>window.location.href='index.php?page=404';</script>";
    exit;
}

require_once "./controllers/mainController.php";
$pdo = conexion();

// Obtener estadísticas de actividad
$activity_stats = [];

// Usuarios activos por hora (últimas 24 horas)
$hourly_activity = $pdo->query("
    SELECT 
        HOUR(ultimo_acceso) as hora,
        COUNT(*) as usuarios_activos
    FROM Usuarios 
    WHERE ultimo_acceso >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
    GROUP BY HOUR(ultimo_acceso)
    ORDER BY hora
")->fetchAll(PDO::FETCH_ASSOC);

// Nuevos usuarios por mes (último año)
$monthly_registrations = $pdo->query("
    SELECT 
        DATE_FORMAT(fecha_creacion, '%Y-%m') as mes,
        COUNT(*) as nuevos_usuarios
    FROM Usuarios 
    WHERE fecha_creacion >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(fecha_creacion, '%Y-%m')
    ORDER BY mes
")->fetchAll(PDO::FETCH_ASSOC);

// Top usuarios más activos
$most_active = $pdo->query("
    SELECT 
        Nombre, Username, 
        DATE_FORMAT(ultimo_acceso, '%d/%m/%Y %H:%i') as ultimo_acceso_formatted,
        TIMESTAMPDIFF(MINUTE, ultimo_acceso, NOW()) as minutos_desde_acceso
    FROM Usuarios 
    WHERE ultimo_acceso IS NOT NULL
    ORDER BY ultimo_acceso DESC
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

// Usuarios por rol
$users_by_role = $pdo->query("
    SELECT 
        Rol,
        COUNT(*) as total,
        SUM(CASE WHEN estado = 'activo' THEN 1 ELSE 0 END) as activos
    FROM Usuarios
    GROUP BY Rol
    ORDER BY total DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actividad de Usuarios - KalliJaguar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <div class="gradient-bg text-white p-6">
        <div class="max-w-7xl mx-auto">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold flex items-center">
                        <i class="fas fa-chart-line mr-3"></i>
                        Actividad de Usuarios
                    </h1>
                    <p class="text-white/80 mt-2">Panel de monitoreo y estadísticas</p>
                </div>
                <a href="index.php?page=userManagement" class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-users mr-2"></i>
                    Gestión de Usuarios
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Actividad por horas -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-clock mr-2 text-blue-600"></i>
                Actividad por Horas (Últimas 24h)
            </h3>
            <div class="h-64">
                <canvas id="hourlyChart"></canvas>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Registros mensuales -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-user-plus mr-2 text-green-600"></i>
                    Registros Mensuales
                </h3>
                <div class="h-64">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>

            <!-- Usuarios por rol -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-pie-chart mr-2 text-purple-600"></i>
                    Distribución por Roles
                </h3>
                <div class="h-64">
                    <canvas id="rolesChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Top usuarios activos -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-star mr-2 text-yellow-600"></i>
                Usuarios Más Activos
            </h3>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Último Acceso</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($most_active as $user): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8">
                                        <div class="h-8 w-8 rounded-full bg-gradient-to-r from-blue-400 to-purple-400 flex items-center justify-center text-white text-sm font-semibold">
                                            <?php echo strtoupper(substr($user['Nombre'], 0, 2)); ?>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user['Nombre']); ?></div>
                                        <div class="text-sm text-gray-500">@<?php echo htmlspecialchars($user['Username']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo $user['ultimo_acceso_formatted']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php 
                                $minutos = $user['minutos_desde_acceso'];
                                if ($minutos <= 15): ?>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">En línea</span>
                                <?php elseif ($minutos <= 60): ?>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Reciente</span>
                                <?php else: ?>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="index.php?page=userDetails&id=<?php echo $user['UsuarioID'] ?? '#'; ?>" 
                                   class="text-purple-600 hover:text-purple-900">
                                    Ver detalles
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Configuración de Chart.js
        Chart.defaults.font.family = 'Inter, sans-serif';
        Chart.defaults.color = '#374151';

        // Gráfico de actividad por horas
        const hourlyData = <?php echo json_encode($hourly_activity); ?>;
        const hourlyCtx = document.getElementById('hourlyChart').getContext('2d');
        
        new Chart(hourlyCtx, {
            type: 'line',
            data: {
                labels: Array.from({length: 24}, (_, i) => i + ':00'),
                datasets: [{
                    label: 'Usuarios Activos',
                    data: Array.from({length: 24}, (_, i) => {
                        const found = hourlyData.find(item => item.hora == i);
                        return found ? found.usuarios_activos : 0;
                    }),
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Gráfico de registros mensuales
        const monthlyData = <?php echo json_encode($monthly_registrations); ?>;
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        
        new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: monthlyData.map(item => item.mes),
                datasets: [{
                    label: 'Nuevos Usuarios',
                    data: monthlyData.map(item => item.nuevos_usuarios),
                    backgroundColor: '#10B981',
                    borderColor: '#059669',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Gráfico de usuarios por rol
        const rolesData = <?php echo json_encode($users_by_role); ?>;
        const rolesCtx = document.getElementById('rolesChart').getContext('2d');
        
        new Chart(rolesCtx, {
            type: 'doughnut',
            data: {
                labels: rolesData.map(item => item.Rol),
                datasets: [{
                    data: rolesData.map(item => item.total),
                    backgroundColor: [
                        '#8B5CF6',
                        '#3B82F6',
                        '#10B981',
                        '#F59E0B',
                        '#EF4444'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });

        // Auto-refresh cada 5 minutos
        setInterval(() => {
            location.reload();
        }, 300000);
    </script>
</body>
</html>
