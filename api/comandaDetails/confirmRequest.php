<?php
session_start();
header('Content-Type: application/json');

// Permitir recibir datos tanto por JSON como por POST tradicional
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST)) {
  $input = json_decode(file_get_contents('php://input'), true);
  if (is_array($input)) {
    $_POST = $input;
  }
}

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
$fechaObj = DateTime::createFromFormat('Y-m-d', $fechaDelivery); // Formato ISO desde la app
if (!$fechaObj) {
  $fechaObj = DateTime::createFromFormat('d/m/Y', $fechaDelivery);
  if (!$fechaObj) {
    echo json_encode(['status' => 'error', 'message' => 'Formato de fecha inválido.']);
    exit();
  }
}
$fechaMysql = $fechaObj->format('Y-m-d');
$fecha = date('Ymd');
$random_number = rand(100, 999);
$comandaID = 'COM-' . $fecha . '-' . $_POST['idSucursal'] . '-' . $random_number;
$idUser = $_POST['id'];
$sucursal_id = $_POST['idSucursal'];

// Obtener productos del carrito desde la base de datos
$conn = conexion();
$stmt = $conn->prepare("SELECT * FROM CarritoSolicitudes WHERE UsuarioID = ?");
$stmt->execute([$idUser]);
$carrito = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$carrito || count($carrito) === 0) {
  echo json_encode(['status' => 'error', 'message' => 'No hay productos en el carrito.']);
  exit();
}

try {
  $conn->beginTransaction();

  // Verificar stock
  foreach ($carrito as $item) {
    $consultaStock = $conn->prepare("SELECT Cantidad FROM Productos WHERE ProductoID = ?");
    $consultaStock->execute([$item['ProductoID']]);
    $stockDisponible = $consultaStock->fetchColumn();
    if ($item['Cantidad'] > $stockDisponible) {
      $conn->rollBack();
      echo json_encode(['status' => 'error', 'message' => 'El stock del producto "' . $item['NombreProducto'] . '" no es suficiente.']);
      exit();
    }
  }

  // Insertar movimientos y actualizar stock
  foreach ($carrito as $item) {
    $precioFinal = $item['PrecioUnitario'] * 1.16;
    $stmt = $conn->prepare("INSERT INTO MovimientosInventario 
        (ComandaID, SucursalID, ProductoID, TipoMovimiento, Cantidad, FechaMovimiento, PrecioFinal, UsuarioID, Status, FechaDelivery) 
        VALUES (?, ?, ?, 'Salida', ?, NOW(), ?, ?, 'Abierto', ?)");
    $stmt->execute([
      $comandaID,
      $sucursal_id,
      $item['ProductoID'],
      $item['Cantidad'],
      $precioFinal * $item['Cantidad'],
      $idUser,
      $fechaMysql
    ]);
    $updateStmt = $conn->prepare("UPDATE Productos SET Cantidad = Cantidad - ? WHERE ProductoID = ?");
    $updateStmt->execute([$item['Cantidad'], $item['ProductoID']]);
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
  $pdf->SetFont('Arial', '', 8);
  $pdf->Cell(180, 10, utf8_decode("Fecha de entrega: $fechaLarga"), 0, 1, 'L');
  $pdf->Ln(5);
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
  foreach ($carrito as $item) {
    $unidad = $item['Tipo'] == "Pesable"
      ? ($item['Cantidad'] >= 1.0 ? 'Kg' : 'grs')
      : 'Unidad(es)';
    $cantidad = $item['Tipo'] == "Pesable"
      ? ($item['Cantidad'] >= 1.0 ? number_format($item['Cantidad'], 2) : number_format($item['Cantidad'], 3))
      : number_format($item['Cantidad'], 0);

    $totalItem = ($item['PrecioUnitario'] * 1.16) * $item['Cantidad'];
    $totalGeneral += $totalItem;

    $pdf->Cell(60, 10, utf8_decode(ucwords(strtolower($item['NombreProducto']))), 1, 0, 'C');
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

// Email
$productosHTML = '';
$totalGenerals = 0;
foreach ($carrito as $items) {
  $unidadesResult = '';
  $quantityRes = '';

  if ($items['Tipo'] == "Pesable") {
    if ($items['Cantidad'] >= 1.0) {
      $unidadesResult = 'Kg';
      $quantityRes = number_format($items['Cantidad'], 2, '.', '');
    } else {
      $unidadesResult = 'grs';
      $quantityRes = number_format($items['Cantidad'], 3, '.', '');
    }
  } else {
    $unidadesResult = 'Unidad(es)';
    $quantityRes = number_format($items['Cantidad'], 0, '.', '');
  }

  $totalItem = ($items['PrecioUnitario'] * 1.16) * $items['Cantidad'];
  $totalGenerals += $totalItem;

  $productosHTML .= '<li>' . htmlspecialchars($items['NombreProducto']) . ' - Cantidad: ' . $quantityRes . ' ' . $unidadesResult . '</li>';
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
            <tr>
              <td align="left" width="50%" style="padding:20px;">
                <img src="https://stagging.kallijaguar-inventory.com/img/LogoBlack.png" alt="Logo" width="120" style="display:block;">
              </td>
              <td align="right" width="50%" style="padding:20px; color:#ffce17; font-size:14px;">
                <strong>Comanda #: ' . $comandaID . '</strong>
              </td>
            </tr>
            <tr>
              <td colspan="2" style="padding:20px;">
                <p style="font-size:14px; color:#ffffff; margin:0 0 10px 0;">¡Tu pedido ha sido recibido exitosamente!</p>
                <p style="font-size:14px; color:#ffffff; margin:0;">Fecha de entrega: <strong>' . utf8_decode($fechaLarga) . '</strong></p>
                <p style="font-size:14px; color:#ffffff; margin:0;">Adjunto se encontrará el PDF correspondiente a la comanda generada.</p>
              </td>
            </tr>
            <tr>
              <td colspan="2" style="background-color:#2a2a2a; padding:20px;">
                <p style="font-size:14px; color:#ffce17; margin:0 0 10px 0;"><strong>Productos solicitados:</strong></p>
                <ul style="font-size:14px; color:#ffffff; padding-left:20px; margin:0;">
                  ' . $productosHTML . '
                </ul>
              </td>
            </tr>
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

$mail = new PHPMailer(true);
try {
  $mail->isSMTP();
  $mail->CharSet = 'UTF-8';
  $mail->Host = 'smtp.titan.email';
  $mail->SMTPDebug = 2;
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

// Limpiar carrito en la base de datos
$conn->prepare("DELETE FROM CarritoSolicitudes WHERE UsuarioID = ?")->execute([$idUser]);

echo json_encode([
  'status' => 'success',
  'message' => 'Solicitud confirmada correctamente.',
  'comanda' => $comandaID
]);
setcookie("persist_cart", "", time() - 3600, "/");