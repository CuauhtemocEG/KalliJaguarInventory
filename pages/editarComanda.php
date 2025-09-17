    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            DEFAULT: '#2563eb',
                            dark: '#1d4ed8'
                        },
                        sidebar: {
                            dark: '#1a1a1a',
                            darker: '#0d0d0d'
                        },
                        accent: {
                            yellow: '#fbbf24',
                            'yellow-dark': '#f59e0b'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .animate-fadeIn {
            animation: fadeIn 0.5s ease-out;
        }

        .animate-slideIn {
            animation: slideIn 0.3s ease-out;
        }
    </style>

    <body class="bg-gradient-to-br from-gray-50 to-blue-50 dark:from-gray-900 dark:to-gray-800 min-h-screen">
        <div class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center">
                            <div class="w-12 h-12 bg-gradient-to-r from-blue-600 to-blue-700 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-edit text-white text-xl"></i>
                            </div>
                            Editar Comanda
                        </h1>
                        <p class="mt-1 text-gray-600 dark:text-gray-400">Modifica, elimina o devuelve productos de comandas existentes</p>
                    </div>
                    <div id="statusBadge" class="hidden">
                        <span id="badgeComanda" class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold">
                            <i class="fas fa-circle mr-2 text-xs"></i>
                            <span id="badgeText"></span>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 mb-8">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white flex items-center">
                        <i class="fas fa-search text-blue-600 mr-3"></i>
                        Buscar Comanda
                    </h2>
                    <p class="mt-1 text-gray-600 dark:text-gray-400">Ingresa el ID de la comanda que deseas modificar</p>
                </div>
                <div class="p-6">
                    <div class="flex flex-col md:flex-row gap-4 items-end">
                        <div class="flex-1 w-full">
                            <label for="comandaId" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                <i class="fas fa-barcode mr-2"></i>ID de Comanda
                            </label>
                            <input type="text"
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                                id="comandaId"
                                placeholder="Ejemplo: COM-20240315-001-123">
                        </div>
                        <button id="buscarComanda"
                            class="w-full md:w-auto inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold rounded-lg shadow-lg transform transition-all duration-200 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-search mr-2"></i>
                            Buscar Comanda
                        </button>
                    </div>
                </div>
            </div>

            <div id="productosContainer" class="hidden animate-fadeIn">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 mb-8">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-xl font-semibold text-gray-900 dark:text-white flex items-center">
                                    <i class="fas fa-list text-green-600 mr-3"></i>
                                    Productos de la Comanda
                                </h2>
                                <p class="mt-1 text-gray-600 dark:text-gray-400">Gestiona los productos incluidos en esta comanda</p>
                            </div>
                            <div class="text-right">
                                <div class="text-sm text-gray-500 dark:text-gray-400">Total actual</div>
                                <div class="text-2xl font-bold text-green-600 dark:text-green-400" id="totalActualizado">$0.00</div>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700" id="tablaProductos">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            <i class="fas fa-box mr-2"></i>Producto
                                        </th>
                                        <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            <i class="fas fa-calculator mr-2"></i>Cantidad
                                        </th>
                                        <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            <i class="fas fa-dollar-sign mr-2"></i>Precio Final
                                        </th>
                                        <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            <i class="fas fa-cogs mr-2"></i>Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="p-6 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                            <div class="flex items-center space-x-4">
                                <div class="flex items-center text-gray-600 dark:text-gray-400">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    <span class="text-sm">Los cambios se aplicarán al confirmar</span>
                                </div>
                            </div>
                            <button class="w-full md:w-auto inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-semibold rounded-lg shadow-lg transform transition-all duration-200 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                id="confirmarCambios">
                                <i class="fas fa-check-circle mr-2"></i>
                                Confirmar y Regenerar PDF
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div id="emptyState" class="text-center py-12">
                <div class="mx-auto h-24 w-24 text-gray-400">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="h-24 w-24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">No hay comanda seleccionada</h3>
                <p class="mt-2 text-gray-500 dark:text-gray-400">Busca una comanda para comenzar a editarla</p>
            </div>
        </div>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script>
            if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                document.documentElement.classList.add('dark');
            }

            $(document).ready(function() {
                let productosActuales = [];

                function showBadge(txt, type) {
                    const badge = $('#badgeComanda');
                    const badgeText = $('#badgeText');
                    const statusBadge = $('#statusBadge');

                    badge.removeClass('bg-blue-500 bg-red-500 bg-green-500 bg-yellow-500 text-blue-100 text-red-100 text-green-100 text-yellow-900');

                    switch (type) {
                        case 'success':
                            badge.addClass('bg-green-500 text-green-100');
                            break;
                        case 'error':
                            badge.addClass('bg-red-500 text-red-100');
                            break;
                        case 'warning':
                            badge.addClass('bg-yellow-500 text-yellow-900');
                            break;
                        default:
                            badge.addClass('bg-blue-500 text-blue-100');
                    }

                    badgeText.text(txt);
                    statusBadge.removeClass('hidden').addClass('animate-slideIn');
                }

                function hideBadge() {
                    $('#statusBadge').addClass('hidden').removeClass('animate-slideIn');
                }

                function cargarProductos(comandaId) {
                    showBadge('Buscando comanda...', 'info');
                    $('#emptyState').addClass('hidden');

                    $.getJSON(window.location.origin + '/api/recreateComanda/getProductosComanda.php', {
                            comanda_id: comandaId
                        })
                        .done(function(res) {
                            if (res.success) {
                                productosActuales = res.productos;
                                let total = 0;
                                $('#tablaProductos tbody').empty();

                                if (res.productos.length === 0) {
                                    $('#tablaProductos tbody').append(
                                        `<tr>
                                      <td colspan="4" class="px-6 py-12 text-center">
                                          <div class="flex flex-col items-center">
                                              <i class="fas fa-inbox text-gray-400 text-4xl mb-4"></i>
                                              <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No hay productos</h3>
                                              <p class="text-gray-500 dark:text-gray-400">Esta comanda no contiene productos.</p>
                                          </div>
                                      </td>
                                   </tr>`
                                    );
                                }

                                res.productos.forEach((producto, index) => {
                                    // Aplicar lógica de tipo de inventario
                                    const unidad = producto.Tipo === "Pesable" 
                                        ? (parseFloat(producto.Cantidad) >= 1.0 ? 'Kg' : 'g') 
                                        : 'Unidad(es)';
                                    
                                    const cantidadFormateada = producto.Tipo === "Pesable" 
                                        ? (parseFloat(producto.Cantidad) >= 1.0 ? 
                                            parseFloat(producto.Cantidad).toFixed(2) : 
                                            parseFloat(producto.Cantidad).toFixed(3))
                                        : parseInt(producto.Cantidad).toString();

                                    const fila = `
                                  <tr data-movimiento-id="${producto.ID}" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors animate-slideIn" style="animation-delay: ${index * 100}ms">
                                      <td class="px-6 py-4">
                                          <div class="flex items-center">
                                              <div class="flex-shrink-0 h-10 w-10">
                                                  <div class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center">
                                                      <i class="fas fa-box text-white text-sm"></i>
                                                  </div>
                                              </div>
                                              <div class="ml-4">
                                                  <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                      ${producto.Nombre}
                                                  </div>
                                                  <div class="text-sm text-gray-500 dark:text-gray-400">
                                                      ID: ${producto.ID} • Tipo: ${producto.Tipo}
                                                  </div>
                                              </div>
                                          </div>
                                      </td>
                                      <td class="px-6 py-4 text-center">
                                          <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                              <i class="fas fa-calculator mr-1 text-xs"></i>
                                              ${cantidadFormateada} ${unidad}
                                          </span>
                                      </td>
                                      <td class="px-6 py-4 text-center">
                                          <span class="text-lg font-semibold text-green-600 dark:text-green-400">
                                              $${parseFloat(producto.PrecioFinal || 0).toFixed(2)}
                                          </span>
                                      </td>
                                      <td class="px-6 py-4">
                                          <div class="flex items-center justify-center space-x-2">
                                              <button class="eliminar inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-full text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                                                  <i class="fas fa-trash mr-1"></i>
                                                  Eliminar
                                              </button>
                                              <div class="flex items-center space-x-1">
                                                  <input type="number" 
                                                         min="0.001" 
                                                         max="${producto.Cantidad}" 
                                                         step="${producto.Tipo === 'Pesable' ? '0.001' : '1'}"
                                                         value="${producto.Tipo === 'Pesable' ? '0.001' : '1'}" 
                                                         class="inputDevolver w-20 px-2 py-1 text-center border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                                                  <button class="devolver inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-full text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-colors">
                                                      <i class="fas fa-undo mr-1"></i>
                                                      Devolver
                                                  </button>
                                              </div>
                                          </div>
                                      </td>
                                  </tr>`;
                                    $('#tablaProductos tbody').append(fila);
                                    total += parseFloat(producto.PrecioUnitario * 1.16) * producto.Cantidad;
                                });

                                $('#totalActualizado').text('$' + total.toFixed(2));
                                $('#productosContainer').removeClass('hidden').addClass('animate-fadeIn');
                                $('#emptyState').addClass('hidden');
                                showBadge('Comanda encontrada', 'success');
                            } else {
                                Swal.fire({
                                    title: 'Comanda no encontrada',
                                    text: res.error || 'No se pudo encontrar la comanda especificada.',
                                    icon: 'error',
                                    confirmButtonColor: '#3B82F6'
                                });
                                $('#tablaProductos tbody').empty();
                                $('#productosContainer').addClass('hidden');
                                $('#emptyState').removeClass('hidden');
                                hideBadge();
                                $('#totalActualizado').text('$0.00');
                            }
                        })
                        .fail(function() {
                            Swal.fire({
                                title: 'Error de conexión',
                                text: 'No se pudo conectar con el servidor. Verifica tu conexión a internet.',
                                icon: 'error',
                                confirmButtonColor: '#3B82F6'
                            });
                            hideBadge();
                            $('#productosContainer').addClass('hidden');
                            $('#emptyState').removeClass('hidden');
                        });
                }

                $('#buscarComanda').click(function() {
                    const comandaId = $('#comandaId').val().trim();
                    if (comandaId === '') {
                        Swal.fire({
                            title: 'Campo requerido',
                            text: 'Por favor ingresa el ID de la comanda.',
                            icon: 'warning',
                            confirmButtonColor: '#3B82F6'
                        });
                        $('#comandaId').focus();
                        return;
                    }
                    cargarProductos(comandaId);
                });

                $('#comandaId').keypress(function(e) {
                    if (e.which === 13) {
                        $('#buscarComanda').click();
                    }
                });

                $('#tablaProductos').on('click', '.eliminar', function() {
                    const tr = $(this).closest('tr');
                    const movimientoId = tr.data('movimiento-id');
                    const comandaId = $('#comandaId').val();

                    Swal.fire({
                        title: '¿Eliminar producto?',
                        text: "Esta acción eliminará permanentemente el producto de la comanda.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#EF4444',
                        cancelButtonColor: '#6B7280',
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Eliminando...',
                                text: 'Por favor espera un momento',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading()
                                }
                            });

                            $.ajax({
                                url: window.location.origin + '/api/recreateComanda/eliminarProductoComanda.php',
                                method: 'POST',
                                contentType: 'application/json',
                                data: JSON.stringify({
                                    movimiento_id: movimientoId
                                }),
                                dataType: 'json',
                                success: function(res) {
                                    if (res.success) {
                                        Swal.fire({
                                            title: '¡Eliminado!',
                                            text: 'El producto ha sido eliminado de la comanda.',
                                            icon: 'success',
                                            timer: 2000,
                                            confirmButtonColor: '#3B82F6'
                                        });
                                        cargarProductos(comandaId);
                                    } else {
                                        Swal.fire({
                                            title: 'Error',
                                            text: res.error || 'No se pudo eliminar el producto.',
                                            icon: 'error',
                                            confirmButtonColor: '#3B82F6'
                                        });
                                    }
                                },
                                error: function() {
                                    Swal.fire({
                                        title: 'Error',
                                        text: 'Error de conexión al eliminar el producto.',
                                        icon: 'error',
                                        confirmButtonColor: '#3B82F6'
                                    });
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
                        Swal.fire({
                            title: 'Cantidad inválida',
                            text: 'Por favor ingresa una cantidad válida para devolver.',
                            icon: 'warning',
                            confirmButtonColor: '#3B82F6'
                        });
                        return;
                    }

                    // Obtener información del producto para mostrar unidades correctas
                    const productoActual = productosActuales.find(p => p.ID == movimientoId);
                    const unidad = productoActual && productoActual.Tipo === "Pesable" 
                        ? (cantidad >= 1.0 ? 'Kg' : 'g') 
                        : (cantidad === 1 ? 'unidad' : 'unidades');
                    
                    const cantidadFormateada = productoActual && productoActual.Tipo === "Pesable"
                        ? (cantidad >= 1.0 ? cantidad.toFixed(2) : cantidad.toFixed(3))
                        : cantidad.toString();

                    Swal.fire({
                        title: '¿Devolver producto?',
                        text: `Se devolverán ${cantidadFormateada} ${unidad} al inventario.`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#F59E0B',
                        cancelButtonColor: '#6B7280',
                        confirmButtonText: 'Sí, devolver',
                        cancelButtonText: 'Cancelar',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Procesando devolución...',
                                text: 'Por favor espera un momento',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading()
                                }
                            });

                            $.ajax({
                                url: window.location.origin + '/api/recreateComanda/devolverProductoComanda.php',
                                method: 'POST',
                                contentType: 'application/json',
                                data: JSON.stringify({
                                    movimiento_id: movimientoId,
                                    cantidad
                                }),
                                dataType: 'json',
                                success: function(res) {
                                    if (res.success) {
                                        Swal.fire({
                                            title: '¡Devuelto!',
                                            text: 'La cantidad ha sido devuelta al inventario correctamente.',
                                            icon: 'success',
                                            timer: 2000,
                                            confirmButtonColor: '#3B82F6'
                                        });
                                        cargarProductos(comandaId);
                                    } else {
                                        Swal.fire({
                                            title: 'Error',
                                            text: res.error || 'No se pudo devolver el producto.',
                                            icon: 'error',
                                            confirmButtonColor: '#3B82F6'
                                        });
                                    }
                                },
                                error: function() {
                                    Swal.fire({
                                        title: 'Error',
                                        text: 'Error de conexión al devolver el producto.',
                                        icon: 'error',
                                        confirmButtonColor: '#3B82F6'
                                    });
                                }
                            });
                        }
                    });
                });

                $('#confirmarCambios').click(function() {
                    const comandaId = $('#comandaId').val().trim();

                    if (!comandaId) {
                        Swal.fire({
                            title: 'Campo requerido',
                            text: 'Por favor ingresa el ID de la comanda.',
                            icon: 'warning',
                            confirmButtonColor: '#3B82F6'
                        });
                        return;
                    }

                    Swal.fire({
                        title: '¿Confirmar cambios?',
                        text: 'Se regenerará el PDF de la comanda con todos los cambios realizados.',
                        icon: 'info',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, confirmar',
                        cancelButtonText: 'Cancelar',
                        confirmButtonColor: '#10B981',
                        cancelButtonColor: '#6B7280',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Regenerando PDF...',
                                text: 'Por favor espera un momento',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading()
                                }
                            });

                            $.post(window.location.origin + '/api/recreateComanda/regenerarComandaPDF.php', {
                                comanda_id: comandaId
                            }, function(res) {
                                if (res.status === 'success') {
                                    Swal.fire({
                                        title: '¡Éxito!',
                                        text: 'La comanda ha sido actualizada y el PDF regenerado correctamente.',
                                        icon: 'success',
                                        confirmButtonColor: '#3B82F6'
                                    });
                                    showBadge('PDF regenerado', 'success');
                                } else {
                                    Swal.fire({
                                        title: 'Error',
                                        text: res.message || 'No se pudo regenerar el PDF.',
                                        icon: 'error',
                                        confirmButtonColor: '#3B82F6'
                                    });
                                }
                            }, 'json').fail(function() {
                                Swal.fire({
                                    title: 'Error',
                                    text: 'Error de conexión al regenerar el PDF.',
                                    icon: 'error',
                                    confirmButtonColor: '#3B82F6'
                                });
                            });
                        }
                    });
                });
            });
        </script>
    </body>