<div class="container-fluid" style="padding-top:15px; padding-bottom:15px">
	<div class="card">
		<div class="card-header font-weight-bold">Actualizar Imagen de Producto</div>
		<div class="card-body">
			<?php
			//include "./includes/btn_back.php";

			require_once BASE_PATH . '/src/Helpers/legacy.php';

			$id = (isset($_GET['idProductUp'])) ? $_GET['idProductUp'] : 0;

			/*== Verificando producto ==*/
			$check_producto = conexion();
			$check_producto = consulta($check_producto, "SELECT * FROM Productos WHERE ProductoID=:p1", [':p1' => $id]);

			if ($check_producto->rowCount() > 0) {
				$datos = $check_producto->fetch();
			?>

				<div class="form-rest"></div>

				<div class="columns">
					<div class="column is-two-fifths">
						<?php if (is_file(config('paths.products') . '/' . basename($datos['image']))) { ?>
							<figure class="image mb-2">
								<img src="<?= e(product_image_url($datos['image'])) ?>">
							</figure>
							<form class="FormularioAjax" action="./controllers/deleteProductImage.php" method="POST" autocomplete="off">

								<input type="hidden" name="idImageDel" value="<?php echo $datos['ProductoID']; ?>">

								<p class="has-text-centered">
									<button type="submit" class="btn btn-danger">Eliminar imagen</button>
								</p>
							</form>
						<?php } else { ?>
							<figure class="image mb-2">
								<img src="<?= asset('img/producto.png') ?>">
							</figure>
						<?php } ?>
					</div>
					<div class="column">
						<form class="has-text-centered FormularioAjax" action="./controllers/updateProductImageController.php" method="POST" enctype="multipart/form-data" autocomplete="off">

							<h4 class="title is-4"><?php echo $datos['Nombre']; ?></h4>
							<hr>
							<b><label>Foto o imagen del producto:</label></b><br>

							<input type="hidden" name="idImageUp" value="<?php echo $datos['ProductoID']; ?>">

							<div class="file has-name is-horizontal is-justify-content-center">
								<label class="file-label">
									<input class="form-control" type="file" name="imageProduct" accept=".jpg, .png, .jpeg">
									<span class="file-cta">
										<span class="file-label">Imagen</span>
									</span>
									<span class="file-name">JPG, JPEG, PNG. (MAX 3MB)</span>
								</label>
							</div>
							<div class="has-text-centered mt-3">
								<button type="submit" class="btn btn-warning">Actualizar Imagen</button>
							</div>
						</form>
					</div>
				</div>
			<?php
			} else {
				include BASE_PATH . '/views/partials/alertError.php';
			}
			$check_producto = null;
			?>
		</div>
	</div>
</div>