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

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'ID de usuario requerido']);
    exit;
}

try {
    $pdo = conexion();
    
    // Verificar que el usuario existe
    $check_query = "SELECT UsuarioID, Username, Nombre, email FROM Usuarios WHERE UsuarioID = :user_id";
    $check_stmt = $pdo->prepare($check_query);
    $check_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $check_stmt->execute();
    $usuario = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
        exit;
    }
    
    // Generar nueva contraseña temporal
    $caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%';
    $nueva_password = '';
    for ($i = 0; $i < 12; $i++) {
        $nueva_password .= $caracteres[rand(0, strlen($caracteres) - 1)];
    }
    
    // Hashear la nueva contraseña
    $password_hasheada = password_hash($nueva_password, PASSWORD_BCRYPT);
    
    // Actualizar la contraseña en la base de datos
    $update_query = "
        UPDATE Usuarios 
        SET 
            password = :password,
            fecha_ultimo_cambio_password = NOW(),
            modificado_por = :admin_id,
            fecha_ultima_modificacion = NOW(),
            intentos_fallidos = 0,
            token_reset_password = NULL,
            token_reset_expira = NULL
        WHERE UsuarioID = :user_id
    ";
    
    $update_stmt = $pdo->prepare($update_query);
    $update_stmt->bindParam(':password', $password_hasheada);
    $update_stmt->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
    $update_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    
    if ($update_stmt->execute()) {
        // Log de la acción administrativa
        $log_query = "
            INSERT INTO admin_logs (admin_id, accion, detalles, ip_address, fecha) 
            VALUES (:admin_id, 'reset_password', :detalles, :ip, NOW())
        ";
        
        $detalles = "Reset de contraseña para usuario: {$usuario['Nombre']} (#{$user_id})";
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
        
        // Aquí podrías enviar un email al usuario con la nueva contraseña
        // sendPasswordResetEmail($usuario['email'], $nueva_password);
        
        echo json_encode([
            'success' => true,
            'message' => 'Contraseña reseteada exitosamente',
            'new_password' => $nueva_password,
            'user_name' => $usuario['Nombre'],
            'user_email' => $usuario['email']
        ]);
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar la contraseña']);
    }
    
} catch (PDOException $e) {
    error_log("Error resetting password: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error de base de datos']);
}

// Función opcional para envío de email (requiere configuración de email)
function sendPasswordResetEmail($email, $password) {
    // Implementar envío de email con PHPMailer u otro sistema
    // Esta función se puede expandir según las necesidades
    return true;
}
?>
