<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_lifetime', 3600);
session_start();

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once "../controllers/mainController.php";


if (!isset($_POST['login_usuario']) || !isset($_POST['login_clave'])) {
    echo json_encode(["success" => false, "message" => "Datos no recibidos correctamente"]);
    exit;
}

$usuario = limpiar_cadena($_POST['login_usuario']);
$clave = limpiar_cadena($_POST['login_clave']);

if ($usuario === "" || $clave === "") {
    echo json_encode(["success" => false, "message" => "Todos los campos son obligatorios."]);
    exit;
}

if (verificar_datos("[a-zA-Z0-9]{4,20}", $usuario)) {
    echo json_encode(["success" => false, "message" => "Usuario con formato inválido."]);
    exit;
}

if (verificar_datos("[a-zA-Z0-9$@.-]{7,100}", $clave)) {
    echo json_encode(["success" => false, "message" => "Contraseña con formato inválido."]);
    exit;
}

try {
    $pdo = conexion();

    if (!$pdo) {
        echo json_encode(["success" => false, "message" => "Error de conexión a la base de datos"]);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM Usuarios WHERE Username = :usuario LIMIT 1");
    $stmt->bindParam(':usuario', $usuario, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() === 1) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (password_verify($clave, $user['Password'])) {
            session_regenerate_id(true);
            
            $_SESSION['id'] = $user['UsuarioID'];
            $_SESSION['nombre'] = $user['Nombre'];
            $_SESSION['rol'] = $user['Rol'];
            $_SESSION['usuario'] = $user['Username'];
            $_SESSION['login_time'] = time();
            
            session_write_close();

            echo json_encode([
                "success" => true, 
                "message" => "Login exitoso",
                "user" => $user['Nombre'],
                "session_id" => session_id(),
                "redirect" => "index.php?page=home"
            ]);
            exit;
        } else {
            echo json_encode(["success" => false, "message" => "Contraseña incorrecta"]);
            exit;
        }
    } else {
        echo json_encode(["success" => false, "message" => "Usuario no encontrado"]);
        exit;
    }

    echo json_encode(["success" => false, "message" => "Usuario o contraseña incorrectos."]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Error de servidor: " . $e->getMessage()]);
}
