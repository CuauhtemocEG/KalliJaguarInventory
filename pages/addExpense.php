<div class="container-fluid" style="padding-top:15px; padding-bottom:15px">
    <div class="card">
        <div class="card-header font-weight-bold">Agregar Gasto</div>
        <div class="card-body">
            <div class="form-rest"></div>
            <form action="./controllers/saveExpense.php" method="POST">
                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <input type="text" class="form-control" id="descripcion" name="descripcion" required>
                </div>
                <div class="mb-3">
                    <label for="monto" class="form-label">Monto</label>
                    <input type="number" class="form-control" id="monto" name="monto" required>
                </div>
                <div class="mb-3">
                    <label for="fecha" class="form-label">Fecha</label>
                    <input type="date" class="form-control" id="fecha" name="fecha" required>
                </div>
                <button type="submit" class="btn btn-primary">Guardar Gasto</button>
            </form>
        </div>
    </div>
</div>