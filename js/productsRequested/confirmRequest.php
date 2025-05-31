<?php
session_start();
require('../../fpdf/fpdf.php');
require_once "../../controllers/mainController.php";
require '../../PHPMailer/src/PHPMailer.php';
require '../../PHPMailer/src/SMTP.php';
require '../../PHPMailer/src/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once "../../includes/contactabilityController.php";

// Validaciones iniciales
if (!isset($_SESSION['INV']) || !is_array($_SESSION['INV']) || count($_SESSION['INV']) === 0) {
  echo json_encode(['status' => 'error', 'message' => 'No hay productos en el carrito.']);
  exit();
}
if (!isset($_POST['idSucursal']) || !isset($_POST['id'])) {
  echo json_encode(['status' => 'error', 'message' => 'Datos de sesión no válidos.']);
  exit();
}
if (!isset($_POST['fecha'])) {
  echo json_encode(['status' => 'error', 'message' => 'Fecha de entrega vacía, selecciona una.']);
  exit();
}

// Inicializar variables
$idUser = $_POST['id'];
$sucursal_id = $_POST['idSucursal'];
$fechaDelivery = $_POST['fecha'];
$fechaMysql = DateTime::createFromFormat('d/m/Y', $fechaDelivery)->format('Y-m-d');
$fechaObj = new DateTime($fechaMysql);
$formatter = new IntlDateFormatter('es_ES', IntlDateFormatter::FULL, IntlDateFormatter::NONE, null, null, "EEEE d 'de' MMMM 'del' yyyy");
setlocale(LC_TIME, 'es_ES.UTF-8');
$fechaLarga = strftime('%A %e de %B del %Y', $fechaObj->getTimestamp());
//$fechaLarga = ucfirst($formatter->format($fechaObj));
$fecha = date('Ymd');
$random_number = rand(100, 999);
$comandaID = 'COM-' . $fecha . '-' . $sucursal_id . '-' . $random_number;

