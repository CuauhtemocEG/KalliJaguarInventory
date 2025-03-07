<?php

/*== Almacenando datos ==*/
$idUserDelete = limpiar_cadena($_GET['idUserDel']);

/*== Verificando usuario ==*/
$check_usuario = conexion();
$check_usuario = $check_usuario->query("SELECT UsuarioID FROM Usuarios WHERE UsuarioID='$idUserDelete'");

if ($check_usuario->rowCount() == 1) {

	$check_productos = conexion();
	$check_productos = $check_productos->query("SELECT UsuarioID FROM Productos WHERE UsuarioID='$idUserDelete' LIMIT 1");

	if ($check_productos->rowCount() <= 0) {

		$eliminar_usuario = conexion();
		$eliminar_usuario = $eliminar_usuario->prepare("DELETE FROM Usuarios WHERE UsuarioID=:id");

		$eliminar_usuario->execute([":id" => $idUserDelete]);

		if ($eliminar_usuario->rowCount() == 1) {
			echo '
			<div class="alert alert-info alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong>¡Usuario Eliminado!</strong><br>
                Los datos del Usuario fueron eliminados con éxito.
            </div>';
		} else {
			echo '
			<div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong>¡Ocurrio un error!</strong><br>
                No se pudo eliminar el Usuario, por favor intente nuevamente.
            </div>';
		}
		$eliminar_usuario = null;
	} else {
		echo '
			<div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong>¡Ocurrio un error!</strong><br>
                No se pudo eliminar el Usuario ya que tiene productos registrados por el, contacta al administrador para solucionar el problema.
            </div>';
	}
	$check_productos = null;
} else {
	echo '
		<div class="alert alert-danger alert-dismissible fade show" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <strong>¡Ocurrio un error!</strong><br>
            No se pudo eliminar el Usuario, no existe en el sistema.
        </div>
        ';
}
$check_usuario = null;
