<?php
require_once "mainController.php";

/*== Almacenando datos ==*/
$product_id = limpiar_cadena($_POST['idImageUp']);

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
            La Imagen del Producto que intenta actualizar no existe.
        </div>';
    exit();
}
$check_producto = null;


/*== Comprobando si se ha seleccionado una imagen ==*/
if ($_FILES['imageProduct']['name'] == "" || $_FILES['imageProduct']['size'] == 0) {
    echo '
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <button type="button" class="close" data-dismiss="alert"aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
            <strong>¡Ocurrio un error!</strong><br>
            No ha seleccionado una imagen o foto.
        </div>';
    exit();
}


/* Directorios de imagenes */
$img_dir = '../img/producto/';


/* Creando directorio de imagenes */
if (!file_exists($img_dir)) {
    if (!mkdir($img_dir, 0777)) {
        echo '
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <button type="button" class="close" data-dismiss="alert"aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
            <strong>¡Ocurrio un error!</strong><br>
            Error al crear el directorio de imagenes.
        </div>';
        exit();
    }
}


/* Cambiando permisos al directorio */
chmod($img_dir, 0777);


/* Comprobando formato de las imagenes */
if (mime_content_type($_FILES['imageProduct']['tmp_name']) != "image/jpeg" && mime_content_type($_FILES['imageProduct']['tmp_name']) != "image/png") {
    echo '
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <button type="button" class="close" data-dismiss="alert"aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
            <strong>¡Ocurrio un error!</strong><br>
            La imagen que ha seleccionado es de un formato que no está permitido.
        </div>';
    exit();
}


/* Comprobando que la imagen no supere el peso permitido */
if (($_FILES['imageProduct']['size'] / 1024) > 3072) {
    echo '
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <button type="button" class="close" data-dismiss="alert"aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
            <strong>¡Ocurrio un error!</strong><br>
            La imagen que ha seleccionado supera el límite de peso permitido.
        </div>';
    exit();
}


/* extencion de las imagenes */
switch (mime_content_type($_FILES['imageProduct']['tmp_name'])) {
    case 'image/jpeg':
        $img_ext = ".jpg";
        break;
    case 'image/png':
        $img_ext = ".png";
        break;
}

/* Nombre de la imagen */
$img_nombre = renombrar_fotos($datos['Nombre']);

/* Nombre final de la imagen */
$foto = $img_nombre . $img_ext;

/* Moviendo imagen al directorio */
if (!move_uploaded_file($_FILES['imageProduct']['tmp_name'], $img_dir . $foto)) {
    echo '
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <button type="button" class="close" data-dismiss="alert"aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
            <strong>¡Ocurrio un error!</strong><br>
            No podemos subir la imagen al sistema en este momento, por favor intente nuevamente.
        </div>';
    exit();
}


/* Eliminando la imagen anterior */
if (is_file($img_dir . $datos['image']) && $datos['image'] != $foto) {

    chmod($img_dir . $datos['image'], 0777);
    unlink($img_dir . $datos['image']);
}


/*== Actualizando datos ==*/
$actualizar_producto = conexion();
$actualizar_producto = $actualizar_producto->prepare("UPDATE Productos SET image=:foto WHERE ProductoID=:id");

$marcadores = [
    ":foto" => $foto,
    ":id" => $product_id
];

if ($actualizar_producto->execute($marcadores)) {
    echo '
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <button type="button" class="close" data-dismiss="alert"aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
            <strong>¡Imagen Actualizada!</strong><br>
            La Imagen del Producto ha sido actualizada exitosamente, pulse "Aceptar" para recargar los cambios.

            <p class="has-text-centered pt-5 pb-5">
                <a href="index.php?page=updateProductImage&idProductUp=' . $product_id . '" class="btn btn-info">Aceptar</a>
            </p>
        </div>
        ';
} else {

    if (is_file($img_dir . $foto)) {
        chmod($img_dir . $foto, 0777);
        unlink($img_dir . $foto);
    }

    echo '
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <button type="button" class="close" data-dismiss="alert"aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
            <strong>¡Ocurrio un Error!</strong><br>
            No podemos subir la imagen al sistema en este momento, por favor intente nuevamente
        </div>
        ';
}
$actualizar_producto = null;
