<?php
header('Content-Type: application/json');
require_once '../../controllers/mainController.php';

$query = isset($_GET['query']) ? $_GET['query'] : '';
$conn = conexion();

$sql = "SELECT ProductoID, Nombre, Descripcion, PrecioUnitario, Cantidad, Tipo, CategoriaID, image 
        FROM Productos 
        WHERE Nombre LIKE :query OR UPC LIKE :query 
        ORDER BY Nombre";
$stmt = $conn->prepare($sql);
$stmt->execute([':query' => "%$query%"]);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['status' => 'success', 'productos' => $productos]);