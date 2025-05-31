<?php
session_start();
header('Content-Type: application/json');
// Validar que los archivos requeridos existen antes de incluirlos
$requiredFiles = [
  '../../fpdf/fpdf.php',
  '../../controllers/mainController.php',
  '../../PHPMailer/src/PHPMailer.php',
  '../../PHPMailer/src/SMTP.php',
  '../../PHPMailer/src/Exception.php',
];

foreach ($requiredFiles as $file) {
  if (!file_exists($file)) {
    echo json_encode(['status' => 'error', 'message' => "Archivo no encontrado: $file"]);
    exit();
  }
}

require('../../fpdf/fpdf.php');
require_once "../../controllers/mainController.php";
require '../../PHPMailer/src/PHPMailer.php';
require '../../PHPMailer/src/SMTP.php';
require '../../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function fechaEnEspañol($fechaObj) {
  $meses = [
    '01' => 'enero', '02' => 'febrero', '03' => 'marzo', '04' => 'abril',
    '05' => 'mayo', '06' => 'junio', '07' => 'julio', '08' => 'agosto',
    '09' => 'septiembre', '10' => 'octubre', '11' => 'noviembre', '12' => 'diciembre'
  ];
  $dias = [
    'Monday' => 'lunes', 'Tuesday' => 'martes', 'Wednesday' => 'miércoles',
    'Thursday' => 'jueves', 'Friday' => 'viernes', 'Saturday' => 'sábado', 'Sunday' => 'domingo'
  ];

  $diaSemana = $dias[$fechaObj->format('l')];
  $dia = $fechaObj->format('j');
  $mes = $meses[$fechaObj->format('m')];
  $anio = $fechaObj->format('Y');

  return ucfirst("$diaSemana $dia de $mes del $anio");
}

// Validaciones de entrada
if (!isset($_SESSION['INV']) || !is_array($_SESSION['INV']) || count($_SESSION['INV']) === 0) {
  echo json_encode(['status' => 'error', 'message' => 'No hay productos en el carrito.']);
  exit();
}

if (!isset($_POST['idSucursal']) || !isset($_POST['id'])) {
  echo json_encode(['status' => 'error', 'message' => 'Datos de sesión no válidos.']);
  exit();
}

if (empty($_POST['fecha'])) {
  echo json_encode(['status' => 'error', 'message' => 'Fecha de entrega vacía, selecciona una.']);
  exit();
}

// Validar y convertir fecha
$fechaDelivery = $_POST['fecha'];
$fechaObj = DateTime::createFromFormat('d/m/Y', $fechaDelivery);
if (!$fechaObj) {
  echo json_encode(['status' => 'error', 'message' => 'Formato de fecha inválido.']);
  exit();
}

$fechaMysql = $fechaObj->format('Y-m-d');
$fecha = date('Ymd');
$random_number = rand(100, 999);
$comandaID = 'COM-' . $fecha . '-' . $_POST['idSucursal'] . '-' . $random_number;
$idUser = $_POST['id'];
$sucursal_id = $_POST['idSucursal'];

try {
  $conn = conexion();
  $conn->beginTransaction();

  // Verificar stock
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

  // Insertar movimientos
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

  $conn->commit();
} catch (Exception $e) {
  if ($conn->inTransaction()) {
    $conn->rollBack();
  }
  echo json_encode(['status' => 'error', 'message' => 'Error en el proceso: ' . $e->getMessage()]);
  exit();
}

// Obtener nombres
$nameSucursal = $conn->query("SELECT nombre FROM Sucursales WHERE SucursalID = '$sucursal_id'")->fetchColumn();
$nameUser = $conn->query("SELECT Nombre FROM Usuarios WHERE UsuarioID = '$idUser'")->fetchColumn();

// Fecha larga (formato alternativo si IntlDateFormatter falla)
$fechaLarga = fechaEnEspañol($fechaObj);

