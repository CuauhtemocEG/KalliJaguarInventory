<?php
if (session_status() === PHP_SESSION_NONE) {
	session_set_cookie_params([
		'lifetime' => 0,
		'path' => '/',
		'domain' => '.kallijaguar-inventory.com',
		'secure' => true,
		'httponly' => true,
		'samesite' => 'Lax'
	]);
	session_start();
}

function limpiar_cadena($cadena) {
	return htmlspecialchars(trim($cadena), ENT_QUOTES, 'UTF-8');
}

function verificar_datos($patron, $cadena) {
	return !preg_match("/^$patron$/", $cadena);
}

$usuario = limpiar_cadena($_POST['login_usuario'] ?? '');
$clave = limpiar_cadena($_POST['login_clave'] ?? '');

if ($usuario === "" || $clave === "") {
	echo '<div class="alert alert-danger">Todos los campos son obligatorios.</div>';
	exit();
}

if (verificar_datos("[a-zA-Z0-9]{4,20}", $usuario)) {
	echo '<div class="alert alert-danger">El campo "Usuario" tiene un formato inválido.</div>';
	exit();
}

if (verificar_datos("[a-zA-Z0-9$@.-]{7,100}", $clave)) {
	echo '<div class="alert alert-danger">El campo "Clave" tiene un formato inválido.</div>';
	exit();
}

try {
	$pdo = conexion();
	$stmt = $pdo->prepare("SELECT * FROM Usuarios WHERE Username = :usuario LIMIT 1");
	$stmt->bindParam(':usuario', $usuario, PDO::PARAM_STR);
	$stmt->execute();

	if ($stmt->rowCount() === 1) {
		$usuarioDB = $stmt->fetch(PDO::FETCH_ASSOC);
		
		if (password_verify($clave, $usuarioDB['Password'])) {
			session_regenerate_id(true);
			$_SESSION['id'] = $usuarioDB['UsuarioID'];
			$_SESSION['nombre'] = $usuarioDB['Nombre'];
			$_SESSION['rol'] = $usuarioDB['Rol'];
			$_SESSION['usuario'] = $usuarioDB['Username'];

			header("Location: index.php?page=home");
			exit;
		}
	}

	echo '<div class="alert alert-danger">Usuario o contraseña incorrectos.</div>';
} catch (PDOException $e) {
	echo '<div class="alert alert-danger">Error en el servidor. Intenta más tarde.</div>';
}
