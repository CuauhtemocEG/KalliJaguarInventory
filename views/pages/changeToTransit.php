<?php
require_once BASE_PATH . '/src/Helpers/legacy.php';

$comandaID = $_GET['ComandaID'];

$toTransit = conexion();
$toTransit = $toTransit->prepare("UPDATE MovimientosInventario SET Status='En transito' WHERE ComandaID=:id");
$toTransit->execute([":id" => $comandaID]);


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    \App\Services\Mailer::configure($mail);

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