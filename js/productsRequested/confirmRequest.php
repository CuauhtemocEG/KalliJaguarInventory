<?php
session_start();
require('../../fpdf/fpdf.php');
require_once "../../controllers/mainController.php";
require '../../PHPMailer/src/PHPMailer.php';
require '../../PHPMailer/src/SMTP.php';
require '../../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Validaciones iniciales
if (!isset($_SESSION['INV']) || !is_array($_SESSION['INV']) || count($_SESSION['INV']) === 0) {
    echo json_encode(['status' => 'error', 'message' => 'No hay productos en el carrito.']);
    exit();
}
if (!isset($_POST['idSucursal']) || !isset($_POST['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Datos de sesión no válidos.']);
    exit();
}

$idUser = $_POST['id'];
$sucursal_id = $_POST['idSucursal'];
$fecha = date('Ymd');
$random_number = rand(100, 999);
$comandaID = 'COM-' . $fecha . '-' . $sucursal_id . '-' . $random_number;

// Validar stock y registrar movimientos
foreach ($_SESSION['INV'] as $item) {
    $conn = conexion();

    $consultaStock = $conn->prepare("SELECT Cantidad FROM Productos WHERE ProductoID = ?");
    $consultaStock->execute([$item['producto']]);
    $stockDisponible = $consultaStock->fetchColumn();

    if ($item['cantidad'] > $stockDisponible) {
        echo json_encode([
            'status' => 'error',
            'message' => 'El stock del producto "' . $item['nombre'] . '" no es suficiente.'
        ]);
        exit();
    }

    $precioFinales = $item['precio'] * 1.16;

    try {
        // Insertar movimiento
        $stmt = $conn->prepare("INSERT INTO MovimientosInventario 
            (ComandaID, SucursalID, ProductoID, TipoMovimiento, Cantidad, FechaMovimiento, PrecioFinal, UsuarioID, Status) 
            VALUES (?, ?, ?, 'Salida', ?, NOW(), ?, ?, 'Abierto')");
        $stmt->execute([
            $comandaID,
            $sucursal_id,
            $item['producto'],
            $item['cantidad'],
            $precioFinales * $item['cantidad'],
            $idUser
        ]);

        // Actualizar stock
        $updateStmt = $conn->prepare("UPDATE Productos SET Cantidad = Cantidad - ? WHERE ProductoID = ?");
        $updateStmt->execute([$item['cantidad'], $item['producto']]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error al registrar movimiento: ' . $e->getMessage()]);
        exit();
    }
}

// Obtener datos para el PDF
$conn = conexion();
$nameSucursal = $conn->query("SELECT nombre FROM Sucursales WHERE SucursalID = '$sucursal_id'")->fetchColumn();
$nameUser = $conn->query("SELECT Nombre FROM Usuarios WHERE UsuarioID = '$idUser'")->fetchColumn();

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

    $pdf->Ln(20);
    $pdf->SetFont('Arial', '', 8);
    $pdf->MultiCell(180, 5, utf8_decode('A continuación se debe capturar las observaciones del producto al ser recepcionado por el solicitante, verificar que todos los productos solicitados están siendo entregados y contar con 3 copias de este documento para cada una de las áreas.'), 0, 'C');

    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(190, 10, utf8_decode('Listado de productos solicitados a Almácen:'), 0, 1, 'L');
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(60, 10, 'Nombre del Producto/Materia Prima', 1, 0, 'C');
    $pdf->Cell(40, 10, 'Cantidad', 1, 0, 'C');
    $pdf->Cell(40, 10, 'Precio', 1, 0, 'C');
    $pdf->Cell(40, 10, 'Observaciones', 1, 0, 'C');
    $pdf->Ln();

    $pdf->SetFont('Arial', '', 9);
    $totalGeneral = 0;
    foreach ($_SESSION['INV'] as $item) {

        $unidadesRes = '';
        $quantity = '';

        if ($item['tipo'] == "Pesable") {
            if ($item['cantidad'] >= 1.0) {
                $unidadesRes = 'Kg';
                $quantity = number_format($item['cantidad'], 2, '.', '');
            } else {
                $unidadesRes = 'grs';
                $quantity = number_format($item['cantidad'], 3, '.', '');
            }
        } else {
            $unidadesRes = 'Unidad(es)';
            $quantity = number_format($item['cantidad'], 0, '.', '');
        }

        $totalItem = ($item['precio'] * 1.16) * $item['cantidad'];
        $totalGeneral += $totalItem;

        $pdf->Cell(60, 10, $item['nombre'], 1, 0, 'C');
        $pdf->Cell(40, 10, $quantity .' '. $unidadesRes, 1, 0, 'C');
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
    $mail->Username = 'info@kallijaguar-inventory.com';
    $mail->Password = '{&<eXA[x$?_q\<N';
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;

    $mail->setFrom('info@kallijaguar-inventory.com', 'Informacion Kalli Jaguar');
    $mail->addAddress('mauricio.dominguez@kallijaguar-inventory.com');
    $mail->addCC('julieta.ramirez@kallijaguar-inventory.com');
    $mail->addCC('miguel.loaeza@kallijaguar-inventory.com');
    $mail->addCC('andrea.sanchez@kallijaguar-inventory.com');
    $mail->addCC('may.sanchez@kallijaguar-inventory.com');
    $mail->addCC('cencarnacion@kallijaguar-inventory.com');
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

unset($_SESSION['INV']);
echo json_encode(['status' => 'success', 'message' => 'La comanda fue procesada correctamente.']);
exit();
