<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Comanda</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="max-w-3xl mx-auto mt-10">
        <div class="bg-white shadow-lg rounded-lg p-8">
            <div class="mb-8 flex items-center justify-between">
                <h2 class="text-3xl font-bold text-gray-800 flex items-center gap-2">
                    <span class="bg-blue-100 text-blue-700 font-semibold px-2 py-1 rounded text-lg">Editar Comanda</span>
                </h2>
                <span id="badgeComanda" class="hidden px-3 py-1 rounded-full text-white text-sm font-semibold ml-2"></span>
            </div>
            <div class="mb-4 flex flex-col md:flex-row gap-4 items-end">
                <div class="flex-1">
                    <label for="comandaId" class="block font-semibold text-gray-700 mb-1">ID de Comanda</label>
                    <input type="text" class="w-full border border-gray-300 rounded px-4 py-2 focus:ring-2 focus:ring-blue-400 outline-none" id="comandaId" placeholder="Ej: 123">
                </div>
                <button id="buscarComanda" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-5 py-2 rounded shadow transition">Buscar Comanda</button>
            </div>

            <div class="overflow-x-auto mt-6">
                <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow-sm" id="tablaProductos">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="py-2 px-4 text-left font-bold text-gray-700">Nombre</th>
                            <th class="py-2 px-4 text-left font-bold text-gray-700">Cantidad</th>
                            <th class="py-2 px-4 text-left font-bold text-gray-700">Precio Final</th>
                            <th class="py-2 px-4 text-left font-bold text-gray-700">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Productos serán cargados aquí -->
                    </tbody>
                </table>
            </div>

            <div class="mt-8 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <span class="text-xl font-semibold text-gray-700">Total actualizado:</span>
                    <span class="bg-green-100 text-green-700 px-4 py-2 rounded-full font-mono text-xl" id="totalActualizado">$0.00</span>
                </div>
                <button class="bg-green-600 hover:bg-green-700 text-white font-bold px-6 py-3 rounded-full shadow transition mt-4 md:mt-0" id="confirmarCambios">
                    Confirmar y regenerar PDF
                </button>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
      $(document).ready(function() {
          let productosActuales = [];

          function showBadge(txt, color) {
              $('#badgeComanda').removeClass('hidden bg-blue-600 bg-red-600 bg-green-600').text(txt).addClass(color).show();
          }
          function hideBadge() {
              $('#badgeComanda').hide();
          }

          function cargarProductos(comandaId) {
              $.getJSON('https://stagging.kallijaguar-inventory.com/api/getProductosComanda.php', {
                      comanda_id: comandaId
                  })
                  .done(function(res) {
                      if (res.success) {
                          productosActuales = res.productos;
                          let total = 0;
                          $('#tablaProductos tbody').empty();

                          if (res.productos.length === 0) {
                              $('#tablaProductos tbody').append(
                                  `<tr><td colspan="4" class="text-center text-gray-500 py-6">No hay productos en la comanda.</td></tr>`
                              );
                          }

                          res.productos.forEach(producto => {
                              const fila = `<tr data-movimiento-id="${producto.ID}" class="hover:bg-gray-50 transition">
                                <td class="py-2 px-4 flex items-center gap-2">
                                    <span class="inline-block bg-indigo-100 text-indigo-700 px-2 py-1 rounded text-xs font-semibold">${producto.Nombre}</span>
                                </td>
                                <td class="py-2 px-4 text-center font-mono">${producto.Cantidad}</td>
                                <td class="py-2 px-4 font-mono text-green-700">$${parseFloat(producto.PrecioFinal || 0).toFixed(2)}</td>
                                <td class="py-2 px-4 flex items-center gap-2">
                                    <button class="eliminar bg-red-500 hover:bg-red-700 text-white px-3 py-1 rounded-full text-xs font-bold shadow transition">Eliminar</button>
                                    <input type="number" min="1" max="${producto.Cantidad}" value="1" class="inputDevolver border border-gray-300 rounded px-2 w-16 mx-1 text-center outline-none focus:ring-2 focus:ring-indigo-400">
                                    <button class="devolver bg-yellow-400 hover:bg-yellow-500 text-white px-3 py-1 rounded-full text-xs font-bold shadow transition">Devolver</button>
                                </td>
                            </tr>`;
                              $('#tablaProductos tbody').append(fila);
                              total += parseFloat(producto.PrecioUnitario*1.16) * producto.Cantidad;
                          });

                          $('#totalActualizado').text('$' + total.toFixed(2));
                          showBadge('Comanda encontrada', 'bg-green-600');
                      } else {
                          Swal.fire('Error', res.error || 'Comanda no encontrada.', 'error');
                          $('#tablaProductos tbody').empty();
                          hideBadge();
                          $('#totalActualizado').text('$0.00');
                      }
                  })
                  .fail(function() {
                      Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error');
                      hideBadge();
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

              Swal.fire({
                  title: '¿Eliminar producto?',
                  text: "Esta acción es irreversible.",
                  icon: 'warning',
                  showCancelButton: true,
                  confirmButtonColor: '#e3342f',
                  cancelButtonColor: '#6c757d',
                  confirmButtonText: 'Sí, eliminar',
                  cancelButtonText: 'Cancelar'
              }).then((result) => {
                  if (result.isConfirmed) {
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
              Swal.fire({
                  title: '¿Devolver producto?',
                  text: `Se devolverán ${cantidad} unidades al inventario.`,
                  icon: 'question',
                  showCancelButton: true,
                  confirmButtonColor: '#f59e42',
                  cancelButtonColor: '#6c757d',
                  confirmButtonText: 'Sí, devolver',
                  cancelButtonText: 'Cancelar'
              }).then((result) => {
                  if (result.isConfirmed) {
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
                  }
              });
          });

          $('#confirmarCambios').click(function() {
              const comandaId = $('#comandaId').val();

              if (!comandaId) {
                  Swal.fire('Error', 'Ingrese un ID de comanda.', 'warning');
                  return;
              }

              Swal.fire({
                  title: '¿Confirmar cambios?',
                  text: 'Se regenerará el PDF de la comanda con los cambios realizados.',
                  icon: 'info',
                  showCancelButton: true,
                  confirmButtonText: 'Sí, confirmar',
                  cancelButtonText: 'Cancelar',
                  confirmButtonColor: '#059669'
              }).then((result) => {
                  if (result.isConfirmed) {
                      $.post('https://stagging.kallijaguar-inventory.com/api/regenerarComandaPDF.php', {
                          comanda_id: comandaId
                      }, function(res) {
                          if (res.status === 'success') {
                              Swal.fire('Comanda Actualizada', 'Se regeneró el PDF correctamente.', 'success');
                          } else {
                              Swal.fire('Error', res.message || 'Error desconocido', 'error');
                          }
                      }, 'json').fail(function() {
                          Swal.fire('Error', 'No se pudo regenerar el PDF.', 'error');
                      });
                  }
              });
          });
      });
    </script>
</body>
</html>