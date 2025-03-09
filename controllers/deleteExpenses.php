<?php
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $elminarExpense = conexion();
    $elminarExpense = $elminarExpense->prepare("DELETE FROM Sucursales WHERE ID=:id");

    $elminarExpense->execute([":id" => $id]);

    if ($elminarExpense->rowCount() == 1) {
        echo '
			<div class="alert alert-info alert-dismissible fade show" role="alert">
            	<button type="button" class="close" data-dismiss="alert" aria-label="Close">
                	<span aria-hidden="true">&times;</span>
         		</button>
            	<strong>Gasto Eliminada!</strong><br>
				Los datos del gasto se eliminaron con éxito.
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
