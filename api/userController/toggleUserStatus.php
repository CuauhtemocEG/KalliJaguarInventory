<?php
header('Content-Type: application/json');
session_start();

// Verificar permisos de administrador
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'Administrador') {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado. Se requieren permisos de administrador.']);
    exit;
}

require_once('../../controllers/mainController.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$user_id = (int)($_POST['user_id'] ?? 0);
$new_status = $_POST['new_status'] ?? '';
$admin_id = $_SESSION['id'];

if (!$user_id || !$new_status) {
    echo json_encode(['success' => false, 'message' => 'Parámetros requeridos: user_id y new_status']);
    exit;
}

// Validar estado
$estados_validos = ['activo', 'inactivo', 'suspendido'];
if (!in_array($new_status, $estados_validos)) {
    echo json_encode(['success' => false, 'message' => 'Estado no válido']);
    exit;
}

try {
    $pdo = conexion();
    
    // Verificar que el usuario existe y obtener información actual
    $check_query = "SELECT UsuarioID, Username, Nombre, estado, Rol FROM Usuarios WHERE UsuarioID = :user_id";
    $check_stmt = $pdo->prepare($check_query);
    $check_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $check_stmt->execute();
    $usuario = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
        exit;
    }
    
    // Evitar que el admin se desactive a sí mismo
    if ($user_id == $admin_id && $new_status !== 'activo') {
        echo json_encode(['success' => false, 'message' => 'No puedes desactivar tu propia cuenta']);
        exit;
    }
    
    // Evitar desactivar al último administrador activo (opcional)
    if ($usuario['Rol'] === 'Administrador' && $new_status !== 'activo') {
        $admin_count_query = "SELECT COUNT(*) as total FROM Usuarios WHERE Rol = 'Administrador' AND estado = 'activo' AND UsuarioID != :user_id";
        $admin_count_stmt = $pdo->prepare($admin_count_query);
        $admin_count_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $admin_count_stmt->execute();
        $admin_count = $admin_count_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($admin_count['total'] == 0) {
            echo json_encode(['success' => false, 'message' => 'No se puede desactivar el último administrador del sistema']);
            exit;
        }
    }
    
    // Actualizar el estado del usuario
    $update_query = "
        UPDATE Usuarios 
        SET 
            estado = :new_status,
            modificado_por = :admin_id,
            fecha_ultima_modificacion = NOW()
        WHERE UsuarioID = :user_id
    ";
    
    $update_stmt = $pdo->prepare($update_query);
    $update_stmt->bindParam(':new_status', $new_status);
    $update_stmt->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
    $update_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    
    if ($update_stmt->execute()) {
        // Log de la acción administrativa
        $log_query = "
            INSERT INTO admin_logs (admin_id, accion, detalles, ip_address, fecha) 
            VALUES (:admin_id, 'toggle_user_status', :detalles, :ip, NOW())
        ";
        
        $estado_anterior = $usuario['estado'];
        $detalles = "Cambio de estado de usuario: {$usuario['Nombre']} (#{$user_id}) de '{$estado_anterior}' a '{$new_status}'";
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        
        try {
            $log_stmt = $pdo->prepare($log_query);
            $log_stmt->bindParam(':admin_id', $admin_id);
            $log_stmt->bindParam(':detalles', $detalles);
            $log_stmt->bindParam(':ip', $ip);
            $log_stmt->execute();
        } catch (PDOException $e) {
            // Si no existe la tabla de logs, continúa sin error
        }
        
        // Si el usuario fue desactivado, cerrar sus sesiones activas
        if ($new_status !== 'activo') {
            // Aquí podrías implementar lógica para invalidar tokens de sesión
            // o marcar sesiones como inválidas en una tabla de sesiones
        }
        
        $accion_texto = '';
        switch ($new_status) {
            case 'activo':
                $accion_texto = 'activado';
                break;
            case 'inactivo':
                $accion_texto = 'desactivado';
                break;
            case 'suspendido':
                $accion_texto = 'suspendido';
                break;
        }
        
        echo json_encode([
            'success' => true,
            'message' => "Usuario {$accion_texto} exitosamente",
            'new_status' => $new_status,
            'user_name' => $usuario['Nombre'],
            'previous_status' => $estado_anterior
        ]);
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar el estado del usuario']);
    }
    
} catch (PDOException $e) {
    error_log("Error toggling user status: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error de base de datos']);
}
?>
