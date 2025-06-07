<?php
header("Content-Type: application/json");

require_once "../controllers/mainController.php";

// Registrar errores en archivo
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    file_put_contents("log.txt", "ERROR: $errstr en $errfile:$errline\n", FILE_APPEND);
    return false;
});

// Log de inicio
file_put_contents("log.txt", "INICIO LOGIN\n", FILE_APPEND);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_POST['login_usuario']) || !isset($_POST['login_clave'])) {
    file_put_contents("log.txt", "FALTAN DATOS POST\n", FILE_APPEND);
    echo json_encode(["success" => false, "message" => "Datos no recibidos"]);
    exit;
}

$usuario = limpiar_cadena($_POST['login_usuario']);
$clave = limpiar_cadena($_POST['login_clave']);

file_put_contents("log.txt", "Usuario: $usuario\n", FILE_APPEND);

if ($usuario === "" || $clave === "") {
    file_put_contents("log.txt", "CAMPOS VACÍOS\n", FILE_APPEND);
    echo json_encode(["success" => false, "message" => "Todos los campos son obligatorios."]);
    exit;
}

if (verificar_datos("[a-zA-Z0-9]{4,20}", $usuario)) {
    file_put_contents("log.txt", "FORMATO USUARIO INVÁLIDO\n", FILE_APPEND);
    echo json_encode(["success" => false, "message" => "Usuario con formato inválido."]);
    exit;
}

if (verificar_datos("[a-zA-Z0-9$@.-]{7,100}", $clave)) {
    file_put_contents("log.txt", "FORMATO CLAVE INVÁLIDO\n", FILE_APPEND);
    echo json_encode(["success" => false, "message" => "Contraseña con formato inválido."]);
    exit;
}

try {
    $pdo = conexion();

    if (!$pdo) {
        file_put_contents("log.txt", "FALLO CONEXIÓN\n", FILE_APPEND);
        echo json_encode(["success" => false, "message" => "Error de conexión a la base de datos"]);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM Usuarios WHERE Username = :usuario LIMIT 1");
    $stmt->bindParam(':usuario', $usuario, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() === 1) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        file_put_contents("log.txt", "USUARIO ENCONTRADO\n", FILE_APPEND);
        file_put_contents("log.txt", "Clave ingresada: $clave\n", FILE_APPEND);
        file_put_contents("log.txt", "Hash BD: {$user['Password']}\n", FILE_APPEND);

        if (password_verify($clave, $user['Password'])) {
            file_put_contents("log.txt", "PASSWORD VERIFICADO\n", FILE_APPEND);

            session_regenerate_id(true);
            $_SESSION['id'] = $user['UsuarioID'];
            $_SESSION['nombre'] = $user['Nombre'];
            $_SESSION['rol'] = $user['Rol'];
            $_SESSION['usuario'] = $user['Username'];

            echo json_encode(["success" => true, "message" => "Login completado"]);
            exit;
        } else {
            file_put_contents("log.txt", "PASSWORD INCORRECTO\n", FILE_APPEND);
        }
    } else {
        file_put_contents("log.txt", "USUARIO NO ENCONTRADO\n", FILE_APPEND);
    }

    echo json_encode(["success" => false, "message" => "Usuario o contraseña incorrectos."]);
} catch (PDOException $e) {
    file_put_contents("log.txt", "EXCEPCIÓN: " . $e->getMessage() . "\n", FILE_APPEND);
    echo json_encode(["success" => false, "message" => "Error de servidor: " . $e->getMessage()]);
}
