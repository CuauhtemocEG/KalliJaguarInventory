<?php
require_once '../controllers/mainController.php';

if (!isset($_GET['comanda_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Falta el parámetro comanda_id']);
    exit;
}

$comandaID = $_GET['comanda_id'];

try {
    $db = conexion();
    $stmt = $db->prepare("
        SELECT mi.ID, p.Nombre, mi.Cantidad, mi.PrecioFinal
        FROM MovimientosInventario mi
        JOIN Productos p ON mi.ProductoID = p.ProductoID
        WHERE mi.ComandaID = :comandaID
    ");
    $stmt->bindParam(':comandaID', $comandaID);
    $stmt->execute();

    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($productos);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
}
