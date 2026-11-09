<?php
require_once BASE_PATH . '/src/Helpers/legacy.php';

/*== Almacenando id ==*/
$id = limpiar_cadena($_POST['productID']);


/*== Verificando producto ==*/
$check_producto = conexion();
$check_producto = consulta($check_producto, "SELECT * FROM Productos WHERE ProductoID=:p1", [':p1' => $id]);

if ($check_producto->rowCount() <= 0) {
    echo '
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong>¡Ocurrio un error!</strong><br>
                La Producto no existe en el sistema.
            </div>';
    exit();
} else {
    $datos = $check_producto->fetch();
}
$check_producto = null;


/*== Almacenando datos ==*/
$codigo = limpiar_cadena($_POST['productUPC']);
$nombre = limpiar_cadena($_POST['productName']);
$typeInventory = limpiar_cadena($_POST['productTypeInventory']);
$precio = limpiar_cadena($_POST['productPrecio']);
$stock = limpiar_cadena($_POST['productStock']);
$categoria = limpiar_cadena($_POST['productCategory']);
$tags = limpiar_cadena($_POST['productTag']);
$description = limpiar_cadena($_POST['productDescription']);


/*== Verificando campos obligatorios ==*/
if ($codigo == "" || $nombre == "" || $precio == "" || $stock == "" || $categoria == ""|| $typeInventory == "" ) {
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
//if (verificar_datos("[a-zA-Z0-9- ]{1,70}", $codigo)) {
//    echo '
//            <div class="alert alert-danger alert-dismissible fade show" role="alert">
//                <button type="button" class="close" data-dismiss="alert" //aria-label="Close">
//                <span aria-hidden="true">&times;</span>
//                </button>
//                <strong>¡Ocurrio un error!</strong><br>
//                El Código de Barras (UPC) no coincide con el formato solicitado.
//            </div>';
//    exit();
//}

//if (verificar_datos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ().,$#\-\/ ]{1,70}", $nombre)) {
//    echo '
//            <div class="alert alert-danger alert-dismissible fade show" role="alert">
//                <button type="button" class="close" data-dismiss="alert" //aria-label="Close">
//                <span aria-hidden="true">&times;</span>
//                </button>
//                <strong>¡Ocurrio un error!</strong><br>
//                El Nombre del Producto no coincide con el formato solicitado.
//            </div>';
//    exit();
//}

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
if ($codigo != $datos['UPC']) {
    $check_codigo = conexion();
    $check_codigo = consulta($check_codigo, "SELECT UPC FROM Productos WHERE UPC=:p1", [':p1' => $codigo]);
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
}


/*== Verificando nombre ==*/
if ($nombre != $datos['Nombre']) {
    $check_nombre = conexion();
    $check_nombre = consulta($check_nombre, "SELECT Nombre FROM Productos WHERE Nombre=:p1", [':p1' => $nombre]);
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
}


/*== Verificando categoria ==*/
if ($categoria != $datos['CategoriaID']) {
    $check_categoria = conexion();
    $check_categoria = consulta($check_categoria, "SELECT CategoriaID FROM Categorias WHERE CategoriaID=:p1", [':p1' => $categoria]);
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
}


/*== Actualizando datos ==*/
$actualizar_producto = conexion();
$actualizar_producto = $actualizar_producto->prepare("UPDATE Productos SET UPC=:codigo,Nombre=:nombre,PrecioUnitario=:precio,Cantidad=:stock,CategoriaID=:categoria,Tipo=:tipo, Tag=:tag, Descripcion=:description WHERE ProductoID=:id");

$marcadores = [
    ":codigo" => $codigo,
    ":nombre" => $nombre,
    ":precio" => $precio,
    ":stock" => $stock,
    ":categoria" => $categoria,
    ":id" => $id,
    ":tipo" => $typeInventory,
    ":tag" => $tags,
    ":description" => $description
];


if ($actualizar_producto->execute($marcadores)) {
    echo '
         <div class="alert alert-success alert-dismissible fade show"role="alert">
            <button type="button" class="close" data-dismiss="alert"aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <strong>¡Producto Actualizado!</strong><br>
            El Producto se ha actualizado con éxito.
        </div>';
} else {
    echo '
        <div class="alert alert-success alert-dismissible fade show"role="alert">
            <button type="button" class="close" data-dismiss="alert"aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <strong>¡Ocurrio un error!</strong><br>
            No se pudo registrar el producto, por favor intente nuevamente.
        </div>';
}
$actualizar_producto = null;
