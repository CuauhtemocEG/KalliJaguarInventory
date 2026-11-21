<?php

declare(strict_types=1);

namespace App\Services;

use PDO;
use PHPMailer\PHPMailer\PHPMailer;
use RuntimeException;

final class ConfirmacionPedido
{
    public static function enviar(PDO $db, string $comandaId): void
    {
        $pedido = ComandaPdf::load($db, $comandaId);
        if ($pedido === null) {
            throw new RuntimeException('No se encontró la comanda para enviar la confirmación');
        }

        $pdf = ComandaPdf::render($db, $comandaId);
        $mail = Mailer::create();
        $mail->Timeout = 15;
        self::preparar($mail, $pedido, $pdf);
        $mail->send();
    }

    /** @param array{info: array<string,mixed>, items: array<int,array<string,mixed>>} $pedido */
    public static function preparar(PHPMailer $mail, array $pedido, string $pdf): void
    {
        $info = $pedido['info'];
        $folio = (string) $info['ComandaID'];
        $escapar = static fn (string $valor): string => htmlspecialchars($valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $lineas = [];
        $texto = [];
        $total = 0.0;
        foreach ($pedido['items'] as $item) {
            $nombre = (string) ($item['Nombre'] ?? 'Producto');
            $cantidad = Unidades::formato($item['Tipo'] ?? null, (float) $item['Cantidad']);
            $lineas[] = '<li>' . $escapar($nombre) . ' — ' . $escapar($cantidad) . '</li>';
            $texto[] = '- ' . $nombre . ' — ' . $cantidad;
            $total += (float) $item['PrecioFinal'];
        }

        $sucursal = (string) ($info['sucursal'] ?? '');
        $solicitante = (string) ($info['solicitante'] ?? '');
        $entrega = (string) ($info['entrega'] ?? '');
        $importe = '$' . number_format($total, 2);

        $mail->isHTML(true);
        $mail->Subject = 'Confirmación de tu pedido: ' . $folio;
        $mail->Body = '<p>Se ha registrado el pedido <strong>' . $escapar($folio) . '</strong>.</p>'
            . '<p>Sucursal: ' . $escapar($sucursal) . '<br>Solicitante: ' . $escapar($solicitante)
            . '<br>Fecha de entrega: ' . $escapar($entrega) . '</p>'
            . '<p>Productos solicitados:</p><ul>' . implode('', $lineas) . '</ul>'
            . '<p>Total con IVA: <strong>' . $importe . '</strong></p>'
            . '<p>La comanda se encuentra adjunta en PDF.</p>';
        $mail->AltBody = "Se ha registrado el pedido $folio.\nSucursal: $sucursal\nSolicitante: $solicitante"
            . "\nFecha de entrega: $entrega\nProductos solicitados:\n" . implode("\n", $texto)
            . "\nTotal con IVA: $importe\nLa comanda se encuentra adjunta en PDF.";
        $nombreArchivo = preg_replace('/[^A-Za-z0-9_-]/', '_', $folio);
        $mail->addStringAttachment($pdf, 'comanda-' . $nombreArchivo . '.pdf', 'base64', 'application/pdf');
    }
}
