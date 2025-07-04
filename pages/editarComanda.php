<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar Comanda</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body { background-color: #f9f9fb; font-family: 'Segoe UI', sans-serif; }
    .card { border-radius: 12px; box-shadow: 0 4px 8px rgba(0,0,0,0.06); }
    .btn-sm { padding: 0.3rem 0.6rem; font-size: 0.85rem; }
    .inputDevolver { max-width: 70px; display: inline-block; }
  </style>
</head>
<body>
<div class="container py-5">
  <div class="card p-4 mb-4">
    <h3 class="mb-3"><i class="bi bi-pencil-square"></i> Editar Comanda</h3>
    <div class="row g-2 align-items-end">
      <div class="col-md-6">
        <label for="comandaId" class="form-label">ID de Comanda</label>
        <input type="text" class="form-control" id="comandaId" placeholder="Ej: 12345">
      </div>
      <div class="col-md-3">
        <button id="buscarComanda" class="btn btn-primary w-100"><i class="bi bi-search"></i> Buscar</button>
      </div>
    </div>
  </div>

  <div class="card p-4 mb-4 d-none" id="seccionTabla">
    <h5 class="mb-3">Productos en la Comanda</h5>
    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle" id="tablaProductos">
        <thead class="table-light">
        <tr>
          <th>Nombre</th>
          <th>Cantidad</th>
          <th>Precio Final</th>
          <th>Acciones</th>
        </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
    <div class="text-end mt-3">
      <h5>Total actualizado: <span class="badge bg-dark fs-5" id="totalActualizado">$0.00</span></h5>
      <button class="btn btn-success mt-3" id="confirmarCambios"><i class="bi bi-file-earmark-arrow-down"></i> Confirmar y regenerar PDF</button>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function () {
  let productosActuales = [];

  function cargarProductos(comandaId) {
    $('#seccionTabla').removeClass('d-none');
    $('#tablaProductos tbody').html('<tr><td colspan="4" class="text-center text-muted">Cargando productos...</td></tr>');

    $.getJSON('https://www.kallijaguar-inventory.com/api/recreateComanda/getProductosComanda.php', {
      comanda_id: comandaId
    })
    .done(function (res) {
      if (res.success) {
        productosActuales = res.productos;
        let total = 0;
        $('#tablaProductos tbody').empty();

        res.productos.forEach(producto => {
          const fila = `<tr data-movimiento-id="${producto.ID}">
            <td>${producto.Nombre}</td>
            <td>${producto.Cantidad}</td>
            <td>$${parseFloat(producto.PrecioFinal || 0).toFixed(2)}</td>
            <td>
              <div class="d-flex flex-wrap gap-1 align-items-center">
                <button class="btn btn-danger btn-sm eliminar"><i class="bi bi-trash"></i></button>
                <input type="number" min="1" max="${producto.Cantidad}" value="1" class="form-control inputDevolver">
                <button class="btn btn-warning btn-sm devolver"><i class="bi bi-arrow-return-left"></i></button>
              </div>
            </td>
          </tr>`;
          $('#tablaProductos tbody').append(fila);
          total += parseFloat(producto.PrecioUnitario * 1.16) * producto.Cantidad;
        });

        $('#totalActualizado').text(`$${total.toFixed(2)}`);
      } else {
        Swal.fire('Error', res.error, 'error');
      }
    })
    .fail(function () {
      Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error');
    });
  }

  $('#buscarComanda').click(function () {
    const comandaId = $('#comandaId').val().trim();
    if (!comandaId) {
      Swal.fire('Atención', 'Ingrese un ID de comanda.', 'warning');
      return;
    }
    cargarProductos(comandaId);
  });

  $('#tablaProductos').on('click', '.eliminar', function () {
    const tr = $(this).closest('tr');
    const movimientoId = tr.data('movimiento-id');
    const comandaId = $('#comandaId').val();

    $.ajax({
      url: 'https://www.kallijaguar-inventory.com/api/recreateComanda/eliminarProductoComanda.php',
      method: 'POST',
      contentType: 'application/json',
      data: JSON.stringify({ movimiento_id: movimientoId }),
      dataType: 'json',
      success: function (res) {
        if (res.success) {
          Swal.fire('Eliminado', 'Producto eliminado.', 'success');
          cargarProductos(comandaId);
        } else {
          Swal.fire('Error', res.error, 'error');
        }
      },
      error: function () {
        Swal.fire('Error', 'No se pudo eliminar el producto.', 'error');
      }
    });
  });

  $('#tablaProductos').on('click', '.devolver', function () {
    const tr = $(this).closest('tr');
    const movimientoId = tr.data('movimiento-id');
    const cantidad = parseFloat(tr.find('.inputDevolver').val());
    const comandaId = $('#comandaId').val();

    if (!cantidad || cantidad <= 0) {
      Swal.fire('Atención', 'Ingrese una cantidad válida para devolver.', 'warning');
      return;
    }

    $.ajax({
      url: 'https://www.kallijaguar-inventory.com/api/recreateComanda/devolverProductoComanda.php',
      method: 'POST',
      contentType: 'application/json',
      data: JSON.stringify({ movimiento_id: movimientoId, cantidad }),
      dataType: 'json',
      success: function (res) {
        if (res.success) {
          Swal.fire('Devuelto', 'Cantidad devuelta correctamente.', 'success');
          cargarProductos(comandaId);
        } else {
          Swal.fire('Error', res.error, 'error');
        }
      },
      error: function () {
        Swal.fire('Error', 'No se pudo devolver el producto.', 'error');
      }
    });
  });

  $('#confirmarCambios').click(function () {
    const comandaId = $('#comandaId').val();
    if (!comandaId) {
      Swal.fire('Atención', 'Debe ingresar un ID de comanda.', 'warning');
      return;
    }

    $.post('https://www.kallijaguar-inventory.com/api/recreateComanda/regenerarComandaPDF.php', {
      comanda_id: comandaId
    }, function (res) {
      if (res.status === 'success') {
        Swal.fire('¡Listo!', 'PDF regenerado correctamente.', 'success');
      } else {
        Swal.fire('Error', res.message || 'Error al regenerar PDF.', 'error');
      }
    }, 'json').fail(function () {
      Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error');
    });
  });
});
</script>
</body>
</html>
