<?php

declare(strict_types=1);

namespace App\Services;

use FPDF;
use PDO;
use RuntimeException;

/**
 * Genera el PDF de una comanda a partir de los datos guardados (MovimientosInventario).
 * Los importes salen de PrecioFinal (precio con IVA vigente al momento de la solicitud),
 * por lo que el documento no cambia si después se modifica el precio del producto.
 */
final class ComandaPdf
{
    private const ESTADOS = [
        'Abierto' => ['Pendiente', [180, 110, 0]],
        'En transito' => ['En tránsito', [0, 90, 170]],
        'Cerrado' => ['Entregado', [0, 120, 40]],
        'Cancelado' => ['Cancelado', [180, 0, 0]],
    ];

    /** @return array{info: array<string,mixed>, items: array<int,array<string,mixed>>}|null */
    public static function load(PDO $db, string $comandaId): ?array
    {
        $stmt = $db->prepare(
            "SELECT m.ComandaID, MIN(m.FechaMovimiento) AS fecha, MAX(m.FechaDelivery) AS entrega,
                    MAX(m.Status) AS estado, s.nombre AS sucursal, u.Nombre AS solicitante
             FROM MovimientosInventario m
             LEFT JOIN Sucursales s ON s.SucursalID = m.SucursalID
             LEFT JOIN Usuarios u ON u.UsuarioID = m.UsuarioID
             WHERE m.ComandaID = :c AND m.TipoMovimiento = 'Salida'
             GROUP BY m.ComandaID, s.nombre, u.Nombre"
        );
        $stmt->execute([':c' => $comandaId]);
        $info = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$info) {
            return null;
        }

        $items = $db->prepare(
            "SELECT p.Nombre, p.Tipo, m.Cantidad, m.PrecioFinal
             FROM MovimientosInventario m
             LEFT JOIN Productos p ON p.ProductoID = m.ProductoID
             WHERE m.ComandaID = :c AND m.TipoMovimiento = 'Salida'
             ORDER BY p.Nombre"
        );
        $items->execute([':c' => $comandaId]);

