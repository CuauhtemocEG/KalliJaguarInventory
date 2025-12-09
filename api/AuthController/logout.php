<?php
header('Content-Type: application/json');
session_name("INV");
session_start();

require_once('../../controllers/mainController.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    // Verificar si hay una sesión activa
    if (!isset($_SESSION['id']) || !isset($_SESSION['usuario'])) {
        echo json_encode([
            'success' => false,
            'message' => 'No hay sesión activa',
            'code' => 'NO_SESSION'
        ]);
        exit;
    }
    
    // Guardar información de la sesión antes de destruirla (para logs)
    $user_id = $_SESSION['id'];
    $username = $_SESSION['usuario'];
    $user_name = $_SESSION['nombre'] ?? 'Unknown';
    $session_duration = time() - ($_SESSION['login_time'] ?? time());
    $client_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    // Log del logout
    $duration_minutes = round($session_duration / 60, 2);
    error_log("Logout exitoso: $username ($user_name) - Duración de sesión: {$duration_minutes} minutos - IP: $client_ip");
    
    // Opcional: Actualizar timestamp de logout en la base de datos
    try {
        $pdo = conexion();
        $update_logout = "
            UPDATE Usuarios 
            SET fecha_ultima_modificacion = NOW()
            WHERE UsuarioID = :user_id
        ";
        $stmt = $pdo->prepare($update_logout);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
    } catch (PDOException $e) {
        // Si falla la actualización de BD, continuar con el logout
        error_log("Error actualizando logout en BD: " . $e->getMessage());
    }
    
    // Limpiar variables de sesión específicas (mantener algunas para el proceso de logout)
    $temp_data = [
        'logout_time' => time(),
        'logout_user' => $username,
        'session_duration' => $session_duration
    ];
    
    // Destruir todas las variables de sesión
    session_unset();
    
    // Destruir la sesión
    session_destroy();
    
    // Iniciar nueva sesión para almacenar datos temporales del logout
    session_start();
    session_regenerate_id(true);
    
    // Almacenar mensaje temporal de logout
    $_SESSION['logout_message'] = 'Sesión cerrada exitosamente';
    $_SESSION['logout_time'] = $temp_data['logout_time'];
    
    echo json_encode([
        'success' => true,
        'message' => 'Logout exitoso',
        'session_info' => [
            'duration_seconds' => $temp_data['session_duration'],
            'duration_minutes' => round($temp_data['session_duration'] / 60, 2),
            'logout_time' => date('Y-m-d H:i:s', $temp_data['logout_time'])
        ],
        'redirect' => 'index.php?page=login'
    ]);
    
} catch (Exception $e) {
    error_log("Error durante logout: " . $e->getMessage());
    
    // En caso de error, forzar destrucción de sesión
    session_unset();
    session_destroy();
    
    echo json_encode([
        'success' => true, // Aún consideramos exitoso porque la sesión se destruyó
        'message' => 'Sesión cerrada',
        'code' => 'FORCED_LOGOUT',
        'redirect' => 'index.php?page=login'
    ]);
}
?>
