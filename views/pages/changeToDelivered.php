<?php
require_once BASE_PATH . '/src/Helpers/legacy.php';

$comandaID = $_GET['ComandaID'];

$toDelivered = conexion();
$toDelivered = $toDelivered->prepare("UPDATE MovimientosInventario SET Status='Cerrado' WHERE ComandaID=:id");
$toDelivered->execute([":id" => $comandaID]);


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    \App\Services\Mailer::configure($mail);

    $mail->isHTML(true);
    $mail->Subject = 'Comanda: ' . $comandaID.' Entregada';
    $mail->Body = "<p>La siguiente comanda: <strong>{$comandaID}</strong></p><p>Ya fue entregada en tu sucursal.</p>";
    $mail->send();
    //echo 'El mensaje ha sido enviado con éxito.';
} catch (Exception $e) {
    echo "El mensaje no pudo ser enviado: {$mail->ErrorInfo}";
}

echo "<script>window.setTimeout(function() { window.location = 'index.php?page=showAllRequest' }, 10);</script>";
?>