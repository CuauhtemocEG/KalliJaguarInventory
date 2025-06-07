document.getElementById("loginForm").addEventListener("submit", function (e) {
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
			window.location.href = "./index.php?page=home";
		} else {
			msgDiv.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
		}
	})
	.catch(error => {
		console.error("Error en el login:", error);
		msgDiv.innerHTML = '<div class="alert alert-danger">Error inesperado.</div>';
	});
});