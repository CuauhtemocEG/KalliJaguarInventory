<div class="container-fluid" style="padding-top:15px; padding-bottom:15px">
  <div class="card">
    <div class="card-header font-weight-bold">Agregar Categoría</div>
    <div class="card-body">
      <div class="form-rest"></div>
      <form action="./controllers/saveCategory.php" method="POST" autocomplete="off" class="FormularioAjax">
        <div class="form-group">
          <b><label>Nombre de la Categoría:</label></b>
          <input class="form-control" type="text" name="nameCategory" maxlength="50" required aria-describedby="categoryHelp">
          <small id="categoryHelp" class="form-text text-muted">Ingresa el nombre de la categoría Ej. Secos, Desechables, etc.</small>
        </div>
        <div class="has-text-centered">
          <button type="submit" class="btn btn-warning">Guardar Categoría</button>
        </div>
      </form>
    </div>
  </div>
</div>