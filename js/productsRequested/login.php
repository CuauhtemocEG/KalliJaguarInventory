<?php
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => 'stagging.kallijaguar-inventory.com',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

header('Content-Type: application/json');

// Incluye tus funciones de conexión y sanitización
require_once '../../controllers/mainController.php'; // Ajusta la ruta si es necesario

// Recibe usuario/clave desde la app móvil
$usuario = isset($_POST['usuario']) ? limpiar_cadena($_POST['usuario']) : '';
$clave   = isset($_POST['password']) ? limpiar_cadena($_POST['password']) : '';

// Validaciones básicas
if ($usuario == "" || $clave == "") {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Usuario y contraseña requeridos']);
    exit();
}

// Conexión a BD
$check_user = conexion();
$check_user = $check_user->prepare("SELECT * FROM Usuarios WHERE Username = ?");
$check_user->execute([$usuario]);

if ($check_user->rowCount() == 1) {
    $user = $check_user->fetch();

    if ($user['Username'] == $usuario && password_verify($clave, $user['Password'])) {
        // Guarda en sesión lo necesario
        $_SESSION['id']      = $user['UsuarioID'];
        $_SESSION['nombre']  = $user['Nombre'];
        $_SESSION['rol']     = $user['Rol'];
        $_SESSION['usuario'] = $user['Username'];

        echo json_encode(['status' => 'success', 'message' => 'Login correcto']);
        exit();
    }
}

// Usuario o clave incorrectos
http_response_code(401);
echo json_encode(['status' => 'error', 'message' => 'Usuario o clave incorrectos']);
?>