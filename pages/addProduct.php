<div class="container-fluid" style="padding-top:15px; padding-bottom:15px">
	<div class="card">
		<div class="card-header font-weight-bold">Agregar Productos</div>
		<div class="card-body">
			<?php
			require_once "./controllers/mainController.php";
			?>
			<div class="form-rest"></div>
			<form class="FormularioAjax" method="POST" autocomplete="off" enctype="multipart/form-data">

				<div class="form-row">
					<div class="form-group col-md-6">
						<b><label>Código de barra (UPC):</label></b>
						<input class="form-control" type="text" name="productUPC" maxlength="13">
					</div>

					<div class="form-group col-md-6">
						<b><label>Nombre del Producto:</label></b>
						<input class="form-control" type="text" name="productName" maxlength="70">
					</div>
				</div>

				<div class="form-row">
					<div class="form-group col-md-4">
						<b><label>Precio:</label></b>
						<input class="form-control" type="text" name="productPrecio" maxlength="25">
					</div>
					<div class="form-group col-md-4">
						<b><label>Stock:</label></b>
						<input class="form-control" type="text" name="productStock">
					</div>
					<div class="form-group col-md-4">
						<b><label>Tipo de Inventario (Unitario, Pesable):</label></b>
						<select class="custom-select" id="inputInventory" name="productTypeInventory">
							<option selected>Seleccione el Inventario</option>
							<option value="Unidad">Unidad</option>
							<option value="Pesable">Pesable</option>
						</select>
					</div>
				</div>

				<div class="form-row">
					<div class="form-group col-md-4">
						<b><label>Categoría:</label></b>
						<select class="custom-select" id="inputCategory" name="productCategory">
							<option selected>Seleccione una categoría</option>
							<?php
							$categorias = conexion();
							$categorias = $categorias->query("SELECT * FROM Categorias");
							if ($categorias->rowCount() > 0) {
								$categorias = $categorias->fetchAll();
								foreach ($categorias as $row) {
									echo '<option value="' . $row['CategoriaID'] . '" >' . $row['Nombre'] . '</option>';
								}
							}
							$categorias = null;
							?>
						</select>
					</div>
					<div class="form-group col-md-4">
						<b><label>Foto o imagen del producto:</label></b><br>
						<div class="file is-small has-name">
							<label class="file-label">
								<input class="form-control" type="file" name="productImage" accept=".jpg, .png, .jpeg">
								<span class="file-cta">
									<span class="file-label">Imagen</span>
								</span>
								<span class="file-name">JPG, JPEG, PNG. (MAX 3MB)</span>
							</label>
						</div>
					</div>
				</div>
				<p class="has-text-centered">
					<button type="submit" class="btn btn-warning">Guardar Producto</button>
				</p>
			</form>
		</div>
	</div>
</div>
<script>
document.querySelector('.FormularioAjax').addEventListener('submit', async function(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const responseContainer = document.querySelector('.form-rest');
    responseContainer.innerHTML = "Procesando...";

    try {
        const res = await fetch('../api/products/createProducts.php', {
            method: 'POST',
            body: formData
        });

        const data = await res.json();

        responseContainer.innerHTML = `
            <div class="alert alert-${data.status === 'success' ? 'success' : 'danger'} alert-dismissible fade show" role="alert">
                ${data.message}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        `;

        if (data.status === 'success') form.reset();
    } catch (error) {
        responseContainer.innerHTML = `
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                Error de conexión con el servidor.
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        `;
    }
});
</script>
