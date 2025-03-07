<?php
require_once "../includes/session_start.php";

require_once "mainController.php";

/*== Almacenando datos ==*/
$codigo = limpiar_cadena($_POST['productUPC']);
$nombre = limpiar_cadena($_POST['productName']);

$precio = limpiar_cadena($_POST['productPrecio']);
$stock = limpiar_cadena($_POST['productStock']);
$typeInventory = limpiar_cadena($_POST['productTypeInventory']);
$categoria = limpiar_cadena($_POST['productCategory']);


/*== Verificando campos obligatorios ==*/
if ($codigo == "" || $nombre == "" || $precio == "" || $stock == "" || $categoria == "" || $typeInventory == "") {
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
if (verificar_datos("[a-zA-Z0-9- ]{1,70}", $codigo)) {
    echo '
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
                <strong>¡Ocurrio un error!</strong><br>
                El Código de Barras (UPC) no coincide con el formato solicitado.
            </div>';
    exit();
}

if (verificar_datos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ().,$#\-\/ ]{1,70}", $nombre)) {
    echo '
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
                <strong>¡Ocurrio un error!</strong><br>
                El Nombre del Producto no coincide con el formato solicitado.
            </div>';
    exit();
}

if (verificar_datos("[0-9.]{1,25}", $precio)) {
    echo '
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
                <strong>¡Ocurrio un error!</strong><br>
                El Precio no coincide con el formato solicitado.
            </div>';
    exit();
}

if (verificar_datos("[0.000-9.000]{1,25}", $stock)) {
    echo '
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
                <strong>¡Ocurrio un error!</strong><br>
                El Stock no coincide con el formato solicitado.
            </div>';
    exit();
}


/*== Verificando codigo ==*/
$check_codigo = conexion();
$check_codigo = $check_codigo->query("SELECT UPC FROM Productos WHERE UPC='$codigo'");
if ($check_codigo->rowCount() > 0) {
    echo '
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
                <strong>¡Ocurrio un error!</strong><br>
                El Código de Barras (UPC) ingresado ya se encuentra registrado, por favor elija otro.
            </div>';
    exit();
}
$check_codigo = null;


/*== Verificando nombre ==*/
$check_nombre = conexion();
$check_nombre = $check_nombre->query("SELECT Nombre FROM Productos WHERE Nombre='$nombre'");
if ($check_nombre->rowCount() > 0) {
    echo '
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
                <strong>¡Ocurrio un error!</strong><br>
                El Nombre del Producto ingresado ya se encuentra registrado, por favor elija otro.
            </div>';
    exit();
}
$check_nombre = null;


/*== Verificando categoria ==*/
$check_categoria = conexion();
$check_categoria = $check_categoria->query("SELECT CategoriaID FROM Categorias WHERE CategoriaID='$categoria'");
if ($check_categoria->rowCount() <= 0) {
    echo '
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
                <strong>¡Ocurrio un error!</strong><br>
                La categoría ingresada no existe en Sistema.
            </div>';
    exit();
}
$check_categoria = null;


/* Directorios de imagenes */
$img_dir = '../img/producto/';


/*== Comprobando si se ha seleccionado una imagen ==*/
if ($_FILES['productImage']['name'] != "" && $_FILES['productImage']['size'] > 0) {

    /* Creando directorio de imagenes */
    if (!file_exists($img_dir)) {
        if (!mkdir($img_dir, 0777)) {
            echo '
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong>¡Ocurrio un error!</strong><br>
                Error al crear el directorio de imagenes.
            </div>';
            exit();
        }
    }

    /* Comprobando formato de las imagenes */
    if (mime_content_type($_FILES['productImage']['tmp_name']) != "image/jpeg" && mime_content_type($_FILES['productImage']['tmp_name']) != "image/png") {
        echo '
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong>¡Ocurrio un error!</strong><br>
                La imagen que ha seleccionado es de un formato que no está permitido.
            </div>';
        exit();
    }


    /* Comprobando que la imagen no supere el peso permitido */
    if (($_FILES['productImage']['size'] / 1024) > 3072) {
        echo '
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong>¡Ocurrio un error!</strong><br>
                La imagen que ha seleccionado supera el límite de peso permitido.
            </div>';
        exit();
    }


    /* extencion de las imagenes */
    switch (mime_content_type($_FILES['productImage']['tmp_name'])) {
        case 'image/jpeg':
            $img_ext = ".jpg";
            break;
        case 'image/png':
            $img_ext = ".png";
            break;
    }

    /* Cambiando permisos al directorio */
    chmod($img_dir, 0777);

    /* Nombre de la imagen */
    $img_nombre = renombrar_fotos($nombre);

    /* Nombre final de la imagen */
    $foto = $img_nombre . $img_ext;

    /* Moviendo imagen al directorio */
    if (!move_uploaded_file($_FILES['productImage']['tmp_name'], $img_dir . $foto)) {
        echo '
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong>¡Ocurrio un error!</strong><br>
                No podemos subir la imagen al sistema en este momento, por favor intente nuevamente.
            </div>';
        exit();
    }
} else {
    $foto = "";
}


/*== Guardando datos ==*/
$guardar_producto = conexion();
$guardar_producto = $guardar_producto->prepare("INSERT INTO Productos(UPC,Nombre,PrecioUnitario,Cantidad,image,Tipo,CategoriaID,UsuarioID) VALUES(:codigo,:nombre,:precio,:stock,:foto,:tipo,:categoria,:usuario)");

$marcadores = [
    ":codigo" => $codigo,
    ":nombre" => $nombre,
    ":precio" => $precio,
    ":stock" => $stock,
    ":foto" => $foto,
    ":tipo" => $typeInventory,
    ":categoria" => $categoria,
    ":usuario" => $_SESSION['id']
];

$guardar_producto->execute($marcadores);

if ($guardar_producto->rowCount() == 1) {
    echo '
        <div class="alert alert-success alert-dismissible fade show"role="alert">
            <button type="button" class="close" data-dismiss="alert"aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <strong>¡Producto Registrado!</strong><br>
            El Producto se ha registrado con éxito.
        </div>';
} else {

    if (is_file($img_dir . $foto)) {
        chmod($img_dir . $foto, 0777);
        unlink($img_dir . $foto);
    }

    echo '
        <div class="alert alert-success alert-dismissible fade show"role="alert">
            <button type="button" class="close" data-dismiss="alert"aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <strong>¡Ocurrio un error!</strong><br>
            No se pudo registrar el producto, por favor intente nuevamente.
        </div>';
}
$guardar_producto = null;
