<?php
require_once "../includes/session_start.php";
require_once "mainController.php";

if ($conexiones->connect_error) {
    die("Conexión fallida: " . $conexiones->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $descripcion = limpiar_cadena($_POST['descripcion']);
    $monto = limpiar_cadena($_POST['monto']);
    $fecha = limpiar_cadena($_POST['fecha']);

    /*== Verificando campos obligatorios ==*/
    if ($fecha == "" || $monto == "" || $descripcion == "") {
        echo '
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
                <strong>¡Ocurrio un error!</strong><br>
                No has llenado todos los campos que son obligatorios.
            </div>';
        exit();
    }

    $saveExpense = conexion();
    $saveExpense = $saveExpense->prepare("INSERT INTO Gastos(Descripcion,Monto,Fecha) VALUES(:descripcion,:monto,:fecha)");

    $marcadores = [
        ":descripcion" => $descripcion,
        ":monto" => $monto,
        ":fecha" => $fecha
    ];

    $saveExpense->execute($marcadores);

    if ($saveExpense->rowCount() == 1) {
        echo '
            <div class="alert alert-success alert-dismissible fade show"role="alert">
                <button type="button" class="close" data-dismiss="alert"aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong>¡Gasto Registrado!</strong><br>
                El Gasto se ha registrado con éxito.
            </div>';
    } else {
        echo '
            <div class="alert alert-success alert-dismissible fade show"role="alert">
                <button type="button" class="close" data-dismiss="alert"aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong>¡Ocurrio un error!</strong><br>
                No se pudo registrar el gasto, por favor intente nuevamente.
            </div>';
    }

    $saveExpense = null;
}
