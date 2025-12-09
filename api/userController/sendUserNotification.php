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
$message = trim($_POST['message'] ?? '');
$admin_id = $_SESSION['id'];

if (!$user_id || !$message) {
    echo json_encode(['success' => false, 'message' => 'Parámetros requeridos: user_id y message']);
    exit;
}

try {
    $pdo = conexion();
    
    // Verificar que el usuario existe
    $check_query = "SELECT UsuarioID, Username, Nombre, email, estado FROM Usuarios WHERE UsuarioID = :user_id";
    $check_stmt = $pdo->prepare($check_query);
    $check_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $check_stmt->execute();
    $usuario = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
        exit;
    }
    
    // Obtener información del administrador que envía
    $admin_query = "SELECT Nombre FROM Usuarios WHERE UsuarioID = :admin_id";
    $admin_stmt = $pdo->prepare($admin_query);
    $admin_stmt->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
    $admin_stmt->execute();
    $admin = $admin_stmt->fetch(PDO::FETCH_ASSOC);
    
    // Crear la notificación
    $notification_query = "
        INSERT INTO user_notifications (
            usuario_id, 
            titulo, 
            mensaje, 
            tipo, 
            enviado_por, 
            fecha_creacion, 
            leido, 
            fecha_expira
        ) VALUES (
            :user_id,
            :titulo,
            :mensaje,
            'admin_message',
            :admin_id,
            NOW(),
            0,
            DATE_ADD(NOW(), INTERVAL 30 DAY)
        )
    ";
    
    $titulo = "Mensaje del Administrador";
    $mensaje_completo = "Mensaje de {$admin['Nombre']}:\n\n{$message}";
    
    try {
        $notification_stmt = $pdo->prepare($notification_query);
        $notification_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $notification_stmt->bindParam(':titulo', $titulo);
        $notification_stmt->bindParam(':mensaje', $mensaje_completo);
        $notification_stmt->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
        $notification_stmt->execute();
        
        $notification_success = true;
    } catch (PDOException $e) {
        // Si no existe la tabla de notificaciones, crear una versión simplificada
        $notification_success = false;
    }
    
    // Si no se pudo crear en tabla de notificaciones, intentar envío por email
    $email_sent = false;
    if (!$notification_success && !empty($usuario['email'])) {
        $email_sent = sendNotificationEmail($usuario['email'], $usuario['Nombre'], $admin['Nombre'], $message);
    }
    
    // Log de la acción administrativa
    $log_query = "
        INSERT INTO admin_logs (admin_id, accion, detalles, ip_address, fecha) 
        VALUES (:admin_id, 'send_notification', :detalles, :ip, NOW())
    ";
    
    $detalles = "Notificación enviada a usuario: {$usuario['Nombre']} (#{$user_id}). Mensaje: " . substr($message, 0, 100) . (strlen($message) > 100 ? '...' : '');
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
    
    // Determinar el método de entrega exitoso
    $delivery_method = '';
    if ($notification_success) {
        $delivery_method = 'notificación en sistema';
    } elseif ($email_sent) {
        $delivery_method = 'email';
    } else {
        $delivery_method = 'registro interno';
    }
    
    echo json_encode([
        'success' => true,
        'message' => "Notificación enviada exitosamente via {$delivery_method}",
        'user_name' => $usuario['Nombre'],
        'delivery_method' => $delivery_method,
        'notification_created' => $notification_success,
        'email_sent' => $email_sent
    ]);
    
} catch (PDOException $e) {
    error_log("Error sending notification: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error de base de datos']);
}

// Función para envío de email
function sendNotificationEmail($email, $userName, $adminName, $message) {
    // Implementación básica - se puede mejorar con PHPMailer
    $subject = "Mensaje del Administrador - Kalli Jaguar Inventory";
    $body = "
    Hola {$userName},

    Has recibido un mensaje del administrador {$adminName}:

    {$message}

    ---
    Este es un mensaje automático del sistema Kalli Jaguar Inventory.
    Por favor no respondas a este email.
    ";
    
    $headers = "From: no-reply@kallijaguar.com\r\n";
    $headers .= "Reply-To: no-reply@kallijaguar.com\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    
    return mail($email, $subject, $body, $headers);
}

// Función para crear la tabla de notificaciones si no existe
function createNotificationsTableIfNotExists($pdo) {
    $create_table = "
        CREATE TABLE IF NOT EXISTS user_notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario_id INT NOT NULL,
            titulo VARCHAR(255) NOT NULL,
            mensaje TEXT NOT NULL,
            tipo ENUM('info', 'warning', 'error', 'admin_message') DEFAULT 'info',
            enviado_por INT,
            fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
            fecha_leido DATETIME NULL,
            leido BOOLEAN DEFAULT FALSE,
            fecha_expira DATE NULL,
            FOREIGN KEY (usuario_id) REFERENCES Usuarios(UsuarioID) ON DELETE CASCADE,
            FOREIGN KEY (enviado_por) REFERENCES Usuarios(UsuarioID) ON DELETE SET NULL
        )
    ";
    
    try {
        $pdo->exec($create_table);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}
?>
