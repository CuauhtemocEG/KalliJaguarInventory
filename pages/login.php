<div class="container">
	<div class="row">
		<div class="col-md-6 offset-md-3">
			<h2 class="text-center h2 text-dark mt-5">Kalli Jaguar Inventory</h2>
			<div class="card my-3">

				<form class="card-body cardbody-color p-lg-5" action="" method="POST" autocomplete="off">
					<div class="text-center">
						<img src="./img/Login.jpg" class="img-fluid img-thumbnail my-4" width="130px" alt="profile">
					</div>

					<div class="mb-3">
						<label class="label font-weight-bold">Usuario:</label>
						<input class="form-control" type="text" name="login_usuario" pattern="[a-zA-Z0-9]{4,20}" maxlength="20" required>
					</div>

					<div class="mb-4">
						<label class="label font-weight-bold">Password:</label>
						<input class="form-control" type="password" name="login_clave" pattern="[a-zA-Z0-9$@.-]{7,100}" maxlength="100" required>
					</div>

					<div class="text-center">
						<button type="submit" class="btn btn-warning mb-3 w-100 font-weight-bold">Iniciar sesión</button>
					</div>

					<?php
						if ($_SERVER['REQUEST_METHOD'] === 'POST') {
							require_once "./controllers/mainController.php";
							require_once "./controllers/iniciar_sesion.php";
						}
					?>
				</form>
			</div>
		</div>
	</div>
</div>