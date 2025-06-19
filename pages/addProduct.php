<div class="container-fluid" style="padding-top:15px; padding-bottom:15px">
	<div class="card">
		<div class="card-header font-weight-bold">Agregar Productos</div>
		<div class="card-body">
			<?php
			require_once "./controllers/mainController.php";
			?>
			<form class="FormularioAjaxAdd" method="POST" autocomplete="off" enctype="multipart/form-data">

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
					<div class="form-group col-md-4">
						<b><label>Tag del producto:</label></b>
						<select class="custom-select" id="inputTag" name="productTag">
							<option value="Costco">Costco</option>
							<option value="Mercado">Mercado</option>
							<option value="Walmart">Walmart</option>
							<option value="Central">Central de Abastos</option>
							<option value="Chilapa">Chilapa</option>
							<option value="Arero">Arero</option>
							<option value="Oficina">Oficina</option>
							<option value="Kike">Kike</option>
						</select>
					</div>
				</div>
				<p class="has-text-centered">
					<button type="submit" id="submitBtn" class="btn btn-warning">Guardar Producto</button>
				</p>
			</form>
		</div>
	</div>
</div>
<script>
document.querySelector('.FormularioAjaxAdd').addEventListener('submit', async function(e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);
    const submitBtn = document.getElementById('submitBtn');

    const requiredFields = ['productUPC', 'productName', 'productPrecio', 'productStock', 'productTag'];
    for (const fieldName of requiredFields) {
        const field = form.querySelector(`[name="${fieldName}"]`);
        if (!field.value.trim()) {
            Swal.fire({
                icon: 'warning',
                title: 'Campo requerido',
                text: `Por favor completa el campo: ${fieldName}`
            });
            return;
        }
    }

    submitBtn.disabled = true;
    submitBtn.textContent = "Guardando...";

    Swal.fire({
        title: 'Procesando...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    try {
        const res = await fetch('../api/products/createProducts.php', {
            method: 'POST',
            body: formData
        });

        const data = await res.json();
        Swal.close();

        if (data.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: data.message,
                timer: 2000,
                showConfirmButton: false
            });
            form.reset();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message
            });
        }
    } catch (error) {
        console.error(error);
        Swal.close();
        Swal.fire({
            icon: 'error',
            title: 'Error de red',
            text: 'No se pudo conectar con el servidor.'
        });
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = "Guardar producto";
    }
});
</script>