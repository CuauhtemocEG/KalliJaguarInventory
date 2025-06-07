<div class="container">
	<div class="row">
		<div class="col-md-6 offset-md-3">
			<h2 class="text-center h2 text-dark mt-5">Kalli Jaguar Inventory</h2>
			<div class="card my-3">
				<form id="loginForm" class="card-body cardbody-color p-lg-5" autocomplete="off">
					<div class="text-center">
						<img src="./img/Login.jpg" class="img-fluid img-thumbnail my-4" width="130px" alt="profile">
					</div>

					<div class="mb-3">
						<label class="label font-weight-bold">Usuario:</label>
						<input class="form-control" type="text" name="login_usuario" id="login_usuario" required>
					</div>

					<div class="mb-4">
						<label class="label font-weight-bold">Password:</label>
						<input class="form-control" type="password" name="login_clave" id="login_clave" required>
					</div>

					<div id="loginMessage" class="mb-3"></div>

					<div class="text-center">
						<button type="submit" class="btn btn-warning w-100 font-weight-bold">Iniciar sesión</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>

<script src="../js/login.js"></script>