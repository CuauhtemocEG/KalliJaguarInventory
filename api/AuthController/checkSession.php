<?php
header('Content-Type: application/json');
session_start();

require_once('../../controllers/mainController.php');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    // Verificar si hay una sesión activa
    if (!isset($_SESSION['id']) || !isset($_SESSION['usuario'])) {
        echo json_encode([
            'success' => false,
            'authenticated' => false,
            'message' => 'No hay sesión activa',
            'code' => 'NO_SESSION'
        ]);
        exit;
    }
    
    // Verificar timeout de sesión (1 hora por defecto)
    $session_timeout = 3600; // 1 hora en segundos
    $current_time = time();
    $last_activity = $_SESSION['last_activity'] ?? $_SESSION['login_time'] ?? $current_time;
    
    if (($current_time - $last_activity) > $session_timeout) {
        // Sesión expirada
        session_unset();
        session_destroy();
        
        echo json_encode([
            'success' => false,
            'authenticated' => false,
            'message' => 'Sesión expirada',
            'code' => 'SESSION_EXPIRED',
            'redirect' => 'index.php?page=login'
        ]);
        exit;
    }
    
    // Actualizar última actividad
    $_SESSION['last_activity'] = $current_time;
    
    // Obtener información actualizada del usuario de la BD
    try {
        $pdo = conexion();
        $query = "
            SELECT 
                UsuarioID, 
                Nombre, 
                Rol, 
                Username, 
                email,
                estado, 
                avatar,
                ultimo_acceso
            FROM Usuarios 
            WHERE UsuarioID = :user_id 
            LIMIT 1
        ";
        
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':user_id', $_SESSION['id'], PDO::PARAM_INT);
        $stmt->execute();
        
        if ($stmt->rowCount() !== 1) {
            // Usuario no existe en BD - destruir sesión
            session_unset();
            session_destroy();
            
            echo json_encode([
                'success' => false,
                'authenticated' => false,
                'message' => 'Usuario no válido',
                'code' => 'INVALID_USER',
                'redirect' => 'index.php?page=login'
            ]);
            exit;
        }
        
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Verificar si la cuenta sigue activa
        if ($user_data['estado'] !== 'activo') {
            session_unset();
            session_destroy();
            
            echo json_encode([
                'success' => false,
                'authenticated' => false,
                'message' => 'Su cuenta ha sido desactivada',
                'code' => 'ACCOUNT_INACTIVE',
                'redirect' => 'index.php?page=login'
            ]);
            exit;
        }
        
        // Actualizar información de sesión si es necesaria
        $_SESSION['nombre'] = $user_data['Nombre'];
        $_SESSION['rol'] = $user_data['Rol'];
        $_SESSION['email'] = $user_data['email'];
        $_SESSION['avatar'] = $user_data['avatar'];
        
    } catch (PDOException $e) {
        error_log("Error verificando sesión en BD: " . $e->getMessage());
        // Continuar con la sesión existente si hay error de BD
        $user_data = [
            'UsuarioID' => $_SESSION['id'],
            'Nombre' => $_SESSION['nombre'],
            'Username' => $_SESSION['usuario'],
            'Rol' => $_SESSION['rol'],
            'email' => $_SESSION['email'] ?? null,
            'avatar' => $_SESSION['avatar'] ?? null
        ];
    }
    
    // Calcular tiempo de sesión
    $login_time = $_SESSION['login_time'] ?? $current_time;
    $session_duration = $current_time - $login_time;
    $time_remaining = $session_timeout - ($current_time - $last_activity);
    
    echo json_encode([
        'success' => true,
        'authenticated' => true,
        'user' => [
            'id' => $user_data['UsuarioID'],
            'nombre' => $user_data['Nombre'],
            'usuario' => $user_data['Username'],
            'email' => $user_data['email'],
            'rol' => $user_data['Rol'],
            'avatar' => $user_data['avatar']
        ],
        'session_info' => [
            'login_time' => date('Y-m-d H:i:s', $login_time),
            'last_activity' => date('Y-m-d H:i:s', $last_activity),
            'current_time' => date('Y-m-d H:i:s', $current_time),
            'session_duration_seconds' => $session_duration,
            'session_duration_minutes' => round($session_duration / 60, 2),
            'time_remaining_seconds' => max(0, $time_remaining),
            'time_remaining_minutes' => max(0, round($time_remaining / 60, 2)),
            'expires_at' => date('Y-m-d H:i:s', $last_activity + $session_timeout)
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Error general verificando sesión: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'authenticated' => false,
        'message' => 'Error verificando sesión',
        'code' => 'SESSION_CHECK_ERROR'
    ]);
}
?>
