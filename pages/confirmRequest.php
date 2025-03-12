<?php
require('../fpdf/fpdf.php');
require_once "../controllers/mainController.php"; // Asegúrate de incluir tu controlador de la base de datos

session_start();

if (!isset($_SESSION['INV']) || count($_SESSION['INV']) == 0 || !isset($_POST['idSucursal'])) {
    echo 'No hay productos en el carrito.';
    exit();
}

$sucursal_id = $_POST['idSucursal'];

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

// Salvar o enviar el PDF
$pdf->Output('I', 'Solicitud_Productos.pdf'); // Generar PDF en pantalla

// Conexión a la base de datos
$conn = conexion(); // Asumiendo que tienes una función de conexión a la BD.

// Registrar los movimientos y reducir las cantidades en inventario
foreach ($_SESSION['INV'] as $item) {
    // Registrar el movimiento en la tabla de movimientos
    $stmt = $conn->prepare("INSERT INTO MovimientosInventario (SucursalID, ProductoID, TipoMovimiento, Cantidad, FechaMovimiento, PrecioFinal, UsuarioID) 
                            VALUES (:sucursalID, :productoID, 'Salida', :cantidad, NOW(), :precioFinal, :usuarioID)");

    $precioFinales = $item['precio'] * (1 + 0.16);

    $stmt->execute([
        ':sucursalID' => $sucursal_id,
        ':productoID' => $item['producto'],
        ':cantidad' => $item['cantidad'],
        ':precioFinal' => $precioFinales,
        ':usuarioID' => $_SESSION['id'], // Asumiendo que tienes un ID de usuario en sesión
    ]);

    // Reducir la cantidad del producto en el inventario
    $updateStmt = $conn->prepare("UPDATE Productos SET Cantidad = Cantidad - :cantidad WHERE ProductoID = :productoID");
    $updateStmt->execute([
        ':cantidad' => $item['cantidad'],
        ':productoID' => $item['producto']
    ]);
}

// Limpiar la sesión después de procesar la solicitud
unset($_SESSION['INV']);

//echo "<script>window.setTimeout(function() { window.location = 'index.php?page=requestProducts' }, 100);</script>";
header("Location: index.php?page=requestProducts");
exit();
?>
