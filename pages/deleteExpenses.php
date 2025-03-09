<?php
if (isset($_GET['expensesId'])) {
	echo '<script>confirm("Quieres enviar el formulario")</script>';
	$id = $_GET['expensesId'];

	$elminarExpense = conexion();
	$elminarExpense = $elminarExpense->prepare("DELETE FROM Gastos WHERE ID=:id");

	$elminarExpense->execute([":id" => $id]);

	if ($elminarExpense->rowCount() == 1) {
		echo "<script>window.setTimeout(function() { window.location = 'index.php?page=expensesWeekly' }, 100);</script>";
		exit();
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
