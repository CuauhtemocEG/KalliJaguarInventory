<?php
$searchModule = limpiar_cadena($_POST['searchModule']);

$modules = ["user", "categoria", "producto"];

if (in_array($searchModule, $modules)) {

	$moduleURL = [
		"user" => "searchUser",
		"categoria" => "searchCategory",
		"producto" => "searchProduct"
	];

	$moduleURL = $moduleURL[$searchModule];

	$searchModule = "busqueda_" . $searchModule;

	# Iniciar busqueda #
	if (isset($_POST['txt_buscador'])) {

		$txt = limpiar_cadena($_POST['txt_buscador']);

		if ($txt == "") {
			echo '
					<div class="alert alert-danger" role="alert">
						<button type="button" class="close" data-dismiss="alert" aria-label="Close">
    						<span aria-hidden="true">&times;</span>
 						</button>
						<strong>¡Ocurrió un problema!</strong>
  						<p>Introduce algún término a buscar.</p>
					</div>';
		} else {
			if (verificar_datos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ ]{1,30}", $txt)) {
				echo '
						<div class="alert alert-danger" role="alert">
							<button type="button" class="close" data-dismiss="alert" aria-label="Close">
    							<span aria-hidden="true">&times;</span>
 							</button>
							<strong>¡Ocurrió un problema!</strong>
  							<p>El término que estas buscando no coincide con el formato solicitado.</p>
						</div>';
			} else {
				$_SESSION[$searchModule] = $txt;
				//header("Location:index.php?vista=$moduleURL", true, 303);
				echo "<script>window.setTimeout(function() { window.location = 'index.php?page=$moduleURL' }, 100);</script>";
				exit();
			}
		}
	}


	# Eliminar busqueda #
	if (isset($_POST['eliminar_buscador'])) {
		unset($_SESSION[$searchModule]);
		//header("Location:index.php?vista=$moduleURL", true, 303);
		echo "<script>window.setTimeout(function() { window.location = 'index.php?page=$moduleURL' }, 100);</script>";
		exit();
	}
} else {
	echo '
            <div class="notification is-danger is-light">
                <strong>¡Ocurrio un error inesperado!</strong><br>
                No podemos procesar la peticion
            </div>
		<div class="alert alert-danger" role="alert">
			<button type="button" class="close" data-dismiss="alert" aria-label="Close">
    			<span aria-hidden="true">&times;</span>
 			</button>
			<strong>¡Ocurrió un problema!</strong>
  			<p>El término que estas buscando no coincide con el formato solicitado.</p>
		</div>
        ';
}
