<?php
session_start();
header('Content-Type: application/json');

$requiredFiles = [
    '../fpdf/fpdf.php',
    '../controllers/mainController.php',
    '../PHPMailer/src/PHPMailer.php',
    '../PHPMailer/src/SMTP.php',
    '../PHPMailer/src/Exception.php',
];

foreach ($requiredFiles as $file) {
    if (!file_exists($file)) {
        echo json_encode(['status' => 'error', 'message' => "Archivo no encontrado: $file"]);
        exit();
    }
}

require('../fpdf/fpdf.php');
require_once "../controllers/mainController.php";
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';
require '../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_POST['comanda_id']) || empty($_POST['comanda_id'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Falta comanda_id']);
    exit;
}

$comandaId = $_POST['comanda_id'];

try {
    $conn = conexion();

    // Obtener datos generales de la comanda
    $stmtInfo = $conn->prepare("SELECT DISTINCT 
    S.nombre AS SucursalNombre, 
    U.Nombre AS UsuarioNombre,
    MI.FechaMovimiento
    FROM MovimientosInventario MI
    JOIN Sucursales S ON MI.SucursalID = S.SucursalID
    JOIN Usuarios U ON MI.UsuarioID = U.UsuarioID
    WHERE MI.ComandaID = ?");
    $stmtInfo->execute([$comandaId]);
    $info = $stmtInfo->fetch(PDO::FETCH_ASSOC);

    if (!$info) {
        echo json_encode(['status' => 'error', 'message' => 'Comanda no encontrada']);
        exit();
    }

    $stmtProductos = $conn->prepare("SELECT 
    P.Nombre,
    P.Tipo,
    P.PrecioUnitario,
    MI.Cantidad,
    MI.PrecioFinal
    FROM MovimientosInventario MI
    JOIN Productos P ON MI.ProductoID = P.ProductoID
    WHERE MI.ComandaID = ? AND MI.TipoMovimiento = 'Salida'");
    $stmtProductos->execute([$comandaId]);
    $productos = $stmtProductos->fetchAll(PDO::FETCH_ASSOC);

    if (!$productos) {
        echo json_encode(['status' => 'error', 'message' => 'No hay productos para esta comanda']);
        exit();
    }

    // Fecha para PDF en español
    $fechaObj = new DateTime($info['FechaMovimiento']);
    function fechaEnEspañol($fechaObj)
    {
        $meses = [
            '01' => 'enero',
            '02' => 'febrero',
            '03' => 'marzo',
            '04' => 'abril',
            '05' => 'mayo',
            '06' => 'junio',
            '07' => 'julio',
            '08' => 'agosto',
            '09' => 'septiembre',
            '10' => 'octubre',
            '11' => 'noviembre',
            '12' => 'diciembre'
        ];
        $dias = [
            'Monday' => 'lunes',
            'Tuesday' => 'martes',
            'Wednesday' => 'miércoles',
            'Thursday' => 'jueves',
            'Friday' => 'viernes',
            'Saturday' => 'sábado',
            'Sunday' => 'domingo'
        ];

        $diaSemana = $dias[$fechaObj->format('l')] ?? '';
        $dia = $fechaObj->format('j');
        $mes = $meses[$fechaObj->format('m')] ?? '';
        $anio = $fechaObj->format('Y');

        return ucfirst("$diaSemana $dia de $mes del $anio");
    }
    $fechaLarga = fechaEnEspañol($fechaObj);

    // Preparar PDF
    $pdfPath = '../documents/' . $comandaId . '.pdf';
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->Image('../img/logo.png', 15, 15, 50);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetXY(70, 11);
    $pdf->Cell(60, 10, $info['SucursalNombre'], 1, 0, 'C');
    $pdf->SetXY(70, 21);
    $pdf->Cell(60, 10, 'Listado de Salida', 1, 0, 'C');
    $pdf->SetXY(130, 11);
    $pdf->Cell(60, 10, $comandaId, 1, 0, 'C');
    $pdf->SetXY(130, 21);
    $pdf->Cell(60, 10, $info['UsuarioNombre'], 1, 0, 'C');
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
    foreach ($productos as $item) {
        $unidad = $item['Tipo'] == "Pesable"
            ? ($item['Cantidad'] >= 1.0 ? 'Kg' : 'grs')
            : 'Unidad(es)';
        $cantidad = $item['Tipo'] == "Pesable"
            ? ($item['Cantidad'] >= 1.0 ? number_format($item['Cantidad'], 2) : number_format($item['Cantidad'], 3))
            : number_format($item['Cantidad'], 0);

        $totalItem = ($item['PrecioUnitario'] * 1.16) * $item['Cantidad'];
        $totalGeneral += $totalItem;

        $pdf->Cell(60, 10, utf8_decode($item['Nombre']), 1, 0, 'C');
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
    $pdf->Cell(90, 10, utf8_decode($info['UsuarioNombre']), 0, 0, 'C');
    $pdf->Cell(90, 10, 'Mauricio Dominguez', 0, 0, 'C');

    $pdf->Output('F', $pdfPath);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error al generar el PDF: ' . $e->getMessage()]);
}

// Preparar y enviar correo
$correoBody = '
<!DOCTYPE html>
<html>
  <head><meta charset="UTF-8"><title>Comanda</title></head>
  <body>
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0; padding:0; background-color:#000000;">
      <tr>
        <td align="center">
          <table width="450" cellpadding="0" cellspacing="0" border="0" style="background-color:#000000; border:2px solid #ffb900;">
            <tr>
              <td align="left" width="50%" style="padding:20px;">
                <img src="https://stagging.kallijaguar-inventory.com/img/LogoBlack.png" alt="Logo" width="120" style="display:block;">
              </td>
              <td align="right" width="50%" style="padding:20px;">
                <h3 style="color:#ffb900; margin:0; font-family:Arial, Helvetica, sans-serif;">Sistema KalliJaguar</h3>
              </td>
            </tr>
          </table>
        </td>
      </tr>
      <tr>
        <td align="center" style="padding: 20px; font-family:Arial, Helvetica, sans-serif; font-size:14px; color:#ffffff;">
          Se ha generado la comanda <b>' . $comandaId . '</b> y se adjunta el archivo PDF con el detalle.
        </td>
      </tr>
    </table>
  </body>
</html>';

// Configurar PHPMailer
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
    $mail->Subject = 'Pedido actualizado: ' . $comandaId;
    $mail->Body = $correoBody;

    $mail->send();

    echo json_encode(['status' => 'success', 'message' => 'PDF generado y enviado correctamente']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error enviando correo: ' . $mail->ErrorInfo]);
}
