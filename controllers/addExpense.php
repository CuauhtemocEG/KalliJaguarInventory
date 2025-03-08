<?php
require_once "./mainController.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $descripcion = $_POST['descripcion'];
    $monto = $_POST['monto'];
    $fecha = $_POST['fecha'];

    $addGastos = conexion();
    $addGastos = $addGastos->prepare("INSERT INTO Gastos (Descripcion, Monto, Fecha) VALUES (:descripcion,:monto,:fecha)");

    $marcadores = [
        ":descripcion" => $descripcion,
        ":monto" => $monto,
        ":fecha" => $fecha
    ];

    $addGastos->execute($marcadores);

    if ($addGastos->rowCount() == 1) {
        echo '
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
                <strong>¡Gasto Registrado!</strong><br>
                El Gasto se registro con éxito en la Base de Datos.
            </div>
        ';
    } else {
        echo '
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
                <strong>¡Ocurrio un error!</strong><br>
                No se pudo registrar el gasto, por favor intente nuevamente.
            </div>
        ';
    }
    $conexion = null;
}
