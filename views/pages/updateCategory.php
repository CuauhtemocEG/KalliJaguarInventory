<div class="container-fluid" style="padding-top:15px; padding-bottom:15px">
	<div class="card">
		<div class="card-header font-weight-bold">Actualizar Categoría</div>
		<div class="card-body">
			<?php
			//include "./includes/btn_back.php";

			require_once BASE_PATH . '/src/Helpers/legacy.php';

			$id = (isset($_GET['idCategoryUp'])) ? $_GET['idCategoryUp'] : 0;
			$id = limpiar_cadena($id);

			/*== Verificando categoria ==*/
			$check_categoria = conexion();
			$check_categoria = consulta($check_categoria, "SELECT * FROM Categorias WHERE CategoriaID=:p1", [':p1' => $id]);

			if ($check_categoria->rowCount() > 0) {
				$datos = $check_categoria->fetch();
			?>
				<div class="form-rest"></div>
				<form action="./controllers/updateCategoryController.php" method="POST" class="FormularioAjax" autocomplete="off">
					<input type="hidden" name="idCategory" value="<?php echo $datos['CategoriaID']; ?>" required>
					<div class="form-group">
						<b><label>Nombre de la Categoría:</label></b>
						<input class="form-control" type="text" name="categoryName"  maxlength="50" required value="<?php echo $datos['Nombre']; ?>">
					</div>
					<div class="has-text-centered">
						<button type="submit" class="btn btn-warning">Actualizar Categoría</button>
					</div>
				</form>
			<?php
			} else {
				include BASE_PATH . '/views/partials/alertError.php';
			}
			$check_categoria = null;
			?>
		</div>
	</div>
</div>