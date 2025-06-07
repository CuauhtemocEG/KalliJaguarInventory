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
						<input class="form-control" type="text" name="login_usuario" id="login_usuario">
					</div>

					<div class="mb-4">
						<label class="label font-weight-bold">Password:</label>
						<input class="form-control" type="password" name="login_clave" id="login_clave">
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

<script>
	document.getElementById("loginForm").addEventListener("submit", function(e) {
		e.preventDefault();

		const usuario = document.getElementById("login_usuario").value.trim();
		const clave = document.getElementById("login_clave").value.trim();
		const msgDiv = document.getElementById("loginMessage");

		msgDiv.innerHTML = "";

		if (usuario === "" || clave === "") {
			msgDiv.innerHTML = '<div class="alert alert-danger">Completa todos los campos.</div>';
			return;
		}

		const formData = new FormData();
		formData.append("login_usuario", usuario);
		formData.append("login_clave", clave);

		fetch("https://stagging.kallijaguar-inventory.com/api/loginHandler.php", {
				method: "POST",
				body: formData
			})
			.then(response => response.json())
			.then(data => {
				if (data.success) {
					window.location.href = "https://stagging.kallijaguar-inventory.com/index.php?page=home";
				} else {
					msgDiv.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
				}
			})
			.catch(error => {
				console.error("Error en el login:", error);
				msgDiv.innerHTML = '<div class="alert alert-danger">Error inesperado.</div>';
			});
	});
</script>