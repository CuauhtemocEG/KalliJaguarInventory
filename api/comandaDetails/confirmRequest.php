<?php
session_name("INV");
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST)) {
  $input = json_decode(file_get_contents('php://input'), true);
  if (is_array($input)) {
    $_POST = $input;
  }
}

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

if (!isset($_POST['idSucursal']) || !isset($_POST['id'])) {
  echo json_encode(['status' => 'error', 'message' => 'Datos de sesión no válidos.']);
  exit();
}
if (empty($_POST['fecha'])) {
  echo json_encode(['status' => 'error', 'message' => 'Fecha de entrega vacía, selecciona una.']);
  exit();
}

$fechaDelivery = $_POST['fecha'];
$fechaObj = DateTime::createFromFormat('Y-m-d', $fechaDelivery);
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

$nameSucursal = $conn->query("SELECT nombre FROM Sucursales WHERE SucursalID = '$sucursal_id'")->fetchColumn();
$nameUser = $conn->query("SELECT Nombre FROM Usuarios WHERE UsuarioID = '$idUser'")->fetchColumn();
$fechaLarga = fechaEnEspañol($fechaObj);

$pdfPath = '../../documents/' . $comandaID . '.pdf';

ini_set('memory_limit', '256M');

try {
  class FacturaPDF extends FPDF {
    
    function Header() {
        $logoPath = '../../img/logo.png';
        if (file_exists($logoPath) && filesize($logoPath) < 1000000) {
            try {
                $this->Image($logoPath, 10, 10, 30);
            } catch (Exception $e) {
            }
        }
        
        $this->SetFont('Arial', 'B', 14);
        $this->SetXY(120, 15);
        $this->Cell(80, 6, 'Kalli Jaguar', 0, 1, 'R');
        
        $this->SetFont('Arial', '', 10);
        $this->SetXY(120, 22);
        $this->Cell(80, 5, 'Sistema de Inventario', 0, 1, 'R');
        $this->SetXY(120, 27);
        $this->Cell(80, 5, 'info@kallijaguar-inventory.com', 0, 1, 'R');
        $this->SetXY(120, 32);
        $this->Cell(80, 5, 'Tel: +52 756 112 7119', 0, 1, 'R');
        
        $this->SetY(45);
        $this->SetDrawColor(255, 185, 0);
        $this->SetLineWidth(1);
        $this->Line(10, 45, 200, 45);
        
        $this->Ln(5);
    }
    
    function Footer() {
        $this->SetY(-25);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(128);
        $this->Cell(0, 5, utf8_decode('Este documento es una solicitud de productos generada automáticamente'), 0, 1, 'C');
        $this->Cell(0, 5, utf8_decode('Página ') . $this->PageNo(), 0, 0, 'C');
    }
  }

  $pdf = new FacturaPDF();
  $pdf->AddPage();
  
  $pdf->SetFont('Arial', 'B', 18);
  $pdf->SetTextColor(0, 0, 0);
  $pdf->Cell(0, 10, 'Solicitud de Productos', 0, 1, 'C');
  $pdf->Ln(5);
  
  $pdf->SetFont('Arial', 'B', 11);
  $pdf->SetFillColor(248, 248, 248);
  
  $pdf->Cell(95, 8, utf8_decode('Información de Solicitud'), 1, 0, 'C', true);
  $pdf->Cell(95, 8, utf8_decode('Información de Entrega'), 1, 1, 'C', true);
  
  $pdf->SetFont('Arial', '', 10);
  $pdf->Cell(25, 6, 'Folio:', 1, 0, 'L');
  $pdf->SetFont('Arial', 'B', 10);
  $pdf->Cell(70, 6, $comandaID, 1, 0, 'L');
  $pdf->SetFont('Arial', '', 10);
  $pdf->Cell(25, 6, 'Sucursal:', 1, 0, 'L');
  $pdf->SetFont('Arial', 'B', 10);
  $pdf->Cell(70, 6, utf8_decode($nameSucursal), 1, 1, 'L');
  
  $pdf->SetFont('Arial', '', 10);
  $pdf->Cell(25, 6, 'Solicitante:', 1, 0, 'L');
  $pdf->SetFont('Arial', 'B', 10);
  $pdf->Cell(70, 6, utf8_decode($nameUser), 1, 0, 'L');
  $pdf->SetFont('Arial', '', 10);
  $pdf->Cell(25, 6, 'Fecha:', 1, 0, 'L');
  $pdf->SetFont('Arial', 'B', 10);
  $pdf->Cell(70, 6, date('d/m/Y'), 1, 1, 'L');
  
  $pdf->SetFont('Arial', '', 10);
  $pdf->Cell(25, 6, 'Estado:', 1, 0, 'L');
  $pdf->SetFont('Arial', 'B', 10);
  $pdf->SetTextColor(0, 150, 0);
  $pdf->Cell(70, 6, 'Pendiente', 1, 0, 'L');
  $pdf->SetTextColor(0, 0, 0);
  $pdf->SetFont('Arial', '', 10);
  $pdf->Cell(25, 6, 'Entrega:', 1, 0, 'L');
  $pdf->SetFont('Arial', 'B', 10);
  $pdf->Cell(70, 6, utf8_decode($fechaLarga), 1, 1, 'L');
  
  $pdf->Ln(8);
  
  $pdf->SetFont('Arial', 'B', 11);
  $pdf->SetFillColor(255, 185, 0);
  $pdf->SetTextColor(0, 0, 0);
  
  $pdf->Cell(8, 10, '#', 1, 0, 'C', true);
  $pdf->Cell(90, 10, 'Producto', 1, 0, 'C', true);
  $pdf->Cell(30, 10, 'Cantidad', 1, 0, 'C', true);
  $pdf->Cell(30, 10, 'Precio Unit.', 1, 0, 'C', true);
  $pdf->Cell(32, 10, 'Subtotal', 1, 1, 'C', true);
  
  $pdf->SetFont('Arial', '', 9);
  $pdf->SetFillColor(255, 255, 255);
  $totalGeneral = 0;
  $contador = 1;
  
  foreach ($carrito as $item) {
    $unidad = $item['Tipo'] == "Pesable" 
        ? ($item['Cantidad'] >= 1.0 ? 'Kg' : 'g') 
        : 'Unidad(es)';
    
    $cantidadFormateada = $item['Tipo'] == "Pesable" 
        ? ($item['Cantidad'] >= 1.0 ? number_format($item['Cantidad'], 2) : number_format($item['Cantidad'], 3))
        : number_format($item['Cantidad'], 0);
    
    $precioConIVA = floatval($item['PrecioUnitario']) * 1.16;
    $subtotal = $precioConIVA * floatval($item['Cantidad']);
    $totalGeneral += $subtotal;
    
    $fill = ($contador % 2 == 0) ? true : false;
    $pdf->SetFillColor($fill ? 248 : 255, $fill ? 248 : 255, $fill ? 248 : 255);
    
    $nombreProducto = strlen($item['NombreProducto']) > 40 
        ? substr($item['NombreProducto'], 0, 37) . '...' 
        : $item['NombreProducto'];
    
    $pdf->Cell(8, 8, $contador, 1, 0, 'C', $fill);
    $pdf->Cell(90, 8, utf8_decode(ucwords(strtolower($nombreProducto))), 1, 0, 'L', $fill);
    $pdf->Cell(30, 8, $cantidadFormateada . ' ' . $unidad, 1, 0, 'C', $fill);
    $pdf->Cell(30, 8, '$' . number_format($precioConIVA, 2), 1, 0, 'R', $fill);
    $pdf->Cell(32, 8, '$' . number_format($subtotal, 2), 1, 1, 'R', $fill);
    
    $contador++;
    
    unset($item, $subtotal, $nombreProducto, $precioConIVA);
  }
  
  $pdf->SetFont('Arial', 'B', 10);
  $pdf->SetFillColor(240, 240, 240);
  $pdf->Cell(158, 8, 'TOTAL GENERAL:', 1, 0, 'R', true);
  $pdf->SetFont('Arial', 'B', 12);
  $pdf->SetTextColor(0, 100, 0);
  $pdf->Cell(32, 8, '$' . number_format($totalGeneral, 2), 1, 1, 'R', true);
  
  $pdf->SetTextColor(0, 0, 0);
  $pdf->Ln(8);
  
  $pdf->SetFont('Arial', 'B', 11);
  $pdf->Cell(0, 6, utf8_decode('AUTORIZACIÓN Y RECEPCIÓN'), 0, 1, 'C');
  $pdf->Ln(5);
  
  $pdf->SetFont('Arial', '', 10);
  $pdf->Cell(0, 5, utf8_decode('Confirmo que los productos listados han sido verificados y corresponden a la solicitud realizada.'), 0, 1, 'C');
  $pdf->Ln(5);
  
  $pdf->Cell(95, 20, '', 1, 0, 'C'); 
  $pdf->Cell(95, 20, '', 1, 1, 'C'); 

  $pdf->SetFont('Arial', 'B', 10);
  $pdf->Cell(95, 6, 'Solicitante', 0, 0, 'C');
  $pdf->Cell(95, 6, utf8_decode('Almacén'), 0, 1, 'C');
  
  $pdf->SetFont('Arial', '', 9);
  $pdf->Cell(95, 5, utf8_decode($nameUser), 0, 0, 'C');
  $pdf->Cell(95, 5, utf8_decode('Encargado de Logística'), 0, 1, 'C');
  
  $pdf->Output('F', $pdfPath);
  
  unset($pdf);
  if (function_exists('gc_collect_cycles')) {
      gc_collect_cycles();
  }
  
} catch (Exception $e) {
  echo json_encode(['status' => 'error', 'message' => 'Error al generar PDF: ' . $e->getMessage()]);
  exit();
}

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
                <p style="font-size:14px; color:#ffffff; margin:0;">Fecha de entrega: <strong>' . $fechaLarga . '</strong></p>
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
  $mail->SMTPAuth = true;
  $mail->Username = 'info@kallijaguar-inventory.com';
  $mail->Password = '{&<eXA[x$?_q\<N';
  $mail->SMTPSecure = 'ssl';
  $mail->Port = 465;

  $mail->setFrom('info@kallijaguar-inventory.com', 'Informacion Kalli Jaguar');
  $mail->addAddress('cencarnacion@kallijaguar-inventory.com');
  $mail->addCC('julieta.ramirez@kallijaguar-inventory.com');
  $mail->addCC('miguel.loaeza@kallijaguar-inventory.com');
  $mail->addCC('may.sanchez@kallijaguar-inventory.com');
  $mail->addCC('claudia.espinoza@kallijaguar-inventory.com');
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
exit();