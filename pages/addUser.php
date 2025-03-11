<div class="container-fluid" style="padding-top:15px; padding-bottom:15px">
	<div class="card">
		<div class="card-header font-weight-bold">Agregar Usuario</div>
		<div class="card-body">
			<div class="form-rest"></div>
			<form action="./controllers/saveUser.php" method="POST" class="FormularioAjax" autocomplete="off">

				<div class="form-row">
					<div class="form-group col-md-6">
						<b><label>Nombre:</label></b>
						<input class="form-control" type="text" name="userName" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,40}" maxlength="40" required>
					</div>

					<div class="form-group col-md-6">
						<b><label>Rol del Usuario:</label></b>
						<select class="custom-select" id="userRol" name="userRol" required>
							<option selected>Seleccione el Rol</option>
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
						<input class="form-control" type="text" name="user" pattern="[a-zA-Z0-9]{4,20}" maxlength="20" required>
					</div>
					<div class="form-group col-md-6">
						<b><label>Email:</label></b>
						<input class="form-control" type="email" name="userEmail" maxlength="70">
					</div>
				</div>

				<div class="form-row">
					<div class="form-group col-md-6">
						<b><label>Password:</label></b>
						<input class="form-control" type="password" name="userPassword" pattern="[a-zA-Z0-9$@.-]{7,100}" maxlength="100" required>
					</div>
					<div class="form-group col-md-6">
						<b><label>Repetir Password:</label></b>
						<input class="form-control" type="password" name="userPassword2" pattern="[a-zA-Z0-9$@.-]{7,100}" maxlength="100" required>
					</div>
				</div>
				<hr>
				<p class="has-text-centered">
					<button type="submit" class="btn btn-warning">Guardar Usuario</button>
				</p>
			</form>
		</div>
	</div>
</div>