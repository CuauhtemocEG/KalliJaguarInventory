<?php
require_once "../controllers/mainController.php";

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

header("Content-Type: application/json");

function limpiar_cadena($cadena) {
	return htmlspecialchars(trim($cadena), ENT_QUOTES, 'UTF-8');
}

function verificar_datos($patron, $cadena) {
	return !preg_match("/^$patron$/", $cadena);
}

$usuario = limpiar_cadena($_POST['login_usuario'] ?? '');
$clave = limpiar_cadena($_POST['login_clave'] ?? '');

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
	echo json_encode(["success" => false, "message" => "Error del servidor: " . $e->getMessage()]);
}
