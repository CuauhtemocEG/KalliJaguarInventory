<?php
header("Content-Type: application/json");
require_once "../controllers/mainController.php";

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

// Validar si se están recibiendo los datos
if (!isset($_POST['login_usuario']) || !isset($_POST['login_clave'])) {
	echo json_encode(["success" => false, "message" => "Datos no recibidos"]);
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

			echo json_encode(["success" => true]);
			exit;
		}
	}

	echo json_encode(["success" => false, "message" => "Usuario o contraseña incorrectos."]);
} catch (PDOException $e) {
	echo json_encode(["success" => false, "message" => "Error de servidor: " . $e->getMessage()]);
}
