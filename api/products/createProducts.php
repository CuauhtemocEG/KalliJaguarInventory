<?php
header("Content-Type: application/json");
require_once "../../includes/session_start.php";
require_once "../../controllers/mainController.php";
require_once '../../helpers/responseHelper.php';

try {
    $requiredFields = ['productUPC', 'productName', 'productPrecio', 'productStock', 'productTypeInventory', 'productCategory', 'productTag'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Todos los campos son obligatorios.");
        }
    }

    $codigo = limpiar_cadena($_POST['productUPC']);
    $nombre = limpiar_cadena($_POST['productName']);
    $precio = limpiar_cadena($_POST['productPrecio']);
    $stock = limpiar_cadena($_POST['productStock']);
    $tipo = limpiar_cadena($_POST['productTypeInventory']);
    $categoria = limpiar_cadena($_POST['productCategory']);
    $tags = limpiar_cadena($_POST['productTag']);

    if (verificar_datos("[0-9.]{1,25}", $precio)) throw new Exception("El precio tiene un formato inválido.");
    if (verificar_datos("[0-9.]{1,25}", $stock)) throw new Exception("El stock tiene un formato inválido.");

    $db = conexion();
    $check = $db->prepare("SELECT 1 FROM Productos WHERE UPC = ?");
    $check->execute([$codigo]);
    if ($check->rowCount() > 0) throw new Exception("El código UPC ya está registrado.");

    $check = $db->prepare("SELECT 1 FROM Productos WHERE Nombre = ?");
    $check->execute([$nombre]);
    if ($check->rowCount() > 0) throw new Exception("El nombre del producto ya existe.");

    $check = $db->prepare("SELECT 1 FROM Categorias WHERE CategoriaID = ?");
    $check->execute([$categoria]);
    if ($check->rowCount() == 0) throw new Exception("La categoría no existe.");

    $foto = "";
    $img_dir = '../../img/producto/';
    if (!empty($_FILES['productImage']['name'])) {
        if (!file_exists($img_dir)) mkdir($img_dir, 0777, true);
        if ($_FILES['productImage']['size'] > 3 * 1024 * 1024) throw new Exception("La imagen supera el tamaño máximo permitido (3MB).");
        
        $mime = mime_content_type($_FILES['productImage']['tmp_name']);
        $ext = ($mime === 'image/jpeg') ? '.jpg' : (($mime === 'image/png') ? '.png' : null);
        if (!$ext) throw new Exception("Formato de imagen no permitido (solo JPG/PNG).");

        $img_name = renombrar_fotos($nombre) . $ext;
        $ruta_final = $img_dir . $img_name;

        if (!move_uploaded_file($_FILES['productImage']['tmp_name'], $ruta_final)) {
            throw new Exception("Error al subir la imagen.");
        }
        $foto = $img_name;
    }

    $insert = $db->prepare("INSERT INTO Productos (UPC, Nombre, PrecioUnitario, Cantidad, image, Tipo, CategoriaID, UsuarioID, Tag) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $ok = $insert->execute([$codigo, $nombre, $precio, $stock, $foto, $tipo, $categoria, $_SESSION['id'], $tags]);

    if (!$ok) throw new Exception("No se pudo guardar el producto.");

    $response = ['status' => 'success', 'message' => 'Producto registrado correctamente'];
} catch (Exception $e) {
    $response = ['status' => 'error', 'message' => $e->getMessage()];
}

echo json_encode($response);
