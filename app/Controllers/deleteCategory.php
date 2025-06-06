<?php
/*== Almacenando datos ==*/
$idCategoryDelete = limpiar_cadena($_GET['idCategoryDel']);

/*== Verificando usuario ==*/
$check_categoria = conexion();
$check_categoria = $check_categoria->query("SELECT CategoriaID FROM Categorias WHERE CategoriaID='$idCategoryDelete'");

if ($check_categoria->rowCount() == 1) {

	$check_productos = conexion();
	$check_productos = $check_productos->query("SELECT CategoriaID FROM Productos WHERE CategoriaID='$idCategoryDelete' LIMIT 1");

	if ($check_productos->rowCount() <= 0) {

		$eliminar_categoria = conexion();
		$eliminar_categoria = $eliminar_categoria->prepare("DELETE FROM Categorias WHERE CategoriaID=:id");

		$eliminar_categoria->execute([":id" => $idCategoryDelete]);

		if ($eliminar_categoria->rowCount() == 1) {
			echo '
			<div class="alert alert-info alert-dismissible fade show" role="alert">
            	<button type="button" class="close" data-dismiss="alert" aria-label="Close">
                	<span aria-hidden="true">&times;</span>
         		</button>
            	<strong>¡Categoría Eliminada!</strong><br>
				Los datos de la Categoría se eliminaron con éxito.
        	</div>';
		} else {
			echo '
			<div class="alert alert-danger alert-dismissible fade show" role="alert">
            	<button type="button" class="close" data-dismiss="alert" aria-label="Close">
                	<span aria-hidden="true">&times;</span>
         		</button>
            	<strong>¡Ocurrio un error!</strong><br>
				No pudimos eliminar la Categoría, por favor intente nuevamente.
        	</div>';
		}
		$eliminar_categoria = null;
	} else {
		echo '
			<div class="alert alert-danger alert-dismissible fade show" role="alert">
            	<button type="button" class="close" data-dismiss="alert" aria-label="Close">
                	<span aria-hidden="true">&times;</span>
         		</button>
            	<strong>¡Ocurrio un error!</strong><br>
				No pudimos eliminar la Categoría ya que tiene productos asociados.
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
				La Categoría que intenta eliminar no existe en el Sistema.
        	</div>';
}
$check_categoria = null;
