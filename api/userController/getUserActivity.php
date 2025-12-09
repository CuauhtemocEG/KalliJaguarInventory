<?php
header('Content-Type: application/json');
session_start();

// Verificar permisos de administrador
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'Administrador') {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado. Se requieren permisos de administrador.']);
    exit;
}

require_once('../../controllers/mainController.php');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$user_id = (int)($_GET['user_id'] ?? 0);
$limit = (int)($_GET['limit'] ?? 20);
$offset = (int)($_GET['offset'] ?? 0);

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'ID de usuario requerido']);
    exit;
}

try {
    $pdo = conexion();
    
    // Verificar que el usuario existe
    $check_query = "SELECT UsuarioID, Nombre FROM Usuarios WHERE UsuarioID = :user_id";
    $check_stmt = $pdo->prepare($check_query);
    $check_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $check_stmt->execute();
    $usuario = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
        exit;
    }
    
    $activity_logs = [];
    
    // Intentar obtener logs de la tabla admin_logs (acciones administrativas sobre este usuario)
    try {
        $admin_logs_query = "
            SELECT 
                'admin_action' as tipo,
                al.accion,
                al.detalles,
                al.fecha,
                al.ip_address,
                u.Nombre as ejecutado_por
            FROM admin_logs al
            JOIN Usuarios u ON al.admin_id = u.UsuarioID
            WHERE al.detalles LIKE :user_search
            ORDER BY al.fecha DESC
            LIMIT :limit OFFSET :offset
        ";
        
        $user_search = "%#{$user_id}%";
        $admin_logs_stmt = $pdo->prepare($admin_logs_query);
        $admin_logs_stmt->bindParam(':user_search', $user_search);
        $admin_logs_stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $admin_logs_stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $admin_logs_stmt->execute();
        
        while ($log = $admin_logs_stmt->fetch(PDO::FETCH_ASSOC)) {
            $activity_logs[] = [
                'tipo' => 'admin_action',
                'accion' => $log['accion'],
                'descripcion' => $log['detalles'],
                'fecha' => $log['fecha'],
                'ip_address' => $log['ip_address'],
                'ejecutado_por' => $log['ejecutado_por']
            ];
        }
    } catch (PDOException $e) {
        // Tabla admin_logs no existe
    }
    
    // Intentar obtener logs de acceso del usuario
    try {
        $access_logs_query = "
            SELECT 
                'user_access' as tipo,
                'login' as accion,
                CONCAT('Acceso al sistema desde IP: ', ip_address) as descripcion,
                fecha_acceso as fecha,
                ip_address,
                user_agent
            FROM user_access_logs
            WHERE usuario_id = :user_id
            ORDER BY fecha_acceso DESC
            LIMIT :limit OFFSET :offset
        ";
        
        $access_logs_stmt = $pdo->prepare($access_logs_query);
        $access_logs_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $access_logs_stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $access_logs_stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $access_logs_stmt->execute();
        
        while ($log = $access_logs_stmt->fetch(PDO::FETCH_ASSOC)) {
            $activity_logs[] = [
                'tipo' => 'user_access',
                'accion' => 'login',
                'descripcion' => $log['descripcion'],
                'fecha' => $log['fecha'],
                'ip_address' => $log['ip_address'],
                'user_agent' => $log['user_agent'] ?? 'No disponible'
            ];
        }
    } catch (PDOException $e) {
        // Tabla user_access_logs no existe
    }
    
    // Si no hay tablas de logs específicas, crear entradas basadas en datos básicos
    if (empty($activity_logs)) {
        // Obtener información básica del usuario para simular actividad
        $basic_info_query = "
            SELECT 
                fecha_creacion,
                fecha_ultima_modificacion,
                ultimo_acceso,
                fecha_ultimo_cambio_password,
                creado_por,
                modificado_por,
                creator.Nombre as creado_por_nombre,
                modifier.Nombre as modificado_por_nombre
            FROM Usuarios u
            LEFT JOIN Usuarios creator ON u.creado_por = creator.UsuarioID
            LEFT JOIN Usuarios modifier ON u.modificado_por = modifier.UsuarioID
            WHERE u.UsuarioID = :user_id
        ";
        
        $basic_info_stmt = $pdo->prepare($basic_info_query);
        $basic_info_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $basic_info_stmt->execute();
        $basic_info = $basic_info_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($basic_info) {
            if ($basic_info['ultimo_acceso']) {
                $activity_logs[] = [
                    'tipo' => 'user_access',
                    'accion' => 'last_login',
                    'descripcion' => 'Último acceso registrado al sistema',
                    'fecha' => $basic_info['ultimo_acceso'],
                    'ip_address' => 'No disponible'
                ];
            }
            
            if ($basic_info['fecha_ultimo_cambio_password']) {
                $activity_logs[] = [
                    'tipo' => 'security',
                    'accion' => 'password_change',
                    'descripcion' => 'Cambio de contraseña',
                    'fecha' => $basic_info['fecha_ultimo_cambio_password'],
                    'ip_address' => 'No disponible'
                ];
            }
            
            if ($basic_info['fecha_ultima_modificacion'] && $basic_info['modificado_por_nombre']) {
                $activity_logs[] = [
                    'tipo' => 'admin_action',
                    'accion' => 'profile_update',
                    'descripcion' => "Perfil actualizado por {$basic_info['modificado_por_nombre']}",
                    'fecha' => $basic_info['fecha_ultima_modificacion'],
                    'ip_address' => 'No disponible',
                    'ejecutado_por' => $basic_info['modificado_por_nombre']
                ];
            }
            
            $activity_logs[] = [
                'tipo' => 'system',
                'accion' => 'account_created',
                'descripcion' => $basic_info['creado_por_nombre'] 
                    ? "Cuenta creada por {$basic_info['creado_por_nombre']}" 
                    : 'Cuenta creada en el sistema',
                'fecha' => $basic_info['fecha_creacion'],
                'ip_address' => 'No disponible',
                'ejecutado_por' => $basic_info['creado_por_nombre'] ?? 'Sistema'
            ];
        }
    }
    
    // Ordenar los logs por fecha (más recientes primero)
    usort($activity_logs, function($a, $b) {
        return strtotime($b['fecha']) - strtotime($a['fecha']);
    });
    
    echo json_encode([
        'success' => true,
        'user_name' => $usuario['Nombre'],
        'user_id' => $user_id,
        'activity_logs' => array_slice($activity_logs, $offset, $limit),
        'total_logs' => count($activity_logs),
        'has_more' => count($activity_logs) > ($offset + $limit)
    ]);
    
} catch (PDOException $e) {
    error_log("Error getting user activity: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error de base de datos']);
}
?>
