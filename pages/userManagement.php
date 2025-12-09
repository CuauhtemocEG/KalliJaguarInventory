<?php
// La verificación de permisos ya se hizo en index.php antes de mostrar el navbar
// Aquí solo obtenemos los permisos específicos para mostrar/ocultar controles

require_once "./controllers/mainController.php";
$pdo = conexion();

// Obtener permisos del usuario para userManagement
$permisos_query = "
    SELECT 
        perm.PuedeVer,
        perm.PuedeCrear,
        perm.PuedeEditar,
        perm.PuedeEliminar
    FROM Permisos perm
    INNER JOIN Paginas p ON perm.PaginaID = p.PaginaID
    WHERE perm.UsuarioID = :usuarioID 
    AND p.Slug = 'userManagement'
    LIMIT 1
";
$stmt_permisos = $pdo->prepare($permisos_query);
$stmt_permisos->execute([':usuarioID' => $_SESSION['id']]);
$permisos = $stmt_permisos->fetch(PDO::FETCH_ASSOC);

// Almacenar permisos en variables (con valores por defecto si no existen)
$puedeVer = isset($permisos['PuedeVer']) ? (bool)$permisos['PuedeVer'] : false;
$puedeCrear = isset($permisos['PuedeCrear']) ? (bool)$permisos['PuedeCrear'] : false;
$puedeEditar = isset($permisos['PuedeEditar']) ? (bool)$permisos['PuedeEditar'] : false;
$puedeEliminar = isset($permisos['PuedeEliminar']) ? (bool)$permisos['PuedeEliminar'] : false;
?>

