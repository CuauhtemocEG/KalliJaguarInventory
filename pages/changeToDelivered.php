<?php
require_once "./controllers/mainController.php";

$comandaID = $_GET['ComandaID'];

$toDelivered = conexion();
$toDelivered = $toDelivered->prepare("UPDATE MovimientosInventario SET Status='Cerrado' WHERE ComandaID=:id");
$toDelivered->execute([":id" => $comandaID]);

require './PHPMailer/src/PHPMailer.php';
require './PHPMailer/src/SMTP.php';
require './PHPMailer/src/Exception.php';

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
    $mail->Password = 'KalliJaguar2025@';
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
    $mail->Subject = 'Comanda: ' . $comandaID.' Entregada';
    $mail->Body = "<p>La siguiente comanda: <strong>{$comandaID}</strong></p><p>Ya fue entregada en tu sucursal.</p>";
    $mail->send();
    echo 'El mensaje ha sido enviado con éxito.';
} catch (Exception $e) {
    echo "El mensaje no pudo ser enviado: {$mail->ErrorInfo}";
}

echo "<script>window.setTimeout(function() { window.location = 'index.php?page=showAllRequest' }, 10);</script>";
?>