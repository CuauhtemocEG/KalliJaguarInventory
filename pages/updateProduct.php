<div class="container-fluid" style="padding-top:15px; padding-bottom:15px">
	<div class="card">
		<div class="card-header font-weight-bold">Actualizar Producto</div>
		<div class="card-body">
			<?php
			require_once "./controllers/mainController.php";

			$id = (isset($_GET['idProductUp'])) ? $_GET['idProductUp'] : 0;
			$id = limpiar_cadena($id);

			/*== Verificando producto ==*/
			$check_producto = conexion();
			$check_producto = $check_producto->query("SELECT * FROM Productos WHERE ProductoID='$id'");

			if ($check_producto->rowCount() > 0) {
				$datos = $check_producto->fetch();
			?>
				<div class="form-rest"></div>
				<h3 class="h3 has-text-centered"><?php echo $datos['Nombre']; ?></h3>
				<hr>
				<form action="./controllers/updateProductController.php" method="POST" class="FormularioAjax" autocomplete="off">

					<input type="hidden" name="productID" value="<?php echo $datos['ProductoID']; ?>" required>

					<div class="form-row">
						<div class="form-group col-md-6">
							<b><label>Código de barra (UPC):</label></b>
							<input class="input" type="text" name="productUPC" pattern="[a-zA-Z0-9- ]{1,70}" maxlength="70" required value="<?php echo $datos['UPC']; ?>">
						</div>

						<div class="form-group col-md-6">
							<b><label>Nombre del Producto:</label></b>
							<input class="input" type="text" name="productName" pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ().,$#\-\/ ]{1,70}" maxlength="70" required value="<?php echo $datos['Nombre']; ?>">
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-md-4">
							<b><label>Precio:</label></b>
							<input class="input" type="text" name="productPrecio" pattern="[0-9.]{1,25}" maxlength="25" required value="<?php echo $datos['PrecioUnitario']; ?>">
						</div>
						<div class="form-group col-md-4">
							<b><label>Stock:</label></b>
							<input class="input" type="text" name="productStock" required value="<?php echo $datos['Cantidad']; ?>">
						</div>
						<div class="form-group col-md-4">
							<b><label>Tipo de Inventario (Unitario, Pesable):</label></b>
							<select class="custom-select" id="inputInventory" name="productTypeInventory">
								<?php
								if ($datos['Tipo'] == "Pesable") {
									echo '
									<option value="' . $datos['Tipo'] . '" selected="" >' . $datos['Tipo'] . ' (Actual)</option>
									<option value="Unidad">Unidad</option>';
								} else {
									echo '
									<option value="' . $datos['Tipo'] . '" selected="">' . $datos['Tipo'] . ' (Actual)</option>
									<option value="Pesable">Pesable</option>';
								}
								?>
							</select>
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-md-5">
							<b><label>Categoría:</label></b>
							<select class="custom-select" id="inputCategory" name="productCategory">
								<?php
								$categorias = conexion();
								$categorias = $categorias->query("SELECT * FROM Categorias");
								if ($categorias->rowCount() > 0) {
									$categorias = $categorias->fetchAll();
									foreach ($categorias as $row) {
										if ($datos['CategoriaID'] == $row['CategoriaID']) {
											echo '<option value="' . $row['CategoriaID'] . '" selected="" >' . $row['Nombre'] . ' (Actual)</option>';
										} else {
											echo '<option value="' . $row['CategoriaID'] . '" >' . $row['Nombre'] . '</option>';
										}
									}
								}
								$categorias = null;
								?>
							</select>
						</div>
					</div>

					<p class="has-text-centered">
						<button type="submit" class="btn btn-warning">Actualizar Producto</button>
					</p>
				</form>
			<?php
			} else {
				include "./includes/alertError.php";
			}
			$check_producto = null;
			?>
		</div>
	</div>
</div>