        return ['info' => $info, 'items' => $items->fetchAll(PDO::FETCH_ASSOC)];
    }

    /**
     * Logo reducido (JPEG sobre blanco) en storage/cache. El PNG original mide 7396 px de ancho
     * y FPDF agota la memoria al descomprimirlo.
     */
    public static function logo(): ?string
    {
        $origen = base_path('public/assets/img/logo.png');
        $destino = base_path('storage/cache/logo-pdf.jpg');
        if (is_file($destino) && is_file($origen) && filemtime($destino) >= filemtime($origen)) {
            return $destino;
        }
        if (!is_file($origen) || !function_exists('imagecreatefrompng') || !is_dir(dirname($destino)) && !@mkdir(dirname($destino), 0775, true)) {
            return null;
        }
        [$w, $h] = getimagesize($origen) ?: [0, 0];
        if ($w < 1 || $h < 1 || $w * $h > 60_000_000) {
            return null;
        }
        $src = @imagecreatefrompng($origen);
        if (!$src) {
            return null;
        }
        $nw = min($w, 600);
        $nh = (int) round($h * $nw / $w);
        $img = imagecreatetruecolor($nw, $nh);
        imagefill($img, 0, 0, imagecolorallocate($img, 255, 255, 255));
        imagecopyresampled($img, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
        $ok = imagejpeg($img, $destino, 90);
        imagedestroy($src);
        imagedestroy($img);

        return $ok ? $destino : null;
    }

    /** Devuelve el contenido binario del PDF; lanza RuntimeException si la comanda no existe. */
    public static function render(PDO $db, string $comandaId): string
    {
        $data = self::load($db, $comandaId);
        if ($data === null) {
            throw new RuntimeException('Comanda no encontrada.');
        }
        ['info' => $info, 'items' => $items] = $data;

        $pdf = new class () extends FPDF {
            public function Header(): void
            {
                $logo = ComandaPdf::logo();
                if ($logo !== null) {
                    $this->Image($logo, 10, 10, 28);
                }
                $this->SetFont('Arial', 'B', 14);
                $this->SetXY(100, 13);
                $this->Cell(100, 6, ComandaPdf::t((string) config('app.company')), 0, 1, 'R');
                $this->SetFont('Arial', '', 9);
                $this->SetTextColor(90);
                $this->SetXY(100, 20);
                $this->Cell(100, 5, ComandaPdf::t('Sistema de Inventario'), 0, 1, 'R');
                $this->SetXY(100, 25);
                $this->Cell(100, 5, (string) config('mail.from_address'), 0, 1, 'R');
                $this->SetTextColor(0);
                $this->SetDrawColor(245, 200, 66);
                $this->SetLineWidth(0.8);
                $this->Line(10, 42, 200, 42);
                $this->SetDrawColor(0);
                $this->SetLineWidth(0.2);
                $this->SetY(48);
            }

            public function Footer(): void
            {
                $this->SetY(-15);
                $this->SetFont('Arial', 'I', 8);
                $this->SetTextColor(128);
                $this->Cell(0, 5, ComandaPdf::t('Documento generado automáticamente · Página ') . $this->PageNo() . ' / {nb}', 0, 0, 'C');
                $this->SetTextColor(0);
            }
        };
        $pdf->AliasNbPages();
        $pdf->SetAutoPageBreak(true, 20);
        $pdf->AddPage();

        [$estadoTxt, $estadoColor] = self::ESTADOS[$info['estado']] ?? [(string) $info['estado'], [0, 0, 0]];

        $pdf->SetFont('Arial', 'B', 17);
        $pdf->Cell(0, 9, self::t('Solicitud de productos'), 0, 1, 'C');
        $pdf->Ln(3);

        $campos = [
            ['Folio', $info['ComandaID'], 'Sucursal', $info['sucursal'] ?: '—'],
            ['Solicitante', $info['solicitante'] ?: '—', 'Fecha', self::fecha($info['fecha'])],
            ['Estado', $estadoTxt, 'Entrega', self::fecha($info['entrega'], true)],
        ];
        foreach ($campos as [$l1, $v1, $l2, $v2]) {
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetFillColor(248, 248, 248);
            $pdf->Cell(26, 7, self::t($l1 . ':'), 1, 0, 'L', true);
            $pdf->SetFont('Arial', 'B', 10);
            if ($l1 === 'Estado') {
                $pdf->SetTextColor(...$estadoColor);
            }
            $pdf->Cell(69, 7, self::t(self::corto((string) $v1, 34)), 1, 0, 'L');
            $pdf->SetTextColor(0);
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(26, 7, self::t($l2 . ':'), 1, 0, 'L', true);
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(69, 7, self::t(self::corto((string) $v2, 34)), 1, 1, 'L');
        }
        $pdf->Ln(7);

        $cabecera = static function (FPDF $pdf): void {
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetFillColor(245, 200, 66);
            $pdf->Cell(10, 9, '#', 1, 0, 'C', true);
            $pdf->Cell(86, 9, 'Producto', 1, 0, 'C', true);
            $pdf->Cell(32, 9, 'Cantidad', 1, 0, 'C', true);
            $pdf->Cell(30, 9, 'P. unit. c/IVA', 1, 0, 'C', true);
            $pdf->Cell(32, 9, 'Importe', 1, 1, 'C', true);
        };
        $cabecera($pdf);

        $total = 0.0;
        foreach ($items as $n => $it) {
            if ($pdf->GetY() > 255) {
                $pdf->AddPage();
                $cabecera($pdf);
            }
            $cant = (float) $it['Cantidad'];
            $importe = (float) $it['PrecioFinal'];
            $total += $importe;
            $cantTxt = Unidades::formato($it['Tipo'] ?? null, $cant);

            $pdf->SetFont('Arial', '', 9);
            $pdf->SetFillColor(248, 248, 248);
            $fill = $n % 2 === 1;
            $pdf->Cell(10, 7.5, (string) ($n + 1), 1, 0, 'C', $fill);
            $pdf->Cell(86, 7.5, self::t(self::corto((string) ($it['Nombre'] ?? 'Producto eliminado'), 46)), 1, 0, 'L', $fill);
            $pdf->Cell(32, 7.5, $cantTxt, 1, 0, 'C', $fill);
            $pdf->Cell(30, 7.5, $cant > 0 ? '$' . number_format($importe / $cant, 2) : '-', 1, 0, 'R', $fill);
            $pdf->Cell(32, 7.5, '$' . number_format($importe, 2), 1, 1, 'R', $fill);
        }

        $rate = (float) config('app.tax_rate');
        $subtotal = $total / (1 + $rate);
        if ($pdf->GetY() > 235) {
            $pdf->AddPage();
        }
        $resumen = [
            ['Subtotal', $subtotal, false],
            ['IVA (' . rtrim(rtrim(number_format($rate * 100, 2), '0'), '.') . '%)', $total - $subtotal, false],
            ['TOTAL', $total, true],
        ];
        foreach ($resumen as [$label, $valor, $fuerte]) {
            $pdf->SetFont('Arial', $fuerte ? 'B' : '', $fuerte ? 11 : 10);
            $pdf->SetFillColor(240, 240, 240);
            $pdf->Cell(158, 7.5, self::t($label), 1, 0, 'R', $fuerte);
            $pdf->Cell(32, 7.5, '$' . number_format($valor, 2), 1, 1, 'R', $fuerte);
        }

        $pdf->Ln(12);
        if ($pdf->GetY() > 235) {
            $pdf->AddPage();
        }
        $pdf->SetFont('Arial', '', 9);
        $pdf->MultiCell(0, 5, self::t('Confirmo que los productos listados fueron verificados y corresponden a la solicitud realizada.'), 0, 'C');
        $pdf->Ln(14);
        $y = $pdf->GetY();
        $pdf->Line(25, $y, 90, $y);
        $pdf->Line(120, $y, 185, $y);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(95, 5, 'Solicitante', 0, 0, 'C');
        $pdf->Cell(95, 5, self::t('Almacén'), 0, 1, 'C');
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(95, 4, self::t((string) ($info['solicitante'] ?? '')), 0, 0, 'C');
        $pdf->Cell(95, 4, self::t('Encargado de Logística'), 0, 1, 'C');

        return $pdf->Output('S');
    }

    /** UTF-8 -> ISO-8859-1 (codificación de las fuentes estándar de FPDF). */
    public static function t(string $s): string
    {
        return mb_convert_encoding($s, 'ISO-8859-1', 'UTF-8');
    }

    private static function corto(string $s, int $max): string
    {
        return mb_strimwidth($s, 0, $max, '...', 'UTF-8');
    }

    private static function fecha(?string $s, bool $larga = false): string
    {
        $ts = $s ? strtotime($s) : false;
        if ($ts === false) {
            return '-';
        }
        if (!$larga) {
            return date('d/m/Y H:i', $ts);
        }
        $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
        return date('j', $ts) . ' de ' . $meses[(int) date('n', $ts) - 1] . ' de ' . date('Y', $ts);
    }
}
