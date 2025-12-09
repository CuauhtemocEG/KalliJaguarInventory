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
$admin_id = $_SESSION['id'];

// Datos del formulario
$nombre = trim($_POST['nombre'] ?? '');
$email = trim($_POST['email'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$rol = trim($_POST['rol'] ?? '');
$estado = trim($_POST['estado'] ?? '');
$notas_admin = trim($_POST['notas_admin'] ?? '');

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'ID de usuario requerido']);
    exit;
}

// Validaciones básicas
if (empty($nombre) || empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Nombre y email son obligatorios']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Email no válido']);
    exit;
}

// Validar rol
$roles_validos = ['Administrador', 'Logística', 'Supervisor'];
if (!in_array($rol, $roles_validos)) {
    echo json_encode(['success' => false, 'message' => 'Rol no válido']);
    exit;
}

// Validar estado
$estados_validos = ['activo', 'inactivo', 'suspendido'];
if (!in_array($estado, $estados_validos)) {
    echo json_encode(['success' => false, 'message' => 'Estado no válido']);
    exit;
}

try {
    $pdo = conexion();
    
    // Verificar que el usuario existe
    $check_query = "SELECT UsuarioID, Username, email as current_email, Rol as current_rol FROM Usuarios WHERE UsuarioID = :user_id";
    $check_stmt = $pdo->prepare($check_query);
    $check_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $check_stmt->execute();
    $usuario_actual = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario_actual) {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
        exit;
    }
    
    // Verificar si el email ya existe en otro usuario
    if ($email !== $usuario_actual['current_email']) {
        $email_check_query = "SELECT UsuarioID FROM Usuarios WHERE email = :email AND UsuarioID != :user_id";
        $email_check_stmt = $pdo->prepare($email_check_query);
        $email_check_stmt->bindParam(':email', $email);
        $email_check_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $email_check_stmt->execute();
        
        if ($email_check_stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'El email ya está en uso por otro usuario']);
            exit;
        }
    }
    
    // Verificar restricciones de rol (no cambiar el último admin)
    if ($usuario_actual['current_rol'] === 'Administrador' && $rol !== 'Administrador') {
        $admin_count_query = "SELECT COUNT(*) as total FROM Usuarios WHERE Rol = 'Administrador' AND UsuarioID != :user_id";
        $admin_count_stmt = $pdo->prepare($admin_count_query);
        $admin_count_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $admin_count_stmt->execute();
        $admin_count = $admin_count_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($admin_count['total'] == 0) {
            echo json_encode(['success' => false, 'message' => 'No se puede cambiar el rol del último administrador del sistema']);
            exit;
        }
    }
    
    // Evitar que el admin se quite permisos a sí mismo
    if ($user_id == $admin_id && ($rol !== 'Administrador' || $estado !== 'activo')) {
        echo json_encode(['success' => false, 'message' => 'No puedes cambiar tu propio rol o desactivar tu cuenta']);
        exit;
    }
    
    // Actualizar el usuario
    $update_query = "
        UPDATE Usuarios 
        SET 
            Nombre = :nombre,
            email = :email,
            telefono = :telefono,
            Rol = :rol,
            estado = :estado,
            notas_admin = :notas_admin,
            modificado_por = :admin_id,
            fecha_ultima_modificacion = NOW()
        WHERE UsuarioID = :user_id
    ";
    
    $update_stmt = $pdo->prepare($update_query);
    $update_stmt->bindParam(':nombre', $nombre);
    $update_stmt->bindParam(':email', $email);
    $update_stmt->bindParam(':telefono', $telefono);
    $update_stmt->bindParam(':rol', $rol);
    $update_stmt->bindParam(':estado', $estado);
    $update_stmt->bindParam(':notas_admin', $notas_admin);
    $update_stmt->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
    $update_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    
    if ($update_stmt->execute()) {
        // Log de la acción administrativa
        $log_query = "
            INSERT INTO admin_logs (admin_id, accion, detalles, ip_address, fecha) 
            VALUES (:admin_id, 'update_user_profile', :detalles, :ip, NOW())
        ";
        
        $cambios = [];
        if ($email !== $usuario_actual['current_email']) {
            $cambios[] = "email: {$usuario_actual['current_email']} → {$email}";
        }
        if ($rol !== $usuario_actual['current_rol']) {
            $cambios[] = "rol: {$usuario_actual['current_rol']} → {$rol}";
        }
        
        $detalles = "Actualización de perfil de usuario: {$nombre} (#{$user_id})";
        if (!empty($cambios)) {
            $detalles .= ". Cambios: " . implode(", ", $cambios);
        }
        
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
        
        echo json_encode([
            'success' => true,
            'message' => 'Perfil de usuario actualizado exitosamente',
            'user_name' => $nombre,
            'changes' => $cambios
        ]);
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar el perfil del usuario']);
    }
    
} catch (PDOException $e) {
    error_log("Error updating user profile: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error de base de datos']);
}
?>
