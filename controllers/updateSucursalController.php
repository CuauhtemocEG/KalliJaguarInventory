<?php
require_once "mainController.php";

/*== Almacenando id ==*/
$id = limpiar_cadena($_POST['sucursalId']);


/*== Verificando categoria ==*/
$verifySucursal = conexion();
$verifySucursal = $verifySucursal->query("SELECT * FROM Sucursales WHERE SucursalID='$id'");

if ($verifySucursal->rowCount() <= 0) {
    echo '
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong>¡Ocurrio un error!</strong><br>
                La Sucursal no existe en el sistema.
            </div>';
    exit();
} else {
    $datos = $verifySucursal->fetch();
}
$verifySucursal = null;

/*== Almacenando datos ==*/
$nombre = limpiar_cadena($_POST['nombreSucursal']);
$ubicacion = limpiar_cadena($_POST['addressSucursal']);


/*== Verificando campos obligatorios ==*/
if ($nombre == "") {
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


/*== Verificando integridad de los datos ==*/
if (verificar_datos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ ]{4,50}", $nombre)) {
    echo '
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong>¡Ocurrio un error!</strong><br>
                El nombre de la Sucursal no coincide con el formato solicitado.
            </div>';
    exit();
}

if ($ubicacion != "") {
    if (verificar_datos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ ]{5,150}", $ubicacion)) {
        echo '
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong>¡Ocurrio un error!</strong><br>
                El Ubicación de la Sucursal no coincide con el formato solicitado.
            </div>';
        exit();
    }
}


/*== Verificando nombre ==*/
if ($nombre != $datos['nombre']) {
    $check_nombre = conexion();
    $check_nombre = $check_nombre->query("SELECT nombre FROM Sucursales WHERE nombre='$nombre'");
    if ($check_nombre->rowCount() > 0) {
        echo '
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <strong>¡Ocurrio un error!</strong><br>
                    El nombre de la Sucursal ingresada ya se encuentra registrado, por favor elija otro.
                </div>';
        exit();
    }
    $check_nombre = null;
}


/*== Actualizar datos ==*/
$updateSucursal = conexion();
$updateSucursal = $updateSucursal->prepare("UPDATE Sucursales SET nombre=:nombre,direccion=:ubicacion WHERE SucursalID=:id");

$marcadores = [
    ":nombre" => $nombre,
    ":ubicacion" => $ubicacion,
    ":id" => $id
];

if ($updateSucursal->execute($marcadores)) {
    echo '
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong>Sucursal Actualizada!</strong><br>
                La Sucursal se actualizó con exito en la Base de datos.
            </div>
        ';
} else {
    echo '
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong>¡Ocurrio un error!</strong><br>
                No se pudo actualizar la Sucursal, por favor intente nuevamente.
            </div>
        ';
}
$updateSucursal = null;
