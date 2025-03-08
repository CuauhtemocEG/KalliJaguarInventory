    <div class="container mt-5">
        <h1 class="text-center">Calendario de Gastos</h1>
        <div class="row">
            <div class="form-rest"></div>
            <div class="col-12">
                <!-- Aquí se generará el calendario -->
                <div id="calendario"></div>
            </div>
        </div>
        <!-- Modal para agregar gastos -->
        <div class="modal fade" id="modalAgregarGasto" tabindex="-1" aria-labelledby="modalAgregarGastoLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalAgregarGastoLabel">Agregar Gasto</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="formAgregarGasto">
                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <input type="text" class="form-control" id="descripcion" required>
                            </div>
                            <div class="mb-3">
                                <label for="monto" class="form-label">Monto</label>
                                <input type="number" class="form-control" id="monto" required>
                            </div>
                            <div class="mb-3">
                                <label for="fecha" class="form-label">Fecha</label>
                                <input type="date" class="form-control" id="fecha" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            // Llamar a PHP para obtener los gastos y generar el calendario
            function cargarCalendario() {
                $.ajax({
                    url: '../controllers/showExpensesController.php',
                    method: 'GET',
                    success: function(data) {
                        $('#calendario').html(data);
                    }
                });
            }
            cargarCalendario();

            // Agregar gasto
            $('#formAgregarGasto').submit(function(event) {
                event.preventDefault();
                var descripcion = $('#descripcion').val();
                var monto = $('#monto').val();
                var fecha = $('#fecha').val();

                $.ajax({
                    url: '../controllers/addExpense.php',
                    method: 'POST',
                    data: {
                        descripcion: descripcion,
                        monto: monto,
                        fecha: fecha
                    },
                    success: function(response) {
                        alert('Gasto agregado');
                        $('#modalAgregarGasto').modal('hide');
                        cargarCalendario();
                    }
                });
            });
        });
    </script>