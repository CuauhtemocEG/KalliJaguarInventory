<?php
header('Content-Type: application/json');
session_start();

require_once '../../controllers/mainController.php';

$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];


$productos = [];

foreach ($cart as $item) {
    $conexion = conexion();
    $stmt = $conexion->prepare("SELECT ProductoID, Nombre, UPC, Descripcion, PrecioUnitario, Tipo, image FROM Productos WHERE ProductoID = ?");
    $stmt->execute([$item['producto_id']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $nombreImagen = !empty($row['image']) && is_file("../../img/producto/" . $row['image'])
            ? $row['image']
            : 'producto.png';
        $urlImagen = 'https://www.kallijaguar-inventory.com/img/producto/' . $nombreImagen;
        $productos[] = [
            'id'         => $row['ProductoID'],
            'nombre'     => $row['Nombre'],
            'upc'        => $row['UPC'],
            'descripcion'=> $row['Descripcion'],
            'precio'     => $row['PrecioUnitario'],
            'unidad'     => $row['Tipo'],
            'imagen'     => $urlImagen,
            'cantidad'   => $item['cantidad']
        ];
    }
}

echo json_encode([
    'status' => 'success',
    'cart'   => $productos
]);
exit();
?>