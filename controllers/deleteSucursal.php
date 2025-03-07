<?php
/*== Almacenando datos ==*/
$idSucursalDelete = limpiar_cadena($_GET['idSucursalDel']);

/*== Verificando usuario ==*/
$checkSucursal = conexion();
$checkSucursal = $checkSucursal->query("SELECT SucursalID FROM Sucursales WHERE SucursalID='$idSucursalDelete'");

if ($checkSucursal->rowCount() == 1) {

	$checkMovimientos = conexion();
	$checkMovimientos = $checkMovimientos->query("SELECT SucursalID FROM MovimientosInventario WHERE SucursalID='$idSucursalDelete' LIMIT 1");

	if ($checkMovimientos->rowCount() <= 0) {

		$eliminarSucursal = conexion();
		$eliminarSucursal = $eliminarSucursal->prepare("DELETE FROM Sucursales WHERE SucursalID=:id");

		$eliminarSucursal->execute([":id" => $idSucursalDelete]);

		if ($eliminarSucursal->rowCount() == 1) {
			echo '
			<div class="alert alert-info alert-dismissible fade show" role="alert">
            	<button type="button" class="close" data-dismiss="alert" aria-label="Close">
                	<span aria-hidden="true">&times;</span>
         		</button>
            	<strong>¡Sucursal Eliminada!</strong><br>
				Los datos de la Sucursal se eliminaron con éxito.
        	</div>';
		} else {
			echo '
			<div class="alert alert-danger alert-dismissible fade show" role="alert">
            	<button type="button" class="close" data-dismiss="alert" aria-label="Close">
                	<span aria-hidden="true">&times;</span>
         		</button>
            	<strong>¡Ocurrio un error!</strong><br>
				No pudimos eliminar la Sucursal, por favor intente nuevamente.
        	</div>';
		}
		$eliminarSucursal = null;
	} else {
		echo '
			<div class="alert alert-danger alert-dismissible fade show" role="alert">
            	<button type="button" class="close" data-dismiss="alert" aria-label="Close">
                	<span aria-hidden="true">&times;</span>
         		</button>
            	<strong>¡Ocurrio un error!</strong><br>
				No podemos eliminar la Sucursal ya que tiene registros de movimientos asociados.
        	</div>';
	}
	$checkMovimientos = null;
} else {
	echo '
		<div class="alert alert-danger alert-dismissible fade show" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <strong>¡Ocurrio un error!</strong><br>
            La Sucursal que intenta eliminar no existe en el Sistema.
        </div>';
}
$checkSucursal = null;
