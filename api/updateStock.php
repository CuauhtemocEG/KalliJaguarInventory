<?php
require '../controllers/mainController.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit;
}

$codigo = limpiar_cadena($_POST['codigo'] ?? '');
$nuevo_stock = $_POST['nuevo_stock'];

if (!$codigo || $nuevo_stock < 0) {
    echo json_encode(['status' => 'error', 'message' => 'Datos inválidos']);
    exit;
}

$pdo = conexion();

$stmt = $pdo->prepare("SELECT Cantidad FROM Productos WHERE UPC = :codigo");
$stmt->execute([':codigo' => $codigo]);
$producto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$producto) {
    echo json_encode(['status' => 'error', 'message' => 'Producto no encontrado']);
    exit;
}

$stock_actual = $producto['Cantidad'];

$upd = $pdo->prepare("UPDATE Productos SET Cantidad = :nuevo WHERE UPC = :codigo");
if ($upd->execute([':nuevo' => $nuevo_stock, ':codigo' => $codigo])) {
    $log = $pdo->prepare("INSERT INTO Logs_stock (UPC, StockBefore, StockAfter) VALUES (:codigo, :anterior, :nuevo)");
    $log->execute([
        ':codigo' => $codigo,
        ':anterior' => $stock_actual,
        ':nuevo' => $nuevo_stock
    ]);

    echo json_encode(['status' => 'ok', 'message' => '¡Stock actualizado exitosamente!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Error al actualizar stock']);
}
