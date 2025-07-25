<?php
require_once "./controllers/mainController.php";

$comandaID = $_GET['ComandaID'];

$toTransit = conexion();
$toTransit = $toTransit->prepare("UPDATE MovimientosInventario SET Status='En transito' WHERE ComandaID=:id");
$toTransit->execute([":id" => $comandaID]);

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
    $mail->Username = 'info@kallijaguar-inventory.com';
    $mail->Password = '{&<eXA[x$?_q\<N';
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;

    $mail->setFrom('info@kallijaguar-inventory.com', 'Informacion Kalli Jaguar');
    $mail->addAddress('andrea.sanchez@kallijaguar-inventory.com');
    $mail->addCC('julieta.ramirez@kallijaguar-inventory.com');
    $mail->addCC('miguel.loaeza@kallijaguar-inventory.com');
    $mail->addCC('may.sanchez@kallijaguar-inventory.com');
    $mail->addCC('cencarnacion@kallijaguar-inventory.com');
    $mail->addCC('claudia.espinoza@kallijaguar-inventory.com');

    $mail->isHTML(true);
    $mail->Subject = 'Comanda: ' . $comandaID.' en transito';
    $mail->Body = "<p>La siguiente comanda: <strong>{$comandaID}</strong></p><p>Ya va en camino, recuerda verificar tu pedido en cuanto llegue a sucursal.</p>";
    $mail->send();
    //echo 'El mensaje ha sido enviado con éxito.';
} catch (Exception $e) {
    echo "El mensaje no pudo ser enviado: {$mail->ErrorInfo}";
}

echo "<script>window.setTimeout(function() { window.location = 'index.php?page=showAllRequest' }, 10);</script>";
?>