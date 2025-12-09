<?php
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'Administrador') {
    echo "<script>window.location.href='index.php?page=404';</script>";
    exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>window.location.href='index.php?page=userManagement';</script>";
    exit;
}

require_once "./controllers/mainController.php";
$pdo = conexion();

$user_id = (int)$_GET['id'];

// Obtener detalles del usuario
$user_query = "
    SELECT 
        u.UsuarioID,
        u.Nombre,
        u.Username,
        u.email,
        u.Rol,
        u.estado,
        u.fecha_creacion,
        u.fecha_ultima_modificacion,
        u.ultimo_acceso,
        u.fecha_ultimo_cambio_password,
        u.intentos_fallidos,
        u.ip_ultimo_acceso,
        u.token_reset_password,
        u.token_reset_expira,
        u.avatar,
        u.telefono,
        u.notas_admin,
        u.creado_por,
        u.modificado_por,
        creator.Nombre as creado_por_nombre,
        modifier.Nombre as modificado_por_nombre,
        DATE_FORMAT(u.fecha_creacion, '%d/%m/%Y %H:%i') as fecha_creacion_formatted,
        DATE_FORMAT(u.fecha_ultima_modificacion, '%d/%m/%Y %H:%i') as fecha_modificacion_formatted,
        DATE_FORMAT(u.ultimo_acceso, '%d/%m/%Y %H:%i') as ultimo_acceso_formatted,
        DATE_FORMAT(u.fecha_ultimo_cambio_password, '%d/%m/%Y %H:%i') as ultimo_cambio_password_formatted,
        DATE_FORMAT(u.token_reset_expira, '%d/%m/%Y %H:%i') as token_reset_expira_formatted,
        CASE 
            WHEN u.ultimo_acceso IS NULL THEN 'Nunca se ha conectado'
            WHEN u.ultimo_acceso >= DATE_SUB(NOW(), INTERVAL 15 MINUTE) THEN 'En línea ahora'
            WHEN u.ultimo_acceso >= DATE_SUB(NOW(), INTERVAL 1 HOUR) THEN 'Hace menos de 1 hora'
            WHEN u.ultimo_acceso >= DATE_SUB(NOW(), INTERVAL 1 DAY) THEN 'Hoy'
            WHEN u.ultimo_acceso >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 'Esta semana'
            WHEN u.ultimo_acceso >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 'Este mes'
            ELSE 'Hace más de un mes'
        END as estado_conexion_texto,
        DATEDIFF(NOW(), u.fecha_creacion) as dias_registro
    FROM Usuarios u
    LEFT JOIN Usuarios creator ON u.creado_por = creator.UsuarioID
    LEFT JOIN Usuarios modifier ON u.modificado_por = modifier.UsuarioID
    WHERE u.UsuarioID = :user_id
";

$stmt = $pdo->prepare($user_query);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    echo "<script>window.location.href='index.php?page=userManagement';</script>";
    exit;
}

