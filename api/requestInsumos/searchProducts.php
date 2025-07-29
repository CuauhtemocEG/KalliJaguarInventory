<?php
header('Content-Type: application/json');
require_once '../../controllers/mainController.php';

$query = isset($_GET['query']) ? $_GET['query'] : '';
$conn = conexion();

$sql = "SELECT 
            p.ProductoID, 
            p.UPC, 
            p.Nombre, 
            p.Descripcion, 
            p.PrecioUnitario, 
            p.Cantidad, 
            p.Tipo, 
            c.Nombre AS nombreCategoria, 
            p.image,
            COALESCE(SUM(mi.Cantidad), 0) AS popularidad
        FROM Productos p
        INNER JOIN Categorias c ON p.CategoriaID = c.CategoriaID
        LEFT JOIN MovimientosInventario mi ON mi.ProductoID = p.ProductoID
            AND mi.TipoMovimiento = 'Salida' -- o el tipo que corresponda a ventas/solicitudes
        WHERE p.Nombre LIKE :query OR p.UPC LIKE :query 
        GROUP BY p.ProductoID
        ORDER BY popularidad DESC, p.Nombre ASC";
$stmt = $conn->prepare($sql);
$stmt->execute([':query' => "%$query%"]);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['status' => 'success', 'productos' => $productos]);