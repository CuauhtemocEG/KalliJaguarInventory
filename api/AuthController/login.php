<?php
header('Content-Type: application/json');
session_start();

require_once('../../controllers/mainController.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Limpiar y validar datos de entrada
$usuario = limpiar_cadena($_POST['usuario'] ?? '');
$password = limpiar_cadena($_POST['password'] ?? '');

if (empty($usuario) || empty($password)) {
    echo json_encode([
        'success' => false,
        'message' => 'Usuario y contraseña son obligatorios',
        'code' => 'MISSING_FIELDS'
    ]);
    exit;
}

// Validar formato de usuario
if (verificar_datos("[a-zA-Z0-9]{4,20}", $usuario)) {
    echo json_encode([
        'success' => false,
        'message' => 'El usuario debe tener entre 4 y 20 caracteres alfanuméricos',
        'code' => 'INVALID_USERNAME_FORMAT'
    ]);
    exit;
}

// Validar formato de contraseña
if (verificar_datos("[a-zA-Z0-9$@.-]{7,100}", $password)) {
    echo json_encode([
        'success' => false,
        'message' => 'La contraseña debe tener entre 7 y 100 caracteres válidos',
        'code' => 'INVALID_PASSWORD_FORMAT'
    ]);
    exit;
}

try {
    $pdo = conexion();
    
    // Obtener información del usuario
    $query = "
        SELECT 
            UsuarioID, 
            Nombre, 
            Rol, 
            Username, 
            email,
            Password, 
            estado, 
            intentos_fallidos, 
            fecha_ultimo_cambio_password,
            ultimo_acceso,
            avatar
        FROM Usuarios 
        WHERE Username = :usuario 
        LIMIT 1
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':usuario', $usuario, PDO::PARAM_STR);
    $stmt->execute();
    
    if ($stmt->rowCount() !== 1) {
        // Usuario no encontrado - registrar intento fallido
        error_log("Intento de login fallido - Usuario no encontrado: $usuario desde IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
        
        echo json_encode([
            'success' => false,
            'message' => 'Credenciales incorrectas',
            'code' => 'INVALID_CREDENTIALS'
        ]);
        exit;
    }
    
    $usuarioDB = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Verificar si la cuenta está suspendida
    if ($usuarioDB['estado'] === 'suspendido') {
        echo json_encode([
            'success' => false,
            'message' => 'Su cuenta ha sido suspendida. Contacte al administrador',
            'code' => 'ACCOUNT_SUSPENDED'
        ]);
        exit;
    }
    
    // Verificar si la cuenta está inactiva
    if ($usuarioDB['estado'] === 'inactivo') {
        echo json_encode([
            'success' => false,
            'message' => 'Su cuenta está inactiva. Contacte al administrador',
            'code' => 'ACCOUNT_INACTIVE'
        ]);
        exit;
    }
    
    // Verificar si tiene demasiados intentos fallidos
    if ($usuarioDB['intentos_fallidos'] >= 5) {
        echo json_encode([
            'success' => false,
            'message' => 'Cuenta bloqueada por demasiados intentos fallidos. Contacte al administrador',
            'code' => 'ACCOUNT_LOCKED'
        ]);
        exit;
    }
    
    // Verificar contraseña
    if (!password_verify($password, $usuarioDB['Password'])) {
        // Incrementar intentos fallidos
        $update_attempts = "
            UPDATE Usuarios 
            SET intentos_fallidos = intentos_fallidos + 1,
                fecha_ultima_modificacion = NOW()
            WHERE UsuarioID = :user_id
        ";
        $update_stmt = $pdo->prepare($update_attempts);
        $update_stmt->bindParam(':user_id', $usuarioDB['UsuarioID'], PDO::PARAM_INT);
        $update_stmt->execute();
        
        error_log("Intento de login fallido - Contraseña incorrecta: $usuario desde IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
        
        $intentos_restantes = 5 - ($usuarioDB['intentos_fallidos'] + 1);
        $message = $intentos_restantes > 0 
            ? "Credenciales incorrectas. Le quedan $intentos_restantes intentos"
            : "Credenciales incorrectas. Su cuenta ha sido bloqueada";
            
        echo json_encode([
            'success' => false,
            'message' => $message,
            'code' => 'INVALID_CREDENTIALS',
            'attempts_remaining' => max(0, $intentos_restantes)
        ]);
        exit;
    }
    
    // Login exitoso - limpiar intentos fallidos y actualizar último acceso
    $client_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    $update_success = "
        UPDATE Usuarios 
        SET intentos_fallidos = 0,
            ultimo_acceso = NOW(),
            ip_ultimo_acceso = :ip,
            fecha_ultima_modificacion = NOW()
        WHERE UsuarioID = :user_id
    ";
    $success_stmt = $pdo->prepare($update_success);
    $success_stmt->bindParam(':user_id', $usuarioDB['UsuarioID'], PDO::PARAM_INT);
    $success_stmt->bindParam(':ip', $client_ip, PDO::PARAM_STR);
    $success_stmt->execute();
    
    // Regenerar ID de sesión por seguridad
    session_regenerate_id(true);
    
    // Establecer variables de sesión
    $_SESSION['id'] = $usuarioDB['UsuarioID'];
    $_SESSION['nombre'] = $usuarioDB['Nombre'];
    $_SESSION['rol'] = $usuarioDB['Rol'];
    $_SESSION['usuario'] = $usuarioDB['Username'];
    $_SESSION['email'] = $usuarioDB['email'];
    $_SESSION['avatar'] = $usuarioDB['avatar'];
    $_SESSION['login_time'] = time();
    $_SESSION['last_activity'] = time();
    $_SESSION['ip'] = $client_ip;
    
    // Log del login exitoso
    error_log("Login exitoso: {$usuarioDB['Username']} ({$usuarioDB['Nombre']}) desde IP: $client_ip");
    
    // Preparar respuesta de éxito
    $response = [
        'success' => true,
        'message' => 'Login exitoso',
        'user' => [
            'id' => $usuarioDB['UsuarioID'],
            'nombre' => $usuarioDB['Nombre'],
            'usuario' => $usuarioDB['Username'],
            'email' => $usuarioDB['email'],
            'rol' => $usuarioDB['Rol'],
            'avatar' => $usuarioDB['avatar'],
            'ultimo_acceso' => $usuarioDB['ultimo_acceso']
        ],
        'redirect' => 'index.php?page=home',
        'session_info' => [
            'login_time' => $_SESSION['login_time'],
            'expires_in' => 3600 // 1 hora
        ]
    ];
    
    echo json_encode($response);
    
} catch (PDOException $e) {
    error_log("Error de base de datos en login: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor. Intente nuevamente',
        'code' => 'DATABASE_ERROR'
    ]);
} catch (Exception $e) {
    error_log("Error general en login: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error inesperado. Intente nuevamente',
        'code' => 'GENERAL_ERROR'
    ]);
}
?>
