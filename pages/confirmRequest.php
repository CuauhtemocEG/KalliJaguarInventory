<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require('./fpdf/fpdf.php');
require_once "./controllers/mainController.php"; // Asegúrate de incluir tu controlador de la base de datos

if (!isset($_SESSION['INV']) || !is_array($_SESSION['INV']) || count($_SESSION['INV']) == 0 || !isset($_POST['idSucursal'])) {
    echo 'No hay productos en el carrito.';
    exit();
}

$sucursal_id = $_POST['idSucursal'];

// Generar un ID único para la comanda basado en la fecha, sucursal y un número aleatorio
$fecha = date('Ymd'); // Formato de fecha: AñoMesDía (ej. 20250311)
$random_number = rand(100, 999); // Número aleatorio de 3 dígitos
$comandaID = $fecha . '-' . $sucursal_id . '-' . $random_number; // Ejemplo: 20250311-1-235

// Debugging output
echo "<script>console.log(" . json_encode($comandaID) . ");</script>";

try {
    // Crear una instancia de la clase FPDF
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 12);

    // Título
    $pdf->Cell(200, 10, 'Lista de Productos Solicitados', 0, 1, 'C');

    // Encabezado de la tabla
    $pdf->Cell(50, 10, 'Producto', 1);
    $pdf->Cell(40, 10, 'Precio', 1);
    $pdf->Cell(40, 10, 'Cantidad', 1);
    $pdf->Cell(40, 10, 'Total', 1);
    $pdf->Ln();

    // Datos de los productos
    $totalGeneral = 0;
    foreach ($_SESSION['INV'] as $item) {
        $totalItem = $item['precio'] * $item['cantidad'];
        $totalGeneral += $totalItem;

        $pdf->Cell(50, 10, $item['nombre'], 1);
        $pdf->Cell(40, 10, '$' . number_format($item['precio'], 2), 1);
        $pdf->Cell(40, 10, $item['cantidad'], 1);
        $pdf->Cell(40, 10, '$' . number_format($totalItem, 2), 1);
        $pdf->Ln();
    }

    // Total
    $pdf->Cell(130, 10, 'Total', 1);
    $pdf->Cell(40, 10, '$' . number_format($totalGeneral, 2), 1);

    $pdfPath = './documents/request-'.$comandaID.'.pdf';
    // Salvar o enviar el PDF
    $pdf->Output('F', $pdfPath, true); // Generar PDF en pantalla
} catch (Exception $e) {
    echo "Error al generar PDF: " . $e->getMessage();
}
// Conexión a la base de datos
$conn = conexion(); // Asumiendo que tienes una función de conexión a la BD.

// Registrar los movimientos y reducir las cantidades en inventario
foreach ($_SESSION['INV'] as $item) {
    // Registrar el movimiento en la tabla de movimientos
    $stmt = $conn->prepare("INSERT INTO MovimientosInventario (ComandaID, SucursalID, ProductoID, TipoMovimiento, Cantidad, FechaMovimiento, PrecioFinal, UsuarioID) 
                            VALUES (:comandaID,:sucursalID, :productoID, 'Salida', :cantidad, NOW(), :precioFinal, :usuarioID)");

    $precioFinales = $item['precio'] * (1 + 0.16);

    try {
        $stmt->execute([
            ':comandaID' => $comandaID,
            ':sucursalID' => $sucursal_id,
            ':productoID' => $item['producto'],
            ':cantidad' => $item['cantidad'],
            ':precioFinal' => $precioFinales,
            ':usuarioID' => $_SESSION['id']
        ]);
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }

    // Reducir la cantidad del producto en el inventario
    $updateStmt = $conn->prepare("UPDATE Productos SET Cantidad = Cantidad - :cantidad WHERE ProductoID = :productoID");
    $updateStmt->execute([
        ':cantidad' => $item['cantidad'],
        ':productoID' => $item['producto']
    ]);
}

// Limpiar la sesión después de procesar la solicitud
unset($_SESSION['INV']);
echo "<script>window.setTimeout(function() { window.location = 'index.php?page=showPDFp&pdf=" . urlencode($pdfPath)."' }, 100);</script>";
exit();
