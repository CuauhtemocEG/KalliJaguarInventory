  <div class="container p-4">
      <h2>Editar Comanda</h2>
      <div class="mb-3">
          <label for="comandaId" class="form-label">ID de Comanda</label>
          <input type="text" class="form-control" id="comandaId">
          <button id="buscarComanda" class="btn btn-primary mt-2">Buscar Comanda</button>
      </div>

      <table class="table table-bordered" id="tablaProductos">
          <thead>
              <tr>
                  <th>Nombre</th>
                  <th>Cantidad</th>
                  <th>Precio Final</th>
                  <th>Acciones</th>
              </tr>
          </thead>
          <tbody></tbody>
      </table>

      <div class="mt-3">
          <h5>Total actualizado: $<span id="totalActualizado">0.00</span></h5>
          <button class="btn btn-success mt-2" id="confirmarCambios">Confirmar y regenerar PDF</button>
      </div>
  </div>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
      $(document).ready(function() {
          let productosActuales = [];

          function cargarProductos(comandaId) {
              $.getJSON('https://stagging.kallijaguar-inventory.com/api/getProductosComanda.php', {
                      comanda_id: comandaId
                  })
                  .done(function(res) {
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
                                <button class="btn btn-danger btn-sm eliminar">Eliminar</button>
                                <input type="number" min="1" max="${producto.Cantidad}" value="1" class="form-control d-inline w-25 ms-2 inputDevolver">
                                <button class="btn btn-warning btn-sm devolver ms-1">Devolver</button>
                            </td>
                        </tr>`;
                              $('#tablaProductos tbody').append(fila);
                              total += parseFloat((producto.PrecioUnitario*0.16)+producto.PrecioUnitario) * producto.Cantidad;
                          });

                          $('#totalActualizado').text(total.toFixed(2));
                      } else {
                          Swal.fire('Error', res.error, 'error');
                      }
                  })
                  .fail(function() {
                      Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error');
                  });
          }

          $('#buscarComanda').click(function() {
              const comandaId = $('#comandaId').val();
              if (comandaId.trim() === '') {
                  Swal.fire('Error', 'Ingrese un ID de comanda.', 'warning');
                  return;
              }
              cargarProductos(comandaId);
          });

          $('#tablaProductos').on('click', '.eliminar', function() {
              const tr = $(this).closest('tr');
              const movimientoId = tr.data('movimiento-id');
              const comandaId = $('#comandaId').val();

              $.ajax({
                  url: 'https://stagging.kallijaguar-inventory.com/api/eliminarProductoComanda.php',
                  method: 'POST',
                  contentType: 'application/json',
                  data: JSON.stringify({
                      movimiento_id: movimientoId
                  }),
                  dataType: 'json',
                  success: function(res) {
                      if (res.success) {
                          Swal.fire('Eliminado', 'Producto eliminado de la comanda.', 'success');
                          cargarProductos(comandaId);
                      } else {
                          Swal.fire('Error', res.error, 'error');
                      }
                  },
                  error: function() {
                      Swal.fire('Error', 'Error al eliminar el producto.', 'error');
                  }
              });
          });

          $('#tablaProductos').on('click', '.devolver', function() {
              const tr = $(this).closest('tr');
              const movimientoId = tr.data('movimiento-id');
              const cantidad = parseFloat(tr.find('.inputDevolver').val());
              const comandaId = $('#comandaId').val();

              if (!cantidad || cantidad <= 0) {
                  Swal.fire('Error', 'Ingrese una cantidad válida para devolver.', 'warning');
                  return;
              }

              $.ajax({
                  url: 'https://stagging.kallijaguar-inventory.com/api/devolverProductoComanda.php',
                  method: 'POST',
                  contentType: 'application/json',
                  data: JSON.stringify({
                      movimiento_id: movimientoId,
                      cantidad
                  }),
                  dataType: 'json',
                  success: function(res) {
                      if (res.success) {
                          Swal.fire('Devuelto', 'Cantidad devuelta al inventario.', 'success');
                          cargarProductos(comandaId);
                      } else {
                          Swal.fire('Error', res.error, 'error');
                      }
                  },
                  error: function() {
                      Swal.fire('Error', 'Error al devolver el producto.', 'error');
                  }
              });
          });

          $('#confirmarCambios').click(function() {
              const comandaId = $('#comandaId').val();

              if (!comandaId) {
                  Swal.fire('Error', 'Ingrese un ID de comanda.', 'warning');
                  return;
              }

              $.post('https://stagging.kallijaguar-inventory.com/api/regenerarComandaPDF.php', {
                  comanda_id: comandaId
              }, function(res) {
                  if (res.status === 'success') {
                      Swal.fire('Comanda Actualizada', 'Se regeneró el PDF correctamente.', 'success');
                      window.open(res.pdfUrl, '_blank');
                  } else {
                      Swal.fire('Error', res.message || 'Error desconocido', 'error');
                  }
              }, 'json').fail(function() {
                  Swal.fire('Error', 'No se pudo regenerar el PDF.', 'error');
              });
          });
      });
  </script>