// Obtener estadísticas de usuarios
$stats_query = "
    SELECT 
        COUNT(*) as total_usuarios,
        SUM(CASE WHEN estado = 'activo' THEN 1 ELSE 0 END) as activos,
        SUM(CASE WHEN estado = 'inactivo' THEN 1 ELSE 0 END) as inactivos,
        SUM(CASE WHEN estado = 'suspendido' THEN 1 ELSE 0 END) as suspendidos,
        SUM(CASE WHEN ultimo_acceso >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as activos_semana
    FROM Usuarios
";
$stats = $pdo->query($stats_query)->fetch(PDO::FETCH_ASSOC);

// Obtener usuarios con paginación
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter_estado = isset($_GET['estado']) ? $_GET['estado'] : '';
$filter_rol = isset($_GET['rol']) ? $_GET['rol'] : '';

$where_conditions = [];
$params = [];

if ($search) {
    $where_conditions[] = "(Nombre LIKE :search OR Username LIKE :search OR email LIKE :search)";
    $params['search'] = "%$search%";
}

if ($filter_estado) {
    $where_conditions[] = "estado = :estado";
    $params['estado'] = $filter_estado;
}

if ($filter_rol) {
    $where_conditions[] = "Rol = :rol";
    $params['rol'] = $filter_rol;
}

$where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

$users_query = "
    SELECT 
        UsuarioID, Nombre, Rol, Username, email, estado, 
        fecha_creacion, ultimo_acceso, intentos_fallidos, ip_ultimo_acceso,
        CASE 
            WHEN ultimo_acceso IS NULL THEN 'Nunca'
            WHEN ultimo_acceso >= DATE_SUB(NOW(), INTERVAL 15 MINUTE) THEN 'En línea'
            WHEN ultimo_acceso >= DATE_SUB(NOW(), INTERVAL 1 DAY) THEN 'Hoy'
            WHEN ultimo_acceso >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 'Esta semana'
            ELSE 'Hace tiempo'
        END as estado_conexion,
        DATEDIFF(NOW(), fecha_creacion) as dias_registro
    FROM Usuarios 
    $where_clause 
    ORDER BY fecha_creacion DESC 
    LIMIT :limit OFFSET :offset
";

$stmt = $pdo->prepare($users_query);
foreach ($params as $key => $value) {
    $stmt->bindValue(":$key", $value);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Contar total para paginación
$count_query = "SELECT COUNT(*) as total FROM Usuarios $where_clause";
$count_stmt = $pdo->prepare($count_query);
foreach ($params as $key => $value) {
    $count_stmt->bindValue(":$key", $value);
}
$count_stmt->execute();
$total_users = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_users / $limit);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - KalliJaguar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .glass-effect { backdrop-filter: blur(16px); background: rgba(255, 255, 255, 0.1); }
        .status-dot { width: 8px; height: 8px; border-radius: 50%; }
        .status-online { background-color: #10b981; }
        .status-today { background-color: #f59e0b; }
        .status-week { background-color: #3b82f6; }
        .status-offline { background-color: #6b7280; }
        
        /* Animaciones para botones de acción */
        .action-btn {
            transition: all 0.2s ease-in-out;
            transform: scale(1);
        }
        
        .action-btn:hover {
            transform: scale(1.1);
        }
        
        .action-btn:active {
            transform: scale(0.95);
        }
        
        /* Colores específicos para iconos de acción - Simplificado */
        .btn-view {
            color: #2563eb !important; /* blue-600 */
        }
        
        .btn-view:hover {
            color: #1d4ed8 !important; /* blue-700 */
        }
        
        .btn-edit {
            color: #9333ea !important; /* purple-600 */
        }
        
        .btn-edit:hover {
            color: #7c3aed !important; /* purple-700 */
        }
        
        .btn-password {
            color: #d97706 !important; /* yellow-600 */
        }
        
        .btn-password:hover {
            color: #b45309 !important; /* yellow-700 */
        }
        
        .btn-activate {
            color: #16a34a !important; /* green-600 */
        }
        
        .btn-activate:hover {
            color: #15803d !important; /* green-700 */
        }
        
        .btn-deactivate {
            color: #dc2626 !important; /* red-600 */
        }
        
        .btn-deactivate:hover {
            color: #b91c1c !important; /* red-700 */
        }
        
        /* Asegurar que Font Awesome funcione correctamente */
        .fas:before {
            font-family: "Font Awesome 6 Free" !important;
            font-weight: 900 !important;
        }
        
        /* Prevenir duplicación de contenido en iconos */
        .btn-view .fas.fa-eye,
        .btn-edit .fas.fa-edit,
        .btn-password .fas.fa-key,
        .btn-activate .fas.fa-user-check,
        .btn-deactivate .fas.fa-user-times {
            content: '' !important;
            text-indent: 0 !important;
        }
        
        /* Estilos para fallback de emojis */
        .emoji-fallback {
            font-family: "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", sans-serif !important;
            font-weight: normal !important;
            font-size: 16px !important;
        }
        
        /* Efectos de hover para filas */
        .table-row-hover {
            transition: all 0.2s ease-in-out;
        }
        
        .table-row-hover:hover {
            background-color: #f8fafc !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        /* Estilos para notificaciones */
        .notification {
            animation: slideInRight 0.3s ease-out forwards, slideOutRight 0.3s ease-in 3.7s forwards;
            transform: translateX(100%);
        }
        
        @keyframes slideInRight {
            to { transform: translateX(0); }
        }
        
        @keyframes slideOutRight {
            to { transform: translateX(100%); opacity: 0; }
        }
        
        /* Loading spinner */
        .loading-spinner {
            border: 2px solid #f3f4f6;
            border-top: 2px solid #8b5cf6;
            border-radius: 50%;
            width: 16px;
            height: 16px;
            animation: spin 1s linear infinite;
            display: inline-block;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Tooltips */
        .tooltip {
            position: relative;
            display: inline-block;
        }
        
        .tooltip .tooltiptext {
            visibility: hidden;
            width: 120px;
            background-color: #1f2937;
            color: white;
            text-align: center;
            border-radius: 6px;
            padding: 5px;
            font-size: 12px;
            position: absolute;
            z-index: 1;
            bottom: 125%;
            left: 50%;
            margin-left: -60px;
            opacity: 0;
            transition: opacity 0.3s;
        }
        
        .tooltip:hover .tooltiptext {
            visibility: visible;
            opacity: 1;
        }
        
        /* Override para asegurar que los iconos mantengan su color */
        .action-btn i,
        .action-btn i:before,
        .action-btn i:after {
            color: inherit !important;
            font-family: "Font Awesome 6 Free" !important;
            font-weight: 900 !important;
            display: inline-block !important;
            font-style: normal !important;
            font-variant: normal !important;
            text-rendering: auto !important;
            -webkit-font-smoothing: antialiased !important;
        }
        
        /* Forzar visibilidad de iconos */
        .fas {
            font-family: "Font Awesome 6 Free" !important;
            font-weight: 900 !important;
            display: inline-block !important;
        }
        
        /* Eliminar cualquier estilo que pueda interferir con los iconos */
        .action-btn:focus,
        .action-btn:active,
        .action-btn:visited {
            outline: none !important;
            box-shadow: none !important;
        }
        
        /* Asegurar que el contenido mantenga sus colores */
        #content .action-btn,
        #content .action-btn i {
            color: inherit !important;
        }
        
        /* Estilos específicos para cada tipo de botón */
        .btn-view,
        .btn-view:hover,
        .btn-view:focus,
        .btn-view:active {
            color: #2563eb !important;
        }
        
        .btn-edit,
        .btn-edit:hover,
        .btn-edit:focus,
        .btn-edit:active {
            color: #9333ea !important;
        }
        
        .btn-password,
        .btn-password:hover,
        .btn-password:focus,
        .btn-password:active {
            color: #d97706 !important;
        }
        
        .btn-activate,
        .btn-activate:hover,
        .btn-activate:focus,
        .btn-activate:active {
            color: #16a34a !important;
        }
        
        .btn-deactivate,
        .btn-deactivate:hover,
        .btn-deactivate:focus,
        .btn-deactivate:active {
            color: #dc2626 !important;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header con estadísticas -->
    <div class="gradient-bg text-white p-6 mb-6">
        <div class="max-w-7xl mx-auto">
            <h1 class="text-3xl font-bold mb-6 flex items-center">
                <i class="fas fa-users mr-3"></i>
                Gestión de Usuarios
            </h1>
            
            <!-- Tarjetas de estadísticas -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div class="glass-effect rounded-lg p-4 border border-white/20">
                    <div class="flex items-center">
                        <div class="text-2xl font-bold"><?php echo $stats['total_usuarios']; ?></div>
                        <i class="fas fa-users ml-2 text-white/70"></i>
                    </div>
                    <div class="text-sm text-white/80">Total Usuarios</div>
                </div>
                
                <div class="glass-effect rounded-lg p-4 border border-white/20">
                    <div class="flex items-center">
                        <div class="text-2xl font-bold text-green-200"><?php echo $stats['activos']; ?></div>
                        <i class="fas fa-check-circle ml-2 text-green-200"></i>
                    </div>
                    <div class="text-sm text-white/80">Activos</div>
                </div>
                
                <div class="glass-effect rounded-lg p-4 border border-white/20">
                    <div class="flex items-center">
                        <div class="text-2xl font-bold text-red-200"><?php echo $stats['inactivos']; ?></div>
                        <i class="fas fa-times-circle ml-2 text-red-200"></i>
                    </div>
                    <div class="text-sm text-white/80">Inactivos</div>
                </div>
                
                <div class="glass-effect rounded-lg p-4 border border-white/20">
                    <div class="flex items-center">
                        <div class="text-2xl font-bold text-yellow-200"><?php echo $stats['suspendidos']; ?></div>
                        <i class="fas fa-pause-circle ml-2 text-yellow-200"></i>
                    </div>
                    <div class="text-sm text-white/80">Suspendidos</div>
                </div>
                
                <div class="glass-effect rounded-lg p-4 border border-white/20">
                    <div class="flex items-center">
                        <div class="text-2xl font-bold text-blue-200"><?php echo $stats['activos_semana']; ?></div>
                        <i class="fas fa-clock ml-2 text-blue-200"></i>
                    </div>
                    <div class="text-sm text-white/80">Activos 7 días</div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Barra de herramientas -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
                <!-- Búsqueda y filtros -->
                <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-4">
                    <div class="relative">
                        <input 
                            type="text" 
                            id="searchInput"
                            value="<?php echo htmlspecialchars($search); ?>"
                            placeholder="Buscar usuario, email..."
                            class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                    
                    <select id="filterEstado" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                        <option value="">Todos los estados</option>
                        <option value="activo" <?php echo $filter_estado === 'activo' ? 'selected' : ''; ?>>Activos</option>
                        <option value="inactivo" <?php echo $filter_estado === 'inactivo' ? 'selected' : ''; ?>>Inactivos</option>
                        <option value="suspendido" <?php echo $filter_estado === 'suspendido' ? 'selected' : ''; ?>>Suspendidos</option>
                    </select>
                    
                    <select id="filterRol" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                        <option value="">Todos los roles</option>
                        <option value="Administrador" <?php echo $filter_rol === 'Administrador' ? 'selected' : ''; ?>>Administrador</option>
                        <option value="Supervisor" <?php echo $filter_rol === 'Supervisor' ? 'selected' : ''; ?>>Supervisor</option>
                        <option value="Logistica" <?php echo $filter_rol === 'Logistica' ? 'selected' : ''; ?>>Logística</option>
                    </select>
                </div>
                
                <!-- Botones de acción -->
                <div class="flex space-x-2">
                    <?php if ($puedeCrear): ?>
                    <button onclick="openCreateUserModal()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                        <i class="fas fa-plus mr-2"></i>
                        Nuevo Usuario
                    </button>
                    <?php endif; ?>
                    
                    <button onclick="exportUsers()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                        <i class="fas fa-download mr-2"></i>
                        Exportar
                    </button>
                </div>
            </div>
        </div>

        <!-- Tabla de usuarios -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Último Acceso</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registro</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($usuarios as $usuario): ?>
                        <tr class="table-row-hover transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-gradient-to-r from-purple-400 to-pink-400 flex items-center justify-center text-white font-semibold">
                                            <?php echo strtoupper(substr($usuario['Nombre'], 0, 2)); ?>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($usuario['Nombre']); ?></div>
                                        <div class="text-sm text-gray-500">@<?php echo htmlspecialchars($usuario['Username']); ?></div>
                                        <div class="text-xs text-gray-400"><?php echo htmlspecialchars($usuario['email']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="status-dot <?php 
                                        echo $usuario['estado_conexion'] === 'En línea' ? 'status-online' : 
                                            ($usuario['estado_conexion'] === 'Hoy' ? 'status-today' : 
                                            ($usuario['estado_conexion'] === 'Esta semana' ? 'status-week' : 'status-offline')); 
                                    ?> mr-2"></div>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full <?php 
                                        echo $usuario['estado'] === 'activo' ? 'bg-green-100 text-green-800' : 
                                            ($usuario['estado'] === 'suspendido' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); 
                                    ?>">
                                        <?php echo ucfirst($usuario['estado']); ?>
                                    </span>
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    <?php 
                                    // Solo mostrar estado de conexión si no es "Nunca"
                                    if ($usuario['estado_conexion'] && $usuario['estado_conexion'] !== 'Nunca') {
                                        echo $usuario['estado_conexion'];
                                    } else {
                                        echo 'Sin conexiones registradas';
                                    }
                                    ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                    <?php echo $usuario['Rol']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php if ($usuario['ultimo_acceso']): ?>
                                    <div><?php echo date('d/m/Y H:i', strtotime($usuario['ultimo_acceso'])); ?></div>
                                    <?php if ($usuario['ip_ultimo_acceso']): ?>
                                        <div class="text-xs text-gray-400"><?php echo $usuario['ip_ultimo_acceso']; ?></div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-gray-400">Nunca</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div><?php echo date('d/m/Y', strtotime($usuario['fecha_creacion'])); ?></div>
                                <div class="text-xs text-gray-400"><?php echo $usuario['dias_registro']; ?> días</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-3">
                                    <!-- Ver detalles - Siempre visible si tiene permiso de Ver -->
                                    <button onclick="viewUser(<?php echo $usuario['UsuarioID']; ?>)" 
                                            class="btn-view text-blue-600 hover:text-blue-800 transition-colors" 
                                            title="Ver detalles">
                                        <i class="fas fa-eye text-lg"></i>
                                    </button>
                                    
                                    <?php if ($puedeEditar): ?>
                                    <!-- Editar usuario -->
                                    <button onclick="editUser(<?php echo $usuario['UsuarioID']; ?>)" 
                                            class="btn-edit text-purple-600 hover:text-purple-800 transition-colors" 
                                            title="Editar usuario">
                                        <i class="fas fa-edit text-lg"></i>
                                    </button>
                                    
                                    <!-- Resetear contraseña -->
                                    <button onclick="resetPassword(<?php echo $usuario['UsuarioID']; ?>)" 
                                            class="btn-password text-yellow-600 hover:text-yellow-800 transition-colors" 
                                            title="Resetear contraseña">
                                        <i class="fas fa-key text-lg"></i>
                                    </button>
                                    <?php endif; ?>
                                    
                                    <?php if ($puedeEliminar): ?>
                                    <!-- Activar/Desactivar -->
                                    <button onclick="toggleUserStatus(<?php echo $usuario['UsuarioID']; ?>, '<?php echo $usuario['estado']; ?>')" 
                                            class="<?php echo $usuario['estado'] === 'activo' ? 'btn-deactivate text-red-600 hover:text-red-800' : 'btn-activate text-green-600 hover:text-green-800'; ?> transition-colors" 
                                            title="<?php echo $usuario['estado'] === 'activo' ? 'Desactivar usuario' : 'Activar usuario'; ?>">
                                        <i class="fas fa-<?php echo $usuario['estado'] === 'activo' ? 'user-times' : 'user-check'; ?> text-lg"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Paginación -->
        <?php if ($total_pages > 1): ?>
        <div class="bg-white px-4 py-3 border border-gray-200 rounded-lg mt-4 flex items-center justify-between">
            <div class="text-sm text-gray-700">
                Mostrando <?php echo (($page - 1) * $limit) + 1; ?> a <?php echo min($page * $limit, $total_users); ?> 
                de <?php echo $total_users; ?> usuarios
            </div>
            <div class="flex space-x-1">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=userManagement&p=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&estado=<?php echo urlencode($filter_estado); ?>&rol=<?php echo urlencode($filter_rol); ?>" 
                       class="px-3 py-2 text-sm <?php echo $i === $page ? 'bg-purple-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'; ?> border border-gray-300 rounded-md">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Scripts -->
    <script>
        // Permisos del usuario actual
        const userPermissions = {
            puedeVer: <?php echo $puedeVer ? 'true' : 'false'; ?>,
            puedeCrear: <?php echo $puedeCrear ? 'true' : 'false'; ?>,
            puedeEditar: <?php echo $puedeEditar ? 'true' : 'false'; ?>,
            puedeEliminar: <?php echo $puedeEliminar ? 'true' : 'false'; ?>
        };
        
        // Filtros en tiempo real
        $('#searchInput, #filterEstado, #filterRol').on('change keyup', function() {
            const search = $('#searchInput').val();
            const estado = $('#filterEstado').val();
            const rol = $('#filterRol').val();
            
            const url = new URL(window.location);
            url.searchParams.set('search', search);
            url.searchParams.set('estado', estado);
            url.searchParams.set('rol', rol);
            url.searchParams.set('p', '1'); // Reset to page 1
            
            window.location.href = url.toString();
        });

        // Funciones de usuario
        function viewUser(id) {
            window.location.href = `?page=userDetails&id=${id}`;
        }

        function editUser(id) {
            if (!userPermissions.puedeEditar) {
                showNotification('No tienes permisos para editar usuarios', 'error');
                return;
            }
            window.location.href = `?page=updateUser&idUserUpdate=${id}`;
        }

        function resetPassword(id) {
            if (!userPermissions.puedeEditar) {
                showNotification('No tienes permisos para resetear contraseñas', 'error');
                return;
            }
            
            if (confirm('¿Estás seguro de que quieres resetear la contraseña de este usuario?\n\nSe generará una nueva contraseña temporal.')) {
                // Mostrar indicador de carga
                const button = event.target.closest('button');
                const originalContent = button.innerHTML;
                button.innerHTML = '<span class="loading-spinner"></span>';
                button.disabled = true;
                
                $.ajax({
                    url: 'api/userActions.php',
                    method: 'POST',
                    data: {
                        action: 'reset_password',
                        user_id: id
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showNotification('Contraseña reseteada exitosamente', 'success');
                            
                            // Mostrar nueva contraseña en un modal más elegante
                            const modal = $(`
                                <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
                                    <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                                        <div class="flex items-center mb-4">
                                            <i class="fas fa-key text-green-500 text-2xl mr-3"></i>
                                            <h3 class="text-lg font-semibold text-gray-900">Nueva Contraseña Generada</h3>
                                        </div>
                                        <div class="mb-4">
                                            <p class="text-gray-600 mb-2">La nueva contraseña temporal es:</p>
                                            <div class="bg-gray-100 p-3 rounded border font-mono text-lg text-center select-all">
                                                ${response.new_password}
                                            </div>
                                            <p class="text-sm text-gray-500 mt-2">
                                                <i class="fas fa-info-circle mr-1"></i>
                                                Haz clic en la contraseña para seleccionarla
                                            </p>
                                        </div>
                                        <button onclick="$(this).closest('.fixed').remove()" 
                                                class="w-full bg-purple-600 hover:bg-purple-700 text-white py-2 px-4 rounded transition-colors">
                                            Cerrar
                                        </button>
                                    </div>
                                </div>
                            `);
                            $('body').append(modal);
                            
                            setTimeout(() => location.reload(), 5000);
                        } else {
                            showNotification('Error: ' + response.message, 'error');
                        }
                    },
                    error: function() {
                        showNotification('Error al comunicarse con el servidor', 'error');
                    },
                    complete: function() {
                        // Restaurar botón
                        button.innerHTML = originalContent;
                        button.disabled = false;
                    }
                });
            }
        }

        function toggleUserStatus(id, currentStatus) {
            if (!userPermissions.puedeEliminar) {
                showNotification('No tienes permisos para activar/desactivar usuarios', 'error');
                return;
            }
            
            const newStatus = currentStatus === 'activo' ? 'inactivo' : 'activo';
            const action = newStatus === 'activo' ? 'activar' : 'desactivar';
            
            if (confirm(`¿Estás seguro de que quieres ${action} este usuario?`)) {
                // Mostrar indicador de carga
                const button = event.target.closest('button');
                const originalContent = button.innerHTML;
                button.innerHTML = '<span class="loading-spinner"></span>';
                button.disabled = true;
                
                $.ajax({
                    url: 'api/userActions.php',
                    method: 'POST',
                    data: {
                        action: 'toggle_status',
                        user_id: id,
                        new_status: newStatus
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showNotification('Usuario ' + action + ' exitosamente', 'success');
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            showNotification('Error: ' + response.message, 'error');
                        }
                    },
                    error: function() {
                        showNotification('Error al comunicarse con el servidor', 'error');
                    },
                    complete: function() {
                        // Restaurar botón
                        button.innerHTML = originalContent;
                        button.disabled = false;
                    }
                });
            }
        }

        function openCreateUserModal() {
            if (!userPermissions.puedeCrear) {
                showNotification('No tienes permisos para crear usuarios', 'error');
                return;
            }
            window.location.href = '?page=addUser';
        }

        function exportUsers() {
            const search = $('#searchInput').val();
            const estado = $('#filterEstado').val();
            const rol = $('#filterRol').val();
            
            // Mostrar notificación de inicio de exportación
            showNotification('Iniciando exportación de usuarios...', 'info');
            
            let url = 'api/userActions.php?action=export_users';
            if (search) url += '&search=' + encodeURIComponent(search);
            if (estado) url += '&estado=' + encodeURIComponent(estado);
            if (rol) url += '&rol=' + encodeURIComponent(rol);
            
            // Crear enlace temporal para descarga
            const link = document.createElement('a');
            link.href = url;
            link.download = 'usuarios_' + new Date().toISOString().slice(0,10) + '.csv';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            // Notificación de éxito después de un breve delay
            setTimeout(() => {
                showNotification('Exportación completada exitosamente', 'success');
            }, 1000);
        }

        // Función para mostrar notificaciones mejorada
        function showNotification(message, type) {
            const icons = {
                success: 'fa-check-circle',
                error: 'fa-exclamation-circle',
                info: 'fa-info-circle',
                warning: 'fa-exclamation-triangle'
            };
            
            const colors = {
                success: 'bg-green-500',
                error: 'bg-red-500', 
                info: 'bg-blue-500',
                warning: 'bg-yellow-500'
            };
            
            const notification = $(`
                <div class="fixed top-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg z-50 notification max-w-sm">
                    <div class="flex items-center">
                        <i class="fas ${icons[type]} mr-3 text-lg"></i>
                        <span class="flex-1">${message}</span>
                        <button onclick="$(this).closest('.notification').remove()" class="ml-3 text-white hover:text-gray-200">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `);
            
            $('body').append(notification);
            
            // Auto-remove después de 4 segundos si no es un error
            if (type !== 'error') {
                setTimeout(() => {
                    notification.fadeOut(300, function() {
                        $(this).remove();
                    });
                }, 4000);
            }
        }

        // Actualizar estado de conexión cada 30 segundos
        setInterval(function() {
            location.reload();
        }, 30000);
        
        // Debug: Verificar si Font Awesome está cargando
        $(document).ready(function() {
            console.log('Font Awesome loaded:', window.FontAwesome !== undefined);
            console.log('Icons count:', $('.fas').length);
            
            // Verificar si los iconos se cargan correctamente después de 2 segundos
            setTimeout(function() {
                let fontAwesomeLoaded = false;
                
                // Verificar si Font Awesome está realmente funcionando
                $('.fas').each(function() {
                    const computedStyle = window.getComputedStyle(this, ':before');
                    if (computedStyle && computedStyle.content && computedStyle.content !== 'none' && computedStyle.content !== '""') {
                        fontAwesomeLoaded = true;
                        return false; // Salir del each
                    }
                });
                
                // Solo aplicar fallback si Font Awesome no está funcionando
                if (!fontAwesomeLoaded) {
                    console.log('Font Awesome no detectado, aplicando fallback de emojis');
                    $('.btn-view .fas').html('👁').removeClass('fas fa-eye').addClass('emoji-fallback');
                    $('.btn-edit .fas').html('✏️').removeClass('fas fa-edit').addClass('emoji-fallback');
                    $('.btn-password .fas').html('🔑').removeClass('fas fa-key').addClass('emoji-fallback');
                    $('.btn-activate .fas').html('✅').removeClass('fas fa-user-check').addClass('emoji-fallback');
                    $('.btn-deactivate .fas').html('❌').removeClass('fas fa-user-times').addClass('emoji-fallback');
                } else {
                    console.log('Font Awesome funcionando correctamente');
                }
            }, 2000);
        });
    </script>
</body>
</html>
