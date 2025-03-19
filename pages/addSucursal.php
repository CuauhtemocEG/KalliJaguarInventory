<div class="container-fluid" style="padding-top:15px; padding-bottom:15px">
  <div class="card">
    <div class="card-header font-weight-bold">Agregar Sucursal</div>
    <div class="card-body">
      <div class="form-rest"></div>
      <form action="./controllers/saveSucursal.php" method="POST" autocomplete="off" class="FormularioAjax">
        <div class="form-group">
          <b><label>Nombre de la Sucursal:</label></b>
          <input class="form-control" type="text" name="sucursalName" maxlength="50" required aria-describedby="sucursalHelp">
          <small id="sucursalHelp" class="form-text text-muted">Ingresa Kalli en cualquier formato Ej. "Kalli Express - Finanzas".</small>
        </div>
        <div class="form-group">
          <b><label>Dirección:</label></b>
          <input class="form-control" type="text" name="sucursalAddress" maxlength="150">
        </div>
        <br>
        <div class="text-center">
          <button type="submit" class="btn btn-warning">Guardar Sucursal</button>
        </div>
      </form>
    </div>
  </div>
</div>