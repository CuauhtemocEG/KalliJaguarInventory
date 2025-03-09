<?php
require_once "../controllers/mainController.php";

$idGastoDelete = limpiar_cadena($_GET['expensesId']);

$checkGastoId = conexion();
$checkGastoId = $checkGastoId->query("SELECT ID FROM Gastos WHERE ID='$idGastoDelete'");

if ($checkGastoId->rowCount() == 1) {

	$elminarExpense = conexion();
	$elminarExpense = $elminarExpense->prepare("DELETE FROM Gastos WHERE ID=:id");

	$elminarExpense->execute([":id" => $idGastoDelete]);

	if ($elminarExpense->rowCount() == 1) {
		echo '
			<div class="alert alert-info alert-dismissible fade show" role="alert">
            	<button type="button" class="close" data-dismiss="alert" aria-label="Close">
                	<span aria-hidden="true">&times;</span>
         		</button>
            	<strong>Gasto Eliminado!</strong><br>
				Los datos del Gasto se eliminaron con éxito.
        	</div>';
	} else {
		echo '
			<div class="alert alert-danger alert-dismissible fade show" role="alert">
            	<button type="button" class="close" data-dismiss="alert" aria-label="Close">
                	<span aria-hidden="true">&times;</span>
         		</button>
            	<strong>¡Ocurrio un error!</strong><br>
				No pudimos eliminar el gasto, por favor intente nuevamente.
        	</div>';
	}
	$elminarExpense = null;
}

