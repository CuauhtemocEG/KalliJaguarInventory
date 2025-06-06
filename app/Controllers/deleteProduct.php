<?php
/*== Almacenando datos ==*/
$idProductDelete = limpiar_cadena($_GET['idProductDel']);

/*== Verificando producto ==*/
$check_producto = conexion();
$check_producto = $check_producto->query("SELECT * FROM Productos WHERE ProductoID='$idProductDelete'");

if ($check_producto->rowCount() == 1) {

	$datos = $check_producto->fetch();

	$eliminar_producto = conexion();
	$eliminar_producto = $eliminar_producto->prepare("DELETE FROM Productos WHERE ProductoID=:id");

	$eliminar_producto->execute([":id" => $idProductDelete]);

	if ($eliminar_producto->rowCount() == 1) {

		if (is_file("./img/producto/" . $datos['image'])) {
			chmod("./img/producto/" . $datos['image'], 0777);
			unlink("./img/producto/" . $datos['image']);
		}

		echo '
			<div class="alert alert-info alert-dismissible fade show" role="alert">
            	<button type="button" class="close" data-dismiss="alert" aria-label="Close">
                	<span aria-hidden="true">&times;</span>
         		</button>
            	<strong>¡Producto Eliminado!</strong><br>
				Los datos del Producto se eliminaron con éxito.
        	</div>';
	} else {
		echo '
			<div class="alert alert-info alert-dismissible fade show" role="alert">
            	<button type="button" class="close" data-dismiss="alert" aria-label="Close">
                	<span aria-hidden="true">&times;</span>
         		</button>
            	<strong>¡Producto Eliminado!</strong><br>
				No pudimos eliminar el Producto, por favor intente nuevamente.
        	</div>';
	}
	$eliminar_producto = null;
} else {
	echo '
		<div class="alert alert-danger alert-dismissible fade show" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
         	</button>
            <strong>¡Ocurrio un error!</strong><br>
			El Producto que intenta eliminar no existe en el Sistema.
        </div>';
}
$check_producto = null;
