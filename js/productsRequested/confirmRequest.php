<?php
session_start();
var_dump($_SESSION);
ini_set('display_errors', 1);
error_reporting(E_ALL);

require('../../fpdf/fpdf.php');
require_once "../../controllers/mainController.php";

if (!isset($_SESSION['INV']) || !is_array($_SESSION['INV']) || count($_SESSION['INV']) == 0 || !isset($_POST['idSucursal'])) {
    echo json_encode(['status' => 'error', 'message' => 'No hay productos en el carrito.']);
    exit();
}

if (!isset($_POST['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Sesión no válida o expirada']);
    exit();
}

$sucursal_id = $_POST['idSucursal'];

$fecha = date('Ymd');
$random_number = rand(100, 999);
$comandaID = 'COM-' . $fecha . '-' . $sucursal_id . '-' . $random_number;

// Registrar los movimientos y reducir las cantidades en inventario
foreach ($_SESSION['INV'] as $item) {
    $consultaStock = conexion();
    $consultaStock = $consultaStock->query("SELECT Cantidad FROM Productos WHERE ProductoID = " . $item['producto'] . "");
    $stockDisponible = $consultaStock->fetchColumn();

    if ($item['cantidad'] <= $stockDisponible) {

        // Registrar el movimiento en la tabla de movimientos
        $conn = conexion();
        $stmt = $conn->prepare("INSERT INTO MovimientosInventario (ComandaID, SucursalID, ProductoID, TipoMovimiento, Cantidad, FechaMovimiento, PrecioFinal, UsuarioID, Status) 
                            VALUES (:comandaID,:sucursalID, :productoID, 'Salida', :cantidad, NOW(), :precioFinal, :usuarioID, :status)");

        $precioFinales = $item['precio'] * (1 + 0.16);

        try {
            $stmt->execute([
                ':comandaID' => $comandaID,
                ':sucursalID' => $sucursal_id,
                ':productoID' => $item['producto'],
                ':cantidad' => $item['cantidad'],
                ':precioFinal' => $precioFinales * $item['cantidad'],
                ':usuarioID' => $_POST['id'],
                ':status' => 'Abierto'
            ]);

            // Reducir la cantidad del producto en el inventario
            $updateStmt = $conn->prepare("UPDATE Productos SET Cantidad = Cantidad - :cantidad WHERE ProductoID = :productoID");
            $updateStmt->execute([
                ':cantidad' => $item['cantidad'],
                ':productoID' => $item['producto']
            ]);
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    } else {

        echo '
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
                <strong>¡Ocurrio un error!</strong><br>
                El stock del producto no es suficiente para cubrir la necesidad.
            </div>';;
    }

    $dataSucursal = conexion();
    $dataSucursal = $dataSucursal->query("SELECT nombre FROM Sucursales WHERE SucursalID = '$sucursal_id'");
    $nameSucursal = $dataSucursal->fetchColumn();

    $idUser = $_POST['id'];

    $dataUser = conexion();
    $dataUser = $dataUser->query("SELECT Nombre FROM Usuarios WHERE UsuarioID = '$idUser'");
    $nameUser = $dataUser->fetchColumn();

    try {
        // Crear una instancia de la clase FPDF
        $pdf = new FPDF();
        $pdf->AddPage();

        $pdf->Image('../../img/logo.png', 15, 15, 50);
        // Título en el centro superior (debes personalizar según lo que necesitas)
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetXY(70, 11);
        $pdf->Cell(60, 10, '' . $nameSucursal . '', 1, 0, 'C');
        $pdf->SetXY(70, 21);
        $pdf->Cell(60, 10, 'Listado de Salida', 1, 0, 'C');

        // Tablón derecho superior e inferior
        $pdf->SetXY(130, 11);
        $pdf->Cell(60, 10, '' . $comandaID . '', 1, 0, 'C');
        $pdf->SetXY(130, 21);
        $pdf->Cell(60, 10, $nameUser, 1, 0, 'C');

        // Salto de línea para el espacio entre la cabecera y el listado de productos
        $pdf->Ln(20);
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(180, 5, utf8_decode('A continuación se debe capturar las observaciones del producto al ser recepcionado por el solicitante, verificar que todos los productos'), 0, 1, 'C');
        $pdf->Cell(180, 5, utf8_decode('solicitados están siendo entregados y contar con 3 copias de este documento para cada una de las áreas.'), 0, 1, 'C');
        //body
        $pdf->Ln(5);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(190, 10, utf8_decode('Listado de productos solicitados a Almácen:'), 0, 1, 'L');
        $pdf->SetFont('Arial', 'B', 9);
        // Encabezado de la tabla
        $pdf->Cell(60, 10, 'Nombre del Producto/Materia Prima', 1, 0, 'C');
        $pdf->Cell(40, 10, 'Cantidad', 1, 0, 'C');
        $pdf->Cell(40, 10, 'Precio', 1, 0, 'C');
        $pdf->Cell(40, 10, 'Observaciones', 1, 0, 'C');
        $pdf->Ln();
        $pdf->SetFont('Arial', '', 9);
        // Datos de los productos
        $totalGeneral = 0;
        foreach ($_SESSION['INV'] as $item) {
            $totalItem = ($item['precio'] * 1.16) * $item['cantidad'];
            $totalGeneral += $totalItem;

            $pdf->Cell(60, 10, $item['nombre'], 1, 0, 'C');
            $pdf->Cell(40, 10, $item['cantidad'], 1, 0, 'C');
            $pdf->Cell(40, 10, '$' . $totalItem, 1, 0, 'C');
            $pdf->Cell(40, 10, '', 1);
            $pdf->Ln();
        }
        $pdf->Cell(100, 10, 'Total:', 1, 0, 'L');
        $pdf->Cell(40, 10, '$' . $totalGeneral, 1, 0, 'C');
        $pdf->Ln(20);

        $pdf->Cell(90, 10, '', 0, 0, 'C');
        $pdf->Cell(90, 10, '', 0, 0, 'C');
        $pdf->Ln(10);
        $pdf->Cell(90, 10, 'Firma', 0, 0, 'C');
        $pdf->Cell(90, 10, 'Firma', 0, 0, 'C');
        $pdf->Ln(10);
        $pdf->Cell(90, 10, '' . $nameUser . '', 0, 0, 'C');
        $pdf->Cell(90, 10, 'Mauricio Dominguez', 0, 0, 'C');


        $pdfPath = '../../documents/' . $comandaID . '.pdf';
        // Salvar o enviar el PDF
        $pdf->Output('F', $pdfPath, true); // Generar PDF en pantalla
    } catch (Exception $e) {
        echo "Error al generar PDF: " . $e->getMessage();
    }
}

$emailUser = conexion();
$emailUser = $emailUser->query("SELECT Email FROM Usuarios WHERE UsuarioID = '$idUser'");
$Usermail = $emailUser->fetchColumn();

require '../../PHPMailer/src/PHPMailer.php';
require '../../PHPMailer/src/SMTP.php';
require '../../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Debugoutput = 'html';
    $mail->Host = 'smtp.titan.email';
    $mail->SMTPAuth = true;
    $mail->Username = 'info@stagging.kallijaguar-inventory.com';
    $mail->Password = '{&<eXA[x$?_q\<N';
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;

    $mail->setFrom('info@stagging.kallijaguar-inventory.com', 'Informacion Kalli Jaguar');
    $mail->addAddress('cencarnacion@stagging.kallijaguar-inventory.com');
    //$mail->addAddress('mauricio.dominguez@kallijaguar-inventory.com');
    //$mail->addCC('julieta.ramirez@kallijaguar-inventory.com');
    //$mail->addCC('miguel.loaeza@kallijaguar-inventory.com');
    //$mail->addCC('andrea.sanchez@kallijaguar-inventory.com');
    //$mail->addCC('may.sanchez@kallijaguar-inventory.com');
    //$mail->addCC('cencarnacion@kallijaguar-inventory.com');

    $mail->addAttachment($pdfPath);

    $mail->isHTML(true);
    $mail->Subject = 'Comanda Generada: ' . $comandaID;
    $mail->Body = "<p>Se ha generado una nueva comanda con el ID: <strong>{$comandaID}</strong></p><p>Adjunto se encontrara el PDF correspondiente a la comanda.</p><br><p>Recuerda revisar tu solicitud.</p>";
    $mail->send();
    echo 'El mensaje ha sido enviado con éxito.';
} catch (Exception $e) {
    echo "El mensaje no pudo ser enviado: {$mail->ErrorInfo}";
}

echo json_encode(['status' => 'success', 'message' => 'La comanda fue procesada correctamente.']);
unset($_SESSION['INV']);
exit();