// Obtener historial de accesos (si existe tabla de logs)
$access_logs = [];
/* 
Opcional: Si tienes tabla de logs de acceso
$logs_query = "SELECT * FROM access_logs WHERE usuario_id = :user_id ORDER BY fecha DESC LIMIT 10";
$logs_stmt = $pdo->prepare($logs_query);
$logs_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$logs_stmt->execute();
$access_logs = $logs_stmt->fetchAll(PDO::FETCH_ASSOC);
*/
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles de Usuario - <?php echo htmlspecialchars($usuario['Nombre']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        .gradient-bg { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
            overflow: hidden;
        }
        
        .gradient-bg::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 20"><defs><radialGradient id="grain"><stop offset="10%" stop-color="white" stop-opacity="0.1"/><stop offset="100%" stop-color="white" stop-opacity="0"/></radialGradient></defs><rect width="100" height="20" fill="url(%23grain)"/></svg>');
        }
        
        .glass-effect { 
            backdrop-filter: blur(16px); 
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .status-dot { 
            width: 12px; 
            height: 12px; 
            border-radius: 50%;
            box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.8);
        }
        
        .status-online { 
            background: linear-gradient(135deg, #10b981, #059669); 
            animation: pulse-online 2s infinite;
        }
        
        .status-today { 
            background: linear-gradient(135deg, #f59e0b, #d97706); 
        }
        
        .status-week { 
            background: linear-gradient(135deg, #3b82f6, #2563eb); 
        }
        
        .status-offline { 
            background: linear-gradient(135deg, #6b7280, #4b5563); 
        }
        
        @keyframes pulse-online {
            0%, 100% { 
                opacity: 1; 
                transform: scale(1);
            }
            50% { 
                opacity: 0.7; 
                transform: scale(1.1);
            }
        }
        
        .card-hover {
            transition: all 0.3s ease;
        }
        
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        .avatar-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            position: relative;
            overflow: hidden;
        }
        
        .avatar-gradient::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, #667eea, #764ba2, #f093fb, #667eea);
            border-radius: inherit;
            z-index: -1;
            animation: rotate-border 3s linear infinite;
        }
        
        @keyframes rotate-border {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .btn-action {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        
        .btn-action::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn-action:hover::before {
            left: 100%;
        }
        
        .info-card {
            background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid rgba(226, 232, 240, 0.8);
        }
        
        .stat-card {
            background: linear-gradient(145deg, #ffffff 0%, #f1f5f9 100%);
            border-left: 4px solid;
        }
        
        .activity-item {
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
        }
        
        .activity-item:hover {
            border-left-color: #3b82f6;
            background: #f8fafc;
            transform: translateX(4px);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <div class="gradient-bg text-white p-6 md:p-8">
        <div class="max-w-6xl mx-auto relative z-10">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between space-y-4 md:space-y-0">
                <div class="flex items-center space-x-4">
                    <a href="index.php?page=userManagement" class="text-white/80 hover:text-white transition-colors p-2 rounded-lg hover:bg-white/10">
                        <i class="fas fa-arrow-left text-xl"></i>
                    </a>
                    <div>
                        <h1 class="text-3xl md:text-4xl font-bold mb-2">Panel de Usuario</h1>
                        <p class="text-white/90 text-lg">Gestión completa de <?php echo htmlspecialchars($usuario['Nombre']); ?></p>
                        <div class="flex items-center mt-2 space-x-4 text-sm text-white/70">
                            <span><i class="fas fa-user-circle mr-1"></i>ID: #<?php echo $usuario['UsuarioID']; ?></span>
                            <span><i class="fas fa-calendar mr-1"></i>Registrado hace <?php echo $usuario['dias_registro']; ?> días</span>
                        </div>
                    </div>
                </div>
                
                <div class="flex flex-wrap gap-2">
                    <button onclick="editUser()" class="btn-action bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                        <i class="fas fa-edit"></i>
                        <span class="hidden sm:inline">Editar</span>
                    </button>
                    <button onclick="resetPassword()" class="btn-action bg-yellow-500/90 hover:bg-yellow-500 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                        <i class="fas fa-key"></i>
                        <span class="hidden sm:inline">Reset</span>
                    </button>
                    <button onclick="showActivityModal()" class="btn-action bg-green-500/90 hover:bg-green-500 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                        <i class="fas fa-history"></i>
                        <span class="hidden sm:inline">Actividad</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 -mt-6">
        <!-- Tarjeta principal del usuario -->
        <div class="glass-effect rounded-2xl shadow-2xl border border-white/20 p-6 md:p-8 mb-8 card-hover">
            <div class="flex flex-col lg:flex-row items-start lg:items-center space-y-6 lg:space-y-0 lg:space-x-8">
                <!-- Avatar mejorado -->
                <div class="flex-shrink-0 relative">
                    <div class="avatar-gradient h-28 w-28 md:h-32 md:w-32 rounded-2xl flex items-center justify-center text-white font-bold text-3xl shadow-xl">
                        <?php echo strtoupper(substr($usuario['Nombre'], 0, 2)); ?>
                    </div>
                    <div class="absolute -bottom-2 -right-2 bg-white rounded-full p-2 shadow-lg">
                        <div class="status-dot <?php 
                            echo strpos($usuario['estado_conexion_texto'], 'En línea') !== false ? 'status-online' : 
                                (strpos($usuario['estado_conexion_texto'], 'Hoy') !== false ? 'status-today' : 
                                (strpos($usuario['estado_conexion_texto'], 'semana') !== false ? 'status-week' : 'status-offline')); 
                        ?>"></div>
                    </div>
                </div>
                
                <!-- Información principal -->
                <div class="flex-grow">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
                        <div>
                            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($usuario['Nombre']); ?></h2>
                            <div class="flex flex-wrap items-center gap-3">
                                <span class="inline-flex items-center px-3 py-1 text-sm font-semibold rounded-full <?php 
                                    echo $usuario['estado'] === 'activo' ? 'bg-green-100 text-green-800 border border-green-200' : 
                                        ($usuario['estado'] === 'suspendido' ? 'bg-yellow-100 text-yellow-800 border border-yellow-200' : 'bg-red-100 text-red-800 border border-red-200'); 
                                ?>">
                                    <i class="fas fa-circle mr-2 text-xs"></i>
                                    <?php echo ucfirst($usuario['estado']); ?>
                                </span>
                                <span class="inline-flex items-center px-3 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800 border border-blue-200">
                                    <i class="fas fa-user-shield mr-2"></i>
                                    <?php echo $usuario['Rol']; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                        <div class="info-card p-4 rounded-xl">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-at text-purple-600"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 font-medium">Usuario</p>
                                    <p class="text-sm font-semibold text-gray-900">@<?php echo htmlspecialchars($usuario['Username']); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="info-card p-4 rounded-xl">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-envelope text-blue-600"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 font-medium">Email</p>
                                    <p class="text-sm font-semibold text-gray-900"><?php echo htmlspecialchars($usuario['email']); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="info-card p-4 rounded-xl">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-wifi text-green-600"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 font-medium">Estado</p>
                                    <p class="text-sm font-semibold text-gray-900"><?php echo $usuario['estado_conexion_texto']; ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="info-card p-4 rounded-xl">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-clock text-orange-600"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 font-medium">Último acceso</p>
                                    <p class="text-sm font-semibold text-gray-900">
                                        <?php echo $usuario['ultimo_acceso_formatted'] ?: 'Nunca'; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-4 gap-6 mb-8">
            <!-- Estadísticas rápidas -->
            <div class="stat-card border-l-blue-500 p-6 rounded-xl shadow-lg card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Días Registrado</p>
                        <p class="text-2xl font-bold text-blue-600">
                            <?php echo $usuario['dias_registro']; ?>
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-calendar-alt text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="stat-card border-l-red-500 p-6 rounded-xl shadow-lg card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Intentos Fallidos</p>
                        <p class="text-2xl font-bold text-red-600"><?php echo $usuario['intentos_fallidos'] ?: 0; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="stat-card border-l-green-500 p-6 rounded-xl shadow-lg card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Estado Sesión</p>
                        <p class="text-sm font-bold text-green-600">
                            <?php echo strpos($usuario['estado_conexion_texto'], 'En línea') !== false ? 'Conectado' : 'Desconectado'; ?>
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-wifi text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="stat-card border-l-purple-500 p-6 rounded-xl shadow-lg card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Seguridad</p>
                        <p class="text-sm font-bold text-purple-600">
                            <?php echo $usuario['fecha_ultimo_cambio_password'] ? 'Actualizada' : 'Pendiente'; ?>
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-shield-alt text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Información detallada -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Información de cuenta -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 md:p-8 card-hover">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold text-gray-900 flex items-center">
                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-user-circle text-purple-600"></i>
                            </div>
                            Información de Cuenta
                        </h3>
                        <button onclick="editUser()" class="text-purple-600 hover:text-purple-700 text-sm font-medium flex items-center">
                            <i class="fas fa-edit mr-1"></i>Editar
                        </button>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="info-card p-5 rounded-xl border border-gray-100">
                            <div class="flex items-start space-x-4">
                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-plus text-blue-600"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="text-sm text-gray-500 mb-1">Fecha de Registro</div>
                                    <div class="font-semibold text-gray-900"><?php echo $usuario['fecha_creacion_formatted']; ?></div>
                                    <?php if ($usuario['creado_por_nombre']): ?>
                                        <div class="text-xs text-gray-400 mt-1">Por: <?php echo htmlspecialchars($usuario['creado_por_nombre']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="info-card p-5 rounded-xl border border-gray-100">
                            <div class="flex items-start space-x-4">
                                <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-edit text-orange-600"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="text-sm text-gray-500 mb-1">Última Modificación</div>
                                    <div class="font-semibold text-gray-900"><?php echo $usuario['fecha_modificacion_formatted']; ?></div>
                                    <?php if ($usuario['modificado_por_nombre']): ?>
                                        <div class="text-xs text-gray-400 mt-1">Por: <?php echo htmlspecialchars($usuario['modificado_por_nombre']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="info-card p-5 rounded-xl border border-gray-100">
                            <div class="flex items-start space-x-4">
                                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-sign-in-alt text-green-600"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="text-sm text-gray-500 mb-1">Último Acceso</div>
                                    <div class="font-semibold text-gray-900">
                                        <?php echo $usuario['ultimo_acceso_formatted'] ?: 'Nunca'; ?>
                                    </div>
                                    <?php if ($usuario['ip_ultimo_acceso']): ?>
                                        <div class="text-xs text-gray-400 mt-1">IP: <?php echo $usuario['ip_ultimo_acceso']; ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="info-card p-5 rounded-xl border border-gray-100">
                            <div class="flex items-start space-x-4">
                                <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-key text-red-600"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="text-sm text-gray-500 mb-1">Último Cambio de Contraseña</div>
                                    <div class="font-semibold text-gray-900">
                                        <?php echo $usuario['ultimo_cambio_password_formatted'] ?: 'No registrado'; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información adicional -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-info-circle mr-2 text-blue-600"></i>
                        Información Adicional
                    </h3>
                    
                    <div class="space-y-4">
                        <?php if ($usuario['telefono']): ?>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Teléfono:</label>
                            <div class="mt-1 text-gray-900"><?php echo htmlspecialchars($usuario['telefono']); ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($usuario['notas_admin']): ?>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Notas Administrativas:</label>
                            <div class="mt-1 p-3 bg-yellow-50 border border-yellow-200 rounded-lg text-gray-900">
                                <?php echo nl2br(htmlspecialchars($usuario['notas_admin'])); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Historial de accesos (si existe) -->
                <?php if (!empty($access_logs)): ?>
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-history mr-2 text-green-600"></i>
                        Historial de Accesos Recientes
                    </h3>
                    
                    <div class="space-y-2">
                        <?php foreach ($access_logs as $log): ?>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <div class="font-medium"><?php echo date('d/m/Y H:i', strtotime($log['fecha'])); ?></div>
                                <div class="text-sm text-gray-500"><?php echo $log['ip_address']; ?></div>
                            </div>
                            <div class="text-sm text-gray-400"><?php echo $log['user_agent']; ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Panel de acciones -->
            <div class="space-y-6">
                <!-- Acciones rápidas -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Acciones Rápidas</h3>
                    
                    <div class="space-y-3">
                        <button onclick="editUser()" class="w-full bg-purple-600 hover:bg-purple-700 text-white py-2 px-4 rounded-lg flex items-center justify-center transition-colors">
                            <i class="fas fa-edit mr-2"></i>
                            Editar Usuario
                        </button>
                        
                        <button onclick="resetPassword()" class="w-full bg-yellow-600 hover:bg-yellow-700 text-white py-2 px-4 rounded-lg flex items-center justify-center transition-colors">
                            <i class="fas fa-key mr-2"></i>
                            Cambiar Contraseña
                        </button>
                        
                        <button onclick="toggleStatus()" class="w-full <?php echo $usuario['estado'] === 'activo' ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700'; ?> text-white py-2 px-4 rounded-lg flex items-center justify-center transition-colors">
                            <i class="fas fa-<?php echo $usuario['estado'] === 'activo' ? 'ban' : 'check'; ?> mr-2"></i>
                            <?php echo $usuario['estado'] === 'activo' ? 'Desactivar' : 'Activar'; ?> Usuario
                        </button>
                        
                        <button onclick="sendNotification()" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg flex items-center justify-center transition-colors">
                            <i class="fas fa-bell mr-2"></i>
                            Enviar Notificación
                        </button>
                    </div>
                </div>

                <!-- Estadísticas -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Estadísticas</h3>
                    
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                            <div class="flex items-center">
                                <i class="fas fa-calendar-alt text-blue-600 mr-2"></i>
                                <span class="text-sm font-medium">Días Registrado</span>
                            </div>
                            <span class="font-bold text-blue-600">
                                <?php echo ceil((time() - strtotime($usuario['fecha_creacion'])) / 86400); ?>
                            </span>
                        </div>
                        
                        <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>
                                <span class="text-sm font-medium">Intentos Fallidos</span>
                            </div>
                            <span class="font-bold text-red-600"><?php echo $usuario['intentos_fallidos'] ?: 0; ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function editUser() {
            // Mostrar modal de edición
            showEditModal();
        }

        function resetPassword() {
            if (confirm('¿Estás seguro de que quieres resetear la contraseña de este usuario?\nSe generará una nueva contraseña temporal.')) {
                const btn = event.target.closest('button');
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Procesando...';
                btn.disabled = true;
                
                $.post('api/userController/resetUserPassword.php', {
                    user_id: <?php echo $user_id; ?>
                })
                .done(function(response) {
                    if (response.success) {
                        showAlert('success', `Contraseña reseteada exitosamente para ${response.user_name}`, 
                            `Nueva contraseña temporal: <strong>${response.new_password}</strong><br>
                             <small class="text-gray-600">El usuario debe cambiarla en su próximo acceso.</small>`);
                    } else {
                        showAlert('error', 'Error al resetear contraseña', response.message);
                    }
                })
                .fail(function() {
                    showAlert('error', 'Error de conexión', 'No se pudo conectar con el servidor');
                })
                .always(function() {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                });
            }
        }

        function toggleStatus() {
            const currentStatus = '<?php echo $usuario['estado']; ?>';
            const newStatus = currentStatus === 'activo' ? 'inactivo' : 'activo';
            const action = newStatus === 'activo' ? 'activar' : 'desactivar';
            
            if (confirm(`¿Estás seguro de que quieres ${action} este usuario?`)) {
                const btn = event.target.closest('button');
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Procesando...';
                btn.disabled = true;
                
                $.post('api/userController/toggleUserStatus.php', {
                    user_id: <?php echo $user_id; ?>,
                    new_status: newStatus
                })
                .done(function(response) {
                    if (response.success) {
                        showAlert('success', response.message, `Usuario: ${response.user_name}`);
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showAlert('error', 'Error al cambiar estado', response.message);
                    }
                })
                .fail(function() {
                    showAlert('error', 'Error de conexión', 'No se pudo conectar con el servidor');
                })
                .always(function() {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                });
            }
        }

        function sendNotification() {
            const message = prompt('Escribe el mensaje a enviar al usuario:');
            if (message && message.trim()) {
                $.post('api/userController/sendUserNotification.php', {
                    user_id: <?php echo $user_id; ?>,
                    message: message.trim()
                })
                .done(function(response) {
                    if (response.success) {
                        showAlert('success', 'Notificación enviada', 
                            `Mensaje enviado a ${response.user_name} via ${response.delivery_method}`);
                    } else {
                        showAlert('error', 'Error al enviar notificación', response.message);
                    }
                })
                .fail(function() {
                    showAlert('error', 'Error de conexión', 'No se pudo conectar con el servidor');
                });
            }
        }

        function showActivityModal() {
            // Crear modal de actividad
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4';
            modal.innerHTML = `
                <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white p-6">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xl font-bold">Actividad del Usuario</h3>
                            <button onclick="closeModal()" class="text-white hover:text-gray-200">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>
                    </div>
                    <div class="p-6">
                        <div id="activity-loading" class="text-center py-8">
                            <i class="fas fa-spinner fa-spin text-3xl text-gray-400"></i>
                            <p class="text-gray-600 mt-2">Cargando actividad...</p>
                        </div>
                        <div id="activity-content" class="hidden"></div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            
            // Cargar actividad
            loadUserActivity();
        }

        function loadUserActivity() {
            $.get('api/userController/getUserActivity.php', {
                user_id: <?php echo $user_id; ?>,
                limit: 20
            })
            .done(function(response) {
                const loading = document.getElementById('activity-loading');
                const content = document.getElementById('activity-content');
                
                loading.classList.add('hidden');
                content.classList.remove('hidden');
                
                if (response.success && response.activity_logs.length > 0) {
                    let html = '<div class="space-y-4">';
                    response.activity_logs.forEach(log => {
                        const icon = getActivityIcon(log.tipo);
                        const color = getActivityColor(log.tipo);
                        html += `
                            <div class="activity-item p-4 rounded-lg border border-gray-200">
                                <div class="flex items-start space-x-3">
                                    <div class="w-10 h-10 ${color} rounded-lg flex items-center justify-center flex-shrink-0">
                                        <i class="fas ${icon}"></i>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-start justify-between">
                                            <div>
                                                <p class="font-medium text-gray-900">${log.descripcion}</p>
                                                <p class="text-sm text-gray-500">${new Date(log.fecha).toLocaleString()}</p>
                                            </div>
                                            <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full">${log.accion}</span>
                                        </div>
                                        ${log.ip_address ? `<p class="text-xs text-gray-400 mt-1">IP: ${log.ip_address}</p>` : ''}
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    html += '</div>';
                    content.innerHTML = html;
                } else {
                    content.innerHTML = `
                        <div class="text-center py-8">
                            <i class="fas fa-history text-4xl text-gray-300"></i>
                            <p class="text-gray-600 mt-2">No hay actividad reciente registrada</p>
                        </div>
                    `;
                }
            })
            .fail(function() {
                const loading = document.getElementById('activity-loading');
                const content = document.getElementById('activity-content');
                loading.classList.add('hidden');
                content.classList.remove('hidden');
                content.innerHTML = `
                    <div class="text-center py-8 text-red-600">
                        <i class="fas fa-exclamation-triangle text-4xl"></i>
                        <p class="mt-2">Error al cargar la actividad</p>
                    </div>
                `;
            });
        }

        function getActivityIcon(tipo) {
            const icons = {
                'admin_action': 'fa-user-shield',
                'user_access': 'fa-sign-in-alt',
                'security': 'fa-shield-alt',
                'system': 'fa-cog'
            };
            return icons[tipo] || 'fa-info-circle';
        }

        function getActivityColor(tipo) {
            const colors = {
                'admin_action': 'bg-purple-100 text-purple-600',
                'user_access': 'bg-green-100 text-green-600',
                'security': 'bg-red-100 text-red-600',
                'system': 'bg-blue-100 text-blue-600'
            };
            return colors[tipo] || 'bg-gray-100 text-gray-600';
        }

        function closeModal() {
            const modal = document.querySelector('.fixed.inset-0.bg-black');
            if (modal) {
                modal.remove();
            }
        }

        function showAlert(type, title, message) {
            const alertColors = {
                'success': 'bg-green-50 border-green-200 text-green-800',
                'error': 'bg-red-50 border-red-200 text-red-800',
                'warning': 'bg-yellow-50 border-yellow-200 text-yellow-800'
            };
            
            const icons = {
                'success': 'fa-check-circle',
                'error': 'fa-exclamation-circle',
                'warning': 'fa-exclamation-triangle'
            };
            
            const alert = document.createElement('div');
            alert.className = `fixed top-4 right-4 z-50 max-w-md w-full ${alertColors[type]} border rounded-lg shadow-lg p-4`;
            alert.innerHTML = `
                <div class="flex items-start">
                    <i class="fas ${icons[type]} text-lg mr-3 mt-0.5"></i>
                    <div class="flex-1">
                        <h4 class="font-bold">${title}</h4>
                        <p class="text-sm mt-1">${message}</p>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-lg">×</button>
                </div>
            `;
            
            document.body.appendChild(alert);
            setTimeout(() => alert.remove(), 5000);
        }

        function showEditModal() {
            // Implementar modal de edición aquí
            showAlert('info', 'Función en desarrollo', 'La edición de usuarios se implementará próximamente');
        }

        // Cerrar modal con Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });
    </script>
</body>
</html>