try {
  $conn = conexion();
  $conn->beginTransaction();

  // Paso 1: Validar stock de todos los productos
  foreach ($_SESSION['INV'] as $item) {
    $consultaStock = $conn->prepare("SELECT Cantidad FROM Productos WHERE ProductoID = ?");
    $consultaStock->execute([$item['producto']]);
    $stockDisponible = $consultaStock->fetchColumn();

    if ($item['cantidad'] > $stockDisponible) {
      $conn->rollBack();
      echo json_encode(['status' => 'error', 'message' => 'El stock del producto "' . $item['nombre'] . '" no es suficiente.']);
      exit();
    }
  }

  // Paso 2: Registrar movimientos y actualizar stock
  foreach ($_SESSION['INV'] as $item) {
    $precioFinal = $item['precio'] * 1.16;
    $stmt = $conn->prepare("INSERT INTO MovimientosInventario 
        (ComandaID, SucursalID, ProductoID, TipoMovimiento, Cantidad, FechaMovimiento, PrecioFinal, UsuarioID, Status, FechaDelivery) 
        VALUES (?, ?, ?, 'Salida', ?, NOW(), ?, ?, 'Abierto', ?)");
    $stmt->execute([
      $comandaID,
      $sucursal_id,
      $item['producto'],
      $item['cantidad'],
      $precioFinal * $item['cantidad'],
      $idUser,
      $fechaMysql
    ]);

    $updateStmt = $conn->prepare("UPDATE Productos SET Cantidad = Cantidad - ? WHERE ProductoID = ?");
    $updateStmt->execute([$item['cantidad'], $item['producto']]);
  }

  // Commit si todo salió bien
  $conn->commit();

} catch (Exception $e) {
  if ($conn->inTransaction()) {
    $conn->rollBack();
  }
  echo json_encode(['status' => 'error', 'message' => 'Error en el proceso: ' . $e->getMessage()]);
  exit();
}

// Paso 3: Generar PDF
$nameSucursal = $conn->query("SELECT nombre FROM Sucursales WHERE SucursalID = '$sucursal_id'")->fetchColumn();
$nameUser = $conn->query("SELECT Nombre FROM Usuarios WHERE UsuarioID = '$idUser'")->fetchColumn();
$pdfPath = '../../documents/' . $comandaID . '.pdf';

try {
  $pdf = new FPDF();
  $pdf->AddPage();
  $pdf->Image('../../img/logo.png', 15, 15, 50);
  $pdf->SetFont('Arial', 'B', 8);
  $pdf->SetXY(70, 11);
  $pdf->Cell(60, 10, $nameSucursal, 1, 0, 'C');
  $pdf->SetXY(70, 21);
  $pdf->Cell(60, 10, 'Listado de Salida', 1, 0, 'C');
  $pdf->SetXY(130, 11);
  $pdf->Cell(60, 10, $comandaID, 1, 0, 'C');
  $pdf->SetXY(130, 21);
  $pdf->Cell(60, 10, $nameUser, 1, 0, 'C');
  $pdf->Ln(5);
  $pdf->SetFont('Arial', '', 9);
  $pdf->Cell(190, 10, utf8_decode("Fecha de entrega: $fechaLarga"), 0, 1, 'C');
  $pdf->Ln(20);
  $pdf->SetFont('Arial', '', 8);
  $pdf->MultiCell(180, 5, utf8_decode('A continuación se debe capturar las observaciones del producto al ser recepcionado...'), 0, 'C');
  $pdf->Ln(5);
  $pdf->SetFont('Arial', 'B', 8);
  $pdf->Cell(190, 10, utf8_decode('Listado de productos solicitados a Almacén:'), 0, 1, 'L');
  $pdf->SetFont('Arial', 'B', 9);
  $pdf->Cell(60, 10, 'Nombre del Producto/Materia Prima', 1, 0, 'C');
  $pdf->Cell(40, 10, 'Cantidad', 1, 0, 'C');
  $pdf->Cell(40, 10, 'Precio', 1, 0, 'C');
  $pdf->Cell(40, 10, 'Observaciones', 1, 0, 'C');
  $pdf->Ln();

  $pdf->SetFont('Arial', '', 9);
  $totalGeneral = 0;
  foreach ($_SESSION['INV'] as $item) {
    $unidad = $item['tipo'] == "Pesable"
      ? ($item['cantidad'] >= 1.0 ? 'Kg' : 'grs')
      : 'Unidad(es)';
    $cantidad = $item['tipo'] == "Pesable"
      ? ($item['cantidad'] >= 1.0 ? number_format($item['cantidad'], 2) : number_format($item['cantidad'], 3))
      : number_format($item['cantidad'], 0);

    $totalItem = ($item['precio'] * 1.16) * $item['cantidad'];
    $totalGeneral += $totalItem;

    $pdf->Cell(60, 10, $item['nombre'], 1, 0, 'C');
    $pdf->Cell(40, 10, $cantidad . ' ' . $unidad, 1, 0, 'C');
    $pdf->Cell(40, 10, '$' . number_format($totalItem, 2), 1, 0, 'C');
    $pdf->Cell(40, 10, '', 1);
    $pdf->Ln();
  }

  $pdf->Cell(100, 10, 'Total:', 1, 0, 'L');
  $pdf->Cell(40, 10, '$' . number_format($totalGeneral, 2), 1, 0, 'C');
  $pdf->Ln(20);
  $pdf->Cell(90, 10, 'Firma', 0, 0, 'C');
  $pdf->Cell(90, 10, 'Firma', 0, 0, 'C');
  $pdf->Ln(10);
  $pdf->Cell(90, 10, $nameUser, 0, 0, 'C');
  $pdf->Cell(90, 10, 'Mauricio Dominguez', 0, 0, 'C');
  $pdf->Output('F', $pdfPath);
} catch (Exception $e) {
  echo json_encode(['status' => 'error', 'message' => 'Error al generar el PDF: ' . $e->getMessage()]);
  exit();
}

// Paso 4: Preparar correo
$productosHTML = '';
foreach ($_SESSION['INV'] as $items) {
  $unidad = $items['tipo'] == "Pesable"
    ? ($items['cantidad'] >= 1.0 ? 'Kg' : 'grs')
    : 'Unidad(es)';
  $cantidad = $items['tipo'] == "Pesable"
    ? ($items['cantidad'] >= 1.0 ? number_format($items['cantidad'], 2) : number_format($items['cantidad'], 3))
    : number_format($items['cantidad'], 0);

  $productosHTML .= '<li>' . htmlspecialchars($items['nombre']) . ' - Cantidad: ' . $cantidad . ' ' . $unidad . '</li>';
}

$correoBody = '...'; // (Aquí puedes dejar el mismo HTML que ya tenías)

$mail = new PHPMailer(true);
try {
  $mail->isSMTP();
  $mail->CharSet = 'UTF-8';
  $mail->Host = 'smtp.titan.email';
  $mail->SMTPAuth = true;
  $mail->Username = 'info@stagging.kallijaguar-inventory.com';
  $mail->Password = 'KalliJaguar2025@';
  $mail->SMTPSecure = 'ssl';
  $mail->Port = 465;

  $mail->setFrom('info@stagging.kallijaguar-inventory.com', 'Información Kalli Jaguar');
  $mail->addAddress('cencarnacion@stagging.kallijaguar-inventory.com');
  $mail->addAttachment($pdfPath);
  $mail->isHTML(true);
  $mail->Subject = 'Confirmación de tu pedido: ' . $comandaID;
  $mail->Body = $correoBody;

  $mail->send();
} catch (Exception $e) {
  echo json_encode(['status' => 'error', 'message' => 'Error al enviar correo: ' . $mail->ErrorInfo]);
  exit();
}

unset($_SESSION['INV']);
echo json_encode(['status' => 'success', 'message' => 'La comanda fue procesada correctamente.']);
exit();
