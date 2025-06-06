<?php
require_once "mainController.php";

/*== Almacenando datos ==*/
$product_id = limpiar_cadena($_POST['idImageDel']);

/*== Verificando producto ==*/
$check_producto = conexion();
$check_producto = $check_producto->query("SELECT * FROM Productos WHERE ProductoID='$product_id'");

if ($check_producto->rowCount() == 1) {
    $datos = $check_producto->fetch();
} else {
    echo '
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <button type="button" class="close" data-dismiss="alert"aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
            <strong>¡Ocurrio un error!</strong><br>
            La Imagen del Producto que intenta eliminar no existe.
        </div>';
    exit();
}
$check_producto = null;


/* Directorios de imagenes */
$img_dir = '../img/producto/';

/* Cambiando permisos al directorio */
chmod($img_dir, 0777);


/* Eliminando la imagen */
if (is_file($img_dir . $datos['image'])) {

    chmod($img_dir . $datos['image'], 0777);

    if (!unlink($img_dir . $datos['image'])) {
        echo '
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <button type="button" class="close" data-dismiss="alert"aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
            <strong>¡Ocurrio un error!</strong><br>
            Error al intentar eliminar la imagen del producto, por favor intente nuevamente.
        </div>';
        exit();
    }
}


/*== Actualizando datos ==*/
$actualizar_producto = conexion();
$actualizar_producto = $actualizar_producto->prepare("UPDATE Productos SET image=:foto WHERE ProductoID=:id");

$marcadores = [
    ":foto" => "",
    ":id" => $product_id
];

if ($actualizar_producto->execute($marcadores)) {
    echo '
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <button type="button" class="close" data-dismiss="alert"aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
            <strong>¡Imagen o Foto Eliminada!</strong><br>
            La imagen del producto ha sido eliminada exitosamente, pulse "Aceptar" para recargar los cambios.
            <p class="has-text-centered pt-5 pb-5">
                <a href="index.php?page=updateProductImage&idProductUp=' . $product_id . '" class="btn btn-info">Aceptar</a>
            </p>
        </div>';
} else {
    echo '
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <button type="button" class="close" data-dismiss="alert"aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
            <strong>¡Imagen o Foto Eliminada!</strong><br>
            Ocurrieron algunos inconvenientes, sin embargo la imagen del producto ha sido eliminada, pulse "Aceptar" para recargar los cambios.
            <p class="has-text-centered pt-5 pb-5">
                <a href="index.php?page=updateProductImage&idProductUp=' . $product_id . '" class="btn btn-info">Aceptar</a>
            </p>
        </div>
        ';
}
$actualizar_producto = null;
