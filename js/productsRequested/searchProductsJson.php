<?php
// Headers CORS para permitir solicitudes desde diferentes subdominios
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
header('Content-Type: application/json; charset=UTF-8');

// Manejar OPTIONS request para preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();

// Incluye tu función de conexión y sanitización
require_once '../../controllers/mainController.php';

// Validar sesión
//if (!isset($_SESSION['id'])) {
//    http_response_code(401);
//    echo json_encode(['status' => 'error', 'message' => 'No autenticado']);
//    exit();
//}

// Recibe el parámetro de búsqueda
$query = isset($_GET['query']) ? limpiar_cadena($_GET['query']) : '';

if ($query === '') {
    echo json_encode(['status' => 'success', 'products' => []]);
    exit();
}

// Conexión
$conexion = conexion();

// Campos a seleccionar
$campos = "Productos.ProductoID,Productos.Descripcion,Productos.UPC,Productos.Nombre as nombreProducto,Productos.PrecioUnitario,Productos.Cantidad,Productos.Tipo,Productos.image,Productos.CategoriaID as productCategory,Productos.UsuarioID,Categorias.CategoriaID,Categorias.Nombre as categoryName,Usuarios.UsuarioID,Usuarios.Nombre as userName";

// Consulta preparada con parámetros
$sql = "SELECT $campos 
        FROM Productos 
        INNER JOIN Categorias ON Productos.CategoriaID = Categorias.CategoriaID 
        INNER JOIN Usuarios ON Productos.UsuarioID = Usuarios.UsuarioID 
        WHERE Productos.UPC LIKE :q OR Productos.Nombre LIKE :q 
        ORDER BY Productos.Nombre";

$stmt = $conexion->prepare($sql);
$search = '%' . $query . '%';
$stmt->bindParam(':q', $search, PDO::PARAM_STR);
$stmt->execute();

$productos = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $productos[] = [
        'id'          => $row['ProductoID'],
        'nombre'      => ucwords(strtolower($row['nombreProducto'])),
        'upc'         => $row['UPC'],
        'descripcion' => $row['Descripcion'],
        'existencias' => $row['Cantidad'],
        'unidad'      => $row['Tipo'],
        'precio'      => $row['PrecioUnitario'],
        'imagen'      => $row['image'],
        'categoria'   => [
            'id'   => $row['productCategory'],
            'nombre' => $row['categoryName'],
        ],
        'usuario'     => [
            'id'   => $row['UsuarioID'],
            'nombre' => $row['userName'],
        ]
    ];
}

echo json_encode([
    'status' => 'success',
    'products' => $productos
]);
exit();
?>