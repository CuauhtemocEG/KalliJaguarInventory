<div class="container-fluid" style="padding-top:15px; padding-bottom:15px">
	<div class="card">
		<div class="card-header font-weight-bold">Modificar Sucursal</div>
		<div class="card-body">
			<?php
			//include "./includes/btn_back.php";

			require_once "./controllers/mainController.php";

			$id = (isset($_GET['idSucursalUp'])) ? $_GET['idSucursalUp'] : 0;
			$id = limpiar_cadena($id);

			/*== Verificando Sucursal ==*/
			$checkSucursal = conexion();
			$checkSucursal = $checkSucursal->query("SELECT * FROM Sucursales WHERE SucursalID='$id'");

			if ($checkSucursal->rowCount() > 0) {
				$datos = $checkSucursal->fetch();
			?>
				<div class="form-rest"></div>

				<form action="./controllers/updateSucursalController.php" method="POST" class="FormularioAjax" autocomplete="off">

					<input type="hidden" name="sucursalId" value="<?php echo $datos['SucursalID']; ?>" required>

					<div class="form-row">
						<div class="form-group col-md-6">
							<label class="font-weight-bold">Nombre de la Sucursal:</label>
							<input class="form-control" type="text" name="nombreSucursal" pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ ]{4,50}" maxlength="50" required value="<?php echo $datos['nombre']; ?>">
						</div>
						<div class="form-group col-md-6">
							<label class="font-weight-bold">Dirección:</label>
							<input class="form-control" type="text" name="addressSucursal" pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ ]{5,150}" maxlength="150" value="<?php echo $datos['direccion']; ?>">
						</div>
					</div>
					<p class="has-text-centered">
						<button type="submit" class="btn btn-warning">Modificar datos</button>
					</p>
				</form>
			<?php
			} else {
				//include "./inc/error_alert.php";
				echo "error";
			}
			$checkSucursal = null;
			?>
		</div>
	</div>
</div>