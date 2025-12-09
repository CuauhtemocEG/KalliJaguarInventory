<?php
header('Content-Type: application/json');
session_name("INV");
session_start();

require_once('../../controllers/mainController.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['id']) || !isset($_SESSION['usuario'])) {
    echo json_encode([
        'success' => false,
        'message' => 'No está autenticado',
        'code' => 'NOT_AUTHENTICATED'
    ]);
    exit;
}

// Limpiar y validar datos de entrada
$current_password = limpiar_cadena($_POST['current_password'] ?? '');
$new_password = limpiar_cadena($_POST['new_password'] ?? '');
$confirm_password = limpiar_cadena($_POST['confirm_password'] ?? '');

if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
    echo json_encode([
        'success' => false,
        'message' => 'Todos los campos son obligatorios',
        'code' => 'MISSING_FIELDS'
    ]);
    exit;
}

// Verificar que las contraseñas nuevas coincidan
if ($new_password !== $confirm_password) {
    echo json_encode([
        'success' => false,
        'message' => 'Las contraseñas nuevas no coinciden',
        'code' => 'PASSWORD_MISMATCH'
    ]);
    exit;
}

// Validar formato de la nueva contraseña
if (verificar_datos("[a-zA-Z0-9$@.-]{7,100}", $new_password)) {
    echo json_encode([
        'success' => false,
        'message' => 'La nueva contraseña debe tener entre 7 y 100 caracteres válidos',
        'code' => 'INVALID_PASSWORD_FORMAT'
    ]);
    exit;
}

// Verificar que la nueva contraseña no sea igual a la actual
if ($current_password === $new_password) {
    echo json_encode([
        'success' => false,
        'message' => 'La nueva contraseña debe ser diferente a la actual',
        'code' => 'SAME_PASSWORD'
    ]);
    exit;
}

try {
    $pdo = conexion();
    
    // Obtener la contraseña actual del usuario
    $query = "SELECT Password FROM Usuarios WHERE UsuarioID = :user_id LIMIT 1";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':user_id', $_SESSION['id'], PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() !== 1) {
        echo json_encode([
            'success' => false,
            'message' => 'Usuario no encontrado',
            'code' => 'USER_NOT_FOUND'
        ]);
        exit;
    }
    
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Verificar la contraseña actual
    if (!password_verify($current_password, $user_data['Password'])) {
        echo json_encode([
            'success' => false,
            'message' => 'La contraseña actual es incorrecta',
            'code' => 'CURRENT_PASSWORD_INCORRECT'
        ]);
        exit;
    }
    
    // Hashear la nueva contraseña
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    // Actualizar la contraseña en la base de datos
    $update_query = "
        UPDATE Usuarios 
        SET Password = :new_password,
            fecha_ultimo_cambio_password = NOW(),
            fecha_ultima_modificacion = NOW(),
            token_reset_password = NULL,
            token_reset_expira = NULL
        WHERE UsuarioID = :user_id
    ";
    
    $update_stmt = $pdo->prepare($update_query);
    $update_stmt->bindParam(':new_password', $hashed_password, PDO::PARAM_STR);
    $update_stmt->bindParam(':user_id', $_SESSION['id'], PDO::PARAM_INT);
    
    if ($update_stmt->execute()) {
        // Log del cambio de contraseña
        error_log("Cambio de contraseña exitoso: {$_SESSION['usuario']} desde IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
        
        echo json_encode([
            'success' => true,
            'message' => 'Contraseña actualizada exitosamente',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al actualizar la contraseña',
            'code' => 'UPDATE_FAILED'
        ]);
    }
    
} catch (PDOException $e) {
    error_log("Error de base de datos en cambio de contraseña: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor',
        'code' => 'DATABASE_ERROR'
    ]);
} catch (Exception $e) {
    error_log("Error general en cambio de contraseña: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error inesperado',
        'code' => 'GENERAL_ERROR'
    ]);
}
?>
