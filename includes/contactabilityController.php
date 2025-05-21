<?php

require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';
require '../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendEmailToConfirm($asunto, $body, $pdf)
{
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
        $mail->addAttachment($pdf);

        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body = $body;

        $mail->send();
    } catch (Exception $e) {
        return json_encode(['status' => 'error', 'message' => 'Error al enviar correo: ' . $mail->ErrorInfo]);
        exit();
    }
}
