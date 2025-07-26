<?php
session_start();
header('Content-Type: application/json');
require_once '../../controllers/mainController.php';
$conn = conexion();

$userId = $_SESSION['id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $idSucursal = $data['idSucursal'] ?? null;
    $fechaEntrega = $data['fechaEntrega'] ?? null;

    if (!$userId || !$idSucursal || !$fechaEntrega) {
        echo json_encode(['status' => 'error', 'message' => 'Datos de sesión inválidos']);
        exit();
    }

    $cartStmt = $conn->prepare("SELECT * FROM CarritoSolicitudes WHERE UsuarioID = ?");
    $cartStmt->execute([$userId]);
    $cart = $cartStmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($cart) === 0) {
        echo json_encode(['status' => 'error', 'message' => 'No hay productos en el carrito']);
        exit();
    }

    $conn->beginTransaction();
    try {
        $fecha = date('Ymd');
        $random_number = rand(100, 999);
        $comandaID = 'COM-' . $fecha . '-' . $idSucursal . '-' . $random_number;

        foreach ($cart as $item) {
            // Verificar stock
            $consultaStock = $conn->prepare("SELECT Cantidad FROM Productos WHERE ProductoID = ?");
            $consultaStock->execute([$item['ProductoID']]);
            $stockDisponible = $consultaStock->fetchColumn();

            if ($item['Cantidad'] > $stockDisponible) {
                $conn->rollBack();
                echo json_encode(['status' => 'error', 'message' => 'El stock del producto "' . $item['NombreProducto'] . '" no es suficiente.']);
                exit();
            }
        }

        foreach ($cart as $item) {
            $precioFinal = $item['PrecioUnitario'] * 1.16;
            $stmt = $conn->prepare("INSERT INTO MovimientosInventario (ComandaID, SucursalID, ProductoID, TipoMovimiento, Cantidad, FechaMovimiento, PrecioFinal, UsuarioID, Status, FechaDelivery) VALUES (?, ?, ?, 'Salida', ?, NOW(), ?, ?, 'Abierto', ?)");
            $stmt->execute([
                $comandaID,
                $idSucursal,
                $item['ProductoID'],
                $item['Cantidad'],
                $precioFinal * $item['Cantidad'],
                $userId,
                $fechaEntrega
            ]);

            $updateStmt = $conn->prepare("UPDATE Productos SET Cantidad = Cantidad - ? WHERE ProductoID = ?");
            $updateStmt->execute([$item['Cantidad'], $item['ProductoID']]);
        }

        // Limpiar carrito
        $delStmt = $conn->prepare("DELETE FROM CarritoSolicitudes WHERE UsuarioID = ?");
        $delStmt->execute([$userId]);

        $conn->commit();
        echo json_encode(['status' => 'success', 'message' => 'Solicitud confirmada correctamente', 'comandaID' => $comandaID]);
    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        echo json_encode(['status' => 'error', 'message' => 'Error en el proceso: ' . $e->getMessage()]);
    }
    exit();
}