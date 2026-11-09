<?php

declare(strict_types=1);

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;

/**
 * Configura PHPMailer con los datos SMTP y destinatarios definidos en .env.
 */
final class Mailer
{
    /** Crea un PHPMailer listo para usar (SMTP, remitente, destinatario y copias). */
    public static function create(): PHPMailer
    {
        return self::configure(new PHPMailer(true));
    }

    public static function configure(PHPMailer $mail): PHPMailer
    {
        $cfg = (array) config('mail');

        $mail->isSMTP();
        $mail->CharSet = 'UTF-8';
        $mail->Host = $cfg['host'];
        $mail->SMTPAuth = $cfg['username'] !== '';
        $mail->Username = $cfg['username'];
        $mail->Password = $cfg['password'];
        $mail->Port = $cfg['port'];
        if (!empty($cfg['encryption'])) {
            $mail->SMTPSecure = $cfg['encryption'];
        }

        $mail->setFrom($cfg['from_address'], $cfg['from_name']);
        if (!empty($cfg['to'])) {
            $mail->addAddress($cfg['to']);
        }
        foreach ($cfg['cc'] as $copia) {
            $mail->addCC($copia);
        }

        return $mail;
    }
}
