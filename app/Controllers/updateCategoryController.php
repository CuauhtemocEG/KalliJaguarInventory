<?php
require_once "mainController.php";

/*== Almacenando id ==*/
$id = limpiar_cadena($_POST['idCategory']);


/*== Verificando categoria ==*/
$check_categoria = conexion();
$check_categoria = $check_categoria->query("SELECT * FROM Categorias WHERE CategoriaID='$id'");

if ($check_categoria->rowCount() <= 0) {
    echo '
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong>¡Ocurrio un error!</strong><br>
                La Categoría no existe en el sistema.
            </div>';
    exit();
} else {
    $datos = $check_categoria->fetch();
}
$check_categoria = null;

/*== Almacenando datos ==*/
$nombre = limpiar_cadena($_POST['categoryName']);

/*== Verificando campos obligatorios ==*/
if ($nombre == "") {
    echo '
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong>¡Ocurrio un error!</strong><br>
                No has llenado el campo obligatorio.
            </div>';
    exit();
}


/*== Verificando integridad de los datos ==*/
//if (verificar_datos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ ]{4,50}", $nombre)) {
//    echo '
//            <div class="alert alert-danger alert-dismissible fade show" role="alert">
//                <button type="button" class="close" data-dismiss="alert" //aria-label="Close">
//                    <span aria-hidden="true">&times;</span>
//                </button>
//                <strong>¡Ocurrio un error!</strong><br>
//                El Nombre de la Categoría no coincide con el formato solicitado.
//            </div>';
//    exit();
//}

/*== Verificando nombre ==*/
if ($nombre != $datos['Nombre']) {
    $check_nombre = conexion();
    $check_nombre = $check_nombre->query("SELECT Nombre FROM Categorias WHERE Nombre='$nombre'");
    if ($check_nombre->rowCount() > 0) {
        echo '
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong>¡Ocurrio un error!</strong><br>
                El Nombre de la Categoría ya se encuentra registrado, por favor elija otro.
            </div>';
        exit();
    }
    $check_nombre = null;
}


/*== Actualizar datos ==*/
$actualizar_categoria = conexion();
$actualizar_categoria = $actualizar_categoria->prepare("UPDATE Categorias SET Nombre=:nombre WHERE CategoriaID=:id");

$marcadores = [
    ":nombre" => $nombre,
    ":id" => $id
];

if ($actualizar_categoria->execute($marcadores)) {
    echo '
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong>¡Categoría Actualizada!</strong><br>
                La Categoría se actualizó con exito.
            </div>';
} else {
    echo '
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong>¡Ocurrio un error!</strong><br>
                No se pudo actualizar la Categoría, por favor intente nuevamente.
            </div>';
}
$actualizar_categoria = null;