// Generar PDF
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
  $pdf->Ln(10);
  $pdf->SetFont('Arial', '', 9);
  $pdf->Cell(190, 10, utf8_decode("Fecha de entrega: $fechaLarga"), 0, 1, 'L');
  $pdf->Ln(20);
  $pdf->SetFont('Arial', '', 8);
  $pdf->MultiCell(180, 5, utf8_decode('A continuación se debe capturar las observaciones del producto al ser recepcionado por el solicitante, verificar que todos los productos solicitados están siendo entregados y contar con 3 copias de este documento para cada una de las áreas.'), 0, 'C');
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
  echo json_encode(['status' => 'error', 'message' => 'Error al generar PDF: ' . $e->getMessage()]);
  exit();
}

// Enviar email (puedes añadir validación aquí también)
$productosHTML = '';
$totalGenerals = 0;

foreach ($_SESSION['INV'] as $items) {
  $unidadesResult = '';
  $quantityRes = '';

  if ($items['tipo'] == "Pesable") {
    if ($items['cantidad'] >= 1.0) {
      $unidadesResult = 'Kg';
      $quantityRes = number_format($items['cantidad'], 2, '.', '');
    } else {
      $unidadesResult = 'grs';
      $quantityRes = number_format($items['cantidad'], 3, '.', '');
    }
  } else {
    $unidadesResult = 'Unidad(es)';
    $quantityRes = number_format($items['cantidad'], 0, '.', '');
  }

  $totalItem = ($items['precio'] * 1.16) * $items['cantidad'];
  $totalGenerals += $totalItem;

  $productosHTML .= '<li>' . htmlspecialchars($items['nombre']) . ' - Cantidad: ' . $quantityRes . ' ' . $unidadesResult . '</li>';
}

$correoBody = '
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>Comanda</title>
  </head>
  <body>
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0; padding:0; background-color:#000000;">
      <tr>
        <td align="center">
          <table width="450" cellpadding="0" cellspacing="0" border="0" style="background-color:#000000; border:2px solid #ffb900;">
            <!-- Encabezado con logo y número de comanda -->
            <tr>
              <td align="left" width="50%" style="padding:20px;">
                <img src="https://stagging.kallijaguar-inventory.com/img/LogoBlack.png" alt="Logo" width="120" style="display:block;">
              </td>
              <td align="right" width="50%" style="padding:20px; color:#ffce17; font-size:14px;">
                <strong>Comanda #: ' . $comandaID . '</strong>
              </td>
            </tr>

            <!-- Mensaje principal -->
            <tr>
              <td colspan="2" style="padding:20px;">
                <p style="font-size:14px; color:#ffffff; margin:0 0 10px 0;">¡Tu pedido ha sido recibido exitosamente!</p>
                <p style="font-size:14px; color:#ffffff; margin:0;">Fecha de entrega: <strong>' . utf8_decode($fechaLarga) . '</strong></p>
                <p style="font-size:14px; color:#ffffff; margin:0;">Adjunto se encontrará el PDF correspondiente a la comanda generada.</p>
              </td>
            </tr>

            <!-- Lista de productos -->
            <tr>
              <td colspan="2" style="background-color:#2a2a2a; padding:20px;">
                <p style="font-size:14px; color:#ffce17; margin:0 0 10px 0;"><strong>Productos solicitados:</strong></p>
                <ul style="font-size:14px; color:#ffffff; padding-left:20px; margin:0;">
                  ' . $productosHTML . '
                </ul>
              </td>
            </tr>

            <!-- Footer -->
            <tr>
              <td colspan="2" style="font-size:12px; color:#ffffff; text-align:center; padding:20px;">
                Si tienes alguna duda, contacta al administrador del sitio.
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </body>
</html>';

// Enviar correo
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

$_SESSION['INV'] = []; // limpiar carrito

echo json_encode([
  'status' => 'success',
  'message' => 'Solicitud confirmada correctamente.',
  'comanda' => $comandaID
]);
