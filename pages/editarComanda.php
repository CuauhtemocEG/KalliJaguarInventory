<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Comanda</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <!-- SweetAlert2 -->
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background: #f8fafc;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        .main-card {
            max-width: 800px;
            margin: 2rem auto;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 6px 30px 0 #e7eaf3;
            padding: 2.5rem 2.5rem 1.5rem 2.5rem;
        }
        .section-title {
            font-size: 1.7rem;
            color: #2365bc;
            font-weight: 600;
            margin-bottom: 2rem;
            letter-spacing: .5px;
        }
        .form-label {
            font-weight: 600;
        }
        .table thead {
            background: #f3f6fa;
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .btn-primary, .btn-success, .btn-warning, .btn-danger {
            font-size: 1em;
        }
        .inputDevolver {
            min-width: 60px;
        }
        @media (max-width: 600px) {
            .main-card { padding: 1rem; }
            .section-title { font-size: 1.2rem; }
            .table-responsive { font-size: .95em; }
        }
    </style>
</head>
<body>
    <div class="main-card">
        <div class="d-flex align-items-center mb-4">
            <i class="bi bi-pencil-square fs-2 text-primary me-2"></i>
            <h2 class="section-title mb-0">Editar Comanda</h2>
        </div>
        <form id="buscarComandaForm">
            <div class="row g-3 align-items-end mb-4">
                <div class="col-sm-8">
                    <label for="comandaId" class="form-label"><i class="bi bi-hash"></i> ID de Comanda</label>
                    <input type="text" class="form-control" id="comandaId" placeholder="Ejemplo: 12345" autocomplete="off">
                </div>
                <div class="col-sm-4 d-grid">
                    <button id="buscarComanda" class="btn btn-primary" type="submit">
                        <i class="bi bi-search"></i> Buscar Comanda
                    </button>
                </div>
            </div>
        </form>

        <div class="table-responsive mb-3">
            <table class="table table-hover table-bordered align-middle" id="tablaProductos">
                <thead class="align-middle text-center">
                    <tr>
                        <th><i class="bi bi-box-seam"></i> Nombre</th>
                        <th><i class="bi bi-123"></i> Cantidad</th>
                        <th><i class="bi bi-currency-dollar"></i> Precio Final</th>
                        <th><i class="bi bi-tools"></i> Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-center align-middle"></tbody>
            </table>
        </div>

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3 mb-1 border-top pt-3">
            <h5 class="mb-3 mb-md-0">
                <i class="bi bi-cash-stack text-success"></i>
                Total actualizado: <span class="fw-bold text-primary" id="totalActualizado">0.00</span>
            </h5>
            <button class="btn btn-success px-4" id="confirmarCambios">
                <i class="bi bi-file-earmark-pdf"></i> Confirmar y regenerar PDF
            </button>
        </div>
    </div>
    <!-- JQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        let productosActuales = [];

        function cargarProductos(comandaId) {
            $('#tablaProductos tbody').html(
                `<tr><td colspan="4" class="text-secondary"><div class="spinner-border spinner-border-sm text-primary me-2"></div> Cargando productos...</td></tr>`
            );
            $.getJSON('https://www.kallijaguar-inventory.com/api/recreateComanda/getProductosComanda.php', {
                comanda_id: comandaId
            })
            .done(function(res) {
                $('#tablaProductos tbody').empty();
                if (res.success) {
                    productosActuales = res.productos;
                    let total = 0;
                    if (!res.productos.length) {
                        $('#tablaProductos tbody').html(
                            `<tr><td colspan="4" class="text-center text-muted">No hay productos en esta comanda.</td></tr>`
                        );
                        $('#totalActualizado').text("0.00");
                        return;
                    }
                    res.productos.forEach(producto => {
                        const fila = `<tr data-movimiento-id="${producto.ID}">
                            <td>${producto.Nombre}</td>
                            <td>${producto.Cantidad}</td>
                            <td>$${parseFloat(producto.PrecioFinal || 0).toFixed(2)}</td>
                            <td>
                                <button class="btn btn-danger btn-sm eliminar" title="Eliminar producto"><i class="bi bi-trash"></i></button>
                                <input type="number" min="1" max="${producto.Cantidad}" value="1" class="form-control d-inline w-25 ms-2 inputDevolver" title="Cantidad a devolver">
                                <button class="btn btn-warning btn-sm devolver ms-1" title="Devolver"><i class="bi bi-arrow-counterclockwise"></i> Devolver</button>
                            </td>
                        </tr>`;
                        $('#tablaProductos tbody').append(fila);
                        total += parseFloat(producto.PrecioUnitario*1.16) * producto.Cantidad;
                    });

                    $('#totalActualizado').text(total.toFixed(2));
                } else {
                    Swal.fire('Error', res.error, 'error');
                    $('#totalActualizado').text("0.00");
                    $('#tablaProductos tbody').html(
                        `<tr><td colspan="4" class="text-center text-danger">No se encontraron productos.</td></tr>`
                    );
                }
            })
            .fail(function() {
                Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error');
                $('#tablaProductos tbody').html(
                    `<tr><td colspan="4" class="text-center text-danger">Error de conexión.</td></tr>`
                );
                $('#totalActualizado').text("0.00");
            });
        }

        $('#buscarComandaForm').submit(function(e){
            e.preventDefault();
            $('#buscarComanda').prop('disabled', true);
            const comandaId = $('#comandaId').val().trim();
            if (comandaId === '') {
                Swal.fire('Error', 'Ingrese un ID de comanda.', 'warning');
                $('#buscarComanda').prop('disabled', false);
                return;
            }
            cargarProductos(comandaId);
            setTimeout(() => $('#buscarComanda').prop('disabled', false), 800);
        });

        $('#tablaProductos').on('click', '.eliminar', function() {
            const tr = $(this).closest('tr');
            const movimientoId = tr.data('movimiento-id');
            const comandaId = $('#comandaId').val();

            Swal.fire({
                title: '¿Eliminar producto?',
                text: "Esta acción no se puede deshacer.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'https://www.kallijaguar-inventory.com/api/recreateComanda/eliminarProductoComanda.php',
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
                text: `¿Seguro que quieres devolver ${cantidad} unidad(es) al inventario?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, devolver',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'https://www.kallijaguar-inventory.com/api/recreateComanda/devolverProductoComanda.php',
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
                text: 'Se regenerará el PDF de la comanda.',
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Sí, confirmar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('https://www.kallijaguar-inventory.com/api/recreateComanda/regenerarComandaPDF.php', {
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