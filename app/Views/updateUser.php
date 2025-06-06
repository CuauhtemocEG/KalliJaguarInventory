<?php
require_once "./controllers/mainController.php";

$id = (isset($_GET['idUserUpdate'])) ? $_GET['idUserUpdate'] : 0;
$id = limpiar_cadena($id);
?>
<div class="container-fluid" style="padding-top:15px; padding-bottom:15px;">
	<div class="card">
		<?php if ($id == $_SESSION['id']) { ?>
			<div class="card-header font-weight-bold">Mi Cuenta - Actualiza tus datos</div>
		<?php } else { ?>
			<div class="card-header font-weight-bold">Actualizar datos del Usuario</div>
		<?php } ?>
		<div class="card-body">
			<?php

			//include "./includes/btn_back.php";

			/*== Verificando usuario ==*/
			$check_usuario = conexion();
			$check_usuario = $check_usuario->query("SELECT * FROM Usuarios WHERE UsuarioID='$id'");

			if ($check_usuario->rowCount() > 0) {
				$datos = $check_usuario->fetch();
			?>
				<div class="form-rest"></div>

				<form action="./controllers/updateUserController.php" method="POST" class="FormularioAjax" autocomplete="off">

					<input type="hidden" name="idUser" value="<?php echo $datos['UsuarioID']; ?>" required>

					<div class="form-row">
						<div class="form-group col-md-6">
							<b><label>Nombre:</label></b>
							<input class="form-control" type="text" name="userName" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,40}" maxlength="40" required value="<?php echo $datos['Nombre']; ?>">
						</div>
						<div class="form-group col-md-6">
							<b><label>Rol del Usuario:</label></b>
							<select class="custom-select" id="userRol" name="userRol" required>
								<option value="<?php echo $datos['Rol']; ?>"><?php echo $datos['Rol']; ?></option>
								<option value="Administrador">Administrador</option>
								<option value="Logistica">Logistica</option>
								<option value="Supervisor">Supervisor</option>
							</select>
							<small id="userHelp" class="form-text text-muted">Existen 3 roles (Administrador, Logística y Supervisor) cada uno contará con módulos distintos.</small>
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-md-6">
							<b><label>Usuario:</label></b>
							<input class="form-control" type="text" name="user" pattern="[a-zA-Z0-9]{4,20}" maxlength="20" required value="<?php echo $datos['Username']; ?>">
						</div>
						<div class="form-group col-md-6">
							<b><label>Email:</label></b>
							<input class="form-control" type="email" name="userEmail" maxlength="70" value="<?php echo $datos['email']; ?>">
						</div>
					</div>

					<hr>
					<p class="has-text-centered">
						<strong>Nota:</strong> Si desea actualizar la contraseña del Usuario, recuerde que ambas deben ser iguales, en caso contrario, los campos pueden permanecer vacíos.
					</p>
					<br>
					<div class="form-row">
						<div class="form-group col-md-6">
							<b><label>Password:</label></b>
							<input class="form-control" type="password" name="userPassword" pattern="[a-zA-Z0-9$@.-]{7,100}" maxlength="100">
						</div>
						<div class="form-group col-md-6">
							<b><label>Repetir Password:</label></b>
							<input class="form-control" type="password" name="userPassword2" pattern="[a-zA-Z0-9$@.-]{7,100}" maxlength="100">
						</div>
					</div>
					<hr>
					<p class="has-text-centered">
						<strong>Nota:</strong> Para realizar la actualización de los datos del usuario por favor ingrese su Nombre de Usuario y Password (credenciales con las que inicia sesión, esto como medida de seguridad).
					</p>
					<br>
					<div class="form-row">
						<div class="form-group col-md-6">
							<b><label>Nombre de Usuario:</label></b>
							<input class="form-control" type="text" name="adminUser" pattern="[a-zA-Z0-9]{4,20}" maxlength="20" required>
						</div>
						<div class="form-group col-md-6">
							<b><label>Password:</label></b>
							<input class="form-control" type="password" name="adminPassword" pattern="[a-zA-Z0-9$@.-]{7,100}" maxlength="100" required>
						</div>
					</div>
					<br>
					<p class="has-text-centered">
						<button type="submit" class="btn btn-warning">Actualizar Usuario</button>
					</p>
				</form>
			<?php
			} else {
				include "./includes/alertError.php";
			}
			$check_usuario = null;
			?>
		</div>
	</div>
</div>