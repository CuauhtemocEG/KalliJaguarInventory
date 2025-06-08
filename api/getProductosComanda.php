<?php
require_once '../controllers/mainController.php';

header('Content-Type: application/json');

if (!isset($_GET['comanda_id']) || empty($_GET['comanda_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Falta el parámetro comanda_id']);
    exit;
}

$comandaID = $_GET['comanda_id'];

try {
    $db = conexion();
    $stmt = $db->prepare("
        SELECT 
        mi.MovimientoID,
        mi.ProductoID,
        mi.Cantidad,
        mi.PrecioFinal,
        p.UPC,
        p.Nombre,
        p.Descripcion,
        p.Tipo,
        p.PrecioUnitario,
        p.image
        FROM MovimientosInventario mi
        JOIN Productos p ON mi.ProductoID = p.ProductoID
        WHERE mi.ComandaID = :comandaID
        AND mi.TipoMovimiento = 'Salida'
    ");
    $stmt->bindParam(':comandaID', $comandaID);
    $stmt->execute();

    $movimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$movimientos) {
        echo json_encode(['success' => false, 'error' => 'No se encontraron productos para esta comanda']);
        exit;
    }

    $productos = [];
    foreach ($movimientos as $mov) {
        $stmtProd = $db->prepare("SELECT Nombre FROM Productos WHERE ProductoID = :productoID");
        $stmtProd->bindParam(':productoID', $mov['ProductoID']);
        $stmtProd->execute();
        $producto = $stmtProd->fetch(PDO::FETCH_ASSOC);

        $productos[] = [
            'ID' => $mov['MovimientoID'],
            'ProductoID' => $mov['ProductoID'],
            'Nombre' => $producto['Nombre'] ?? 'N/D',
            'Cantidad' => $mov['Cantidad'],
            'PrecioFinal' => $mov['PrecioFinal'],
            'PrecioUnitario' => $mov['PrecioUnitario']
        ];
    }

    echo json_encode(['success' => true, 'productos' => $productos]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error en la base de datos: ' . $e->getMessage()]);
}
