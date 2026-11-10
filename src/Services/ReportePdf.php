<?php

declare(strict_types=1);

namespace App\Services;

use FPDF;

/** Base visual común de los reportes PDF del Gestor (encabezado, KPIs, tablas por secciones). */
final class ReportePdf extends FPDF
{
    public const ORO = [245, 200, 66];
    public const OSCURO = [30, 41, 59];

    /** @var list<string> */
    private array $subtitulos;

    /** @param list<string> $subtitulos */
    public function __construct(private readonly string $titulo, array $subtitulos, string $orientacion = 'P')
    {
        parent::__construct($orientacion, 'mm', 'A4');
        $this->subtitulos = $subtitulos;
        $this->AliasNbPages();
        $this->SetAutoPageBreak(true, 16);
        $this->SetMargins(10, 10, 10);
        $this->AddPage();
    }

    public function ancho(): float
    {
        return $this->GetPageWidth() - 20;
    }

    public static function t(string $s): string
    {
        return ComandaPdf::t($s);
    }

    public static function corto(string $s, int $max): string
    {
        return mb_strimwidth($s, 0, $max, '...', 'UTF-8');
    }

    public static function dinero(float $v): string
    {
        return '$' . number_format($v, 2);
    }

    public function Header(): void
    {
        $x1 = $this->GetPageWidth() - 10;
        $logo = ComandaPdf::logo();
        if ($logo !== null) {
            $this->Image($logo, 10, 8, 26);
        }
        $this->SetFont('Arial', 'B', 15);
        $this->SetTextColor(...self::OSCURO);
        $this->SetXY(40, 8);
        $this->Cell($x1 - 40, 7, self::t($this->titulo), 0, 1, 'R');
        $this->SetFont('Arial', '', 8.5);
        $this->SetTextColor(100);
        foreach ($this->subtitulos as $linea) {
            $this->SetX(40);
            $this->Cell($x1 - 40, 4.2, self::t($linea), 0, 1, 'R');
        }
        $y = max($this->GetY(), 22) + 2;
        $this->SetDrawColor(...self::ORO);
        $this->SetLineWidth(0.8);
        $this->Line(10, $y, $x1, $y);
        $this->SetDrawColor(0);
        $this->SetLineWidth(0.2);
        $this->SetTextColor(0);
        $this->SetY($y + 4);
    }

    public function Footer(): void
    {
        $this->SetY(-12);
        $this->SetFont('Arial', 'I', 7.5);
        $this->SetTextColor(128);
        $this->Cell($this->ancho() / 2, 5, self::t((string) config('app.company') . ' · Generado ' . date('d/m/Y H:i')), 0, 0, 'L');
        $this->Cell($this->ancho() / 2, 5, self::t('Página ') . $this->PageNo() . ' / {nb}', 0, 0, 'R');
        $this->SetTextColor(0);
    }

    /** Tarjetas de indicadores en una fila. @param list<array{0:string,1:string}> $kpis */
    public function kpis(array $kpis): void
    {
        $n = count($kpis);
        $gap = 3.0;
        $w = ($this->ancho() - $gap * ($n - 1)) / $n;
        $x = 10.0;
        $y = $this->GetY();
        foreach ($kpis as [$label, $valor]) {
            $this->SetFillColor(248, 250, 252);
            $this->SetDrawColor(226, 232, 240);
            $this->Rect($x, $y, $w, 15, 'DF');
            $this->SetFillColor(...self::ORO);
            $this->Rect($x, $y, 1.2, 15, 'F');
            $this->SetXY($x + 3, $y + 2);
            $this->SetFont('Arial', '', 7.5);
            $this->SetTextColor(100);
            $this->Cell($w - 4, 3.5, self::t($label), 0, 2);
            $this->SetFont('Arial', 'B', 11);
            $this->SetTextColor(...self::OSCURO);
            $this->Cell($w - 4, 6, self::t($valor), 0, 0);
            $x += $w + $gap;
        }
        $this->SetTextColor(0);
        $this->SetDrawColor(0);
        $this->SetY($y + 19);
    }

    /** Franja de sección (sucursal, categoría, etiqueta…). */
    public function seccion(string $texto, string $derecha = ''): void
    {
        if ($this->GetY() > $this->GetPageHeight() - 40) {
            $this->AddPage();
        }
        $this->SetFillColor(...self::OSCURO);
        $this->SetTextColor(255);
        $this->SetFont('Arial', 'B', 10);
        $mitad = $this->ancho() * 0.6;
        $this->Cell($mitad, 7.5, '  ' . self::t(self::corto($texto, 70)), 0, 0, 'L', true);
        $this->SetFont('Arial', '', 9);
        $this->Cell($this->ancho() - $mitad, 7.5, self::t($derecha) . '  ', 0, 1, 'R', true);
        $this->SetTextColor(0);
    }

    /** @param list<array{0:string,1:float,2:string}> $cols [etiqueta, ancho, alineación] */
    public function cabecera(array $cols): void
    {
        $this->SetFont('Arial', 'B', 8);
        $this->SetFillColor(...self::ORO);
        $this->SetTextColor(...self::OSCURO);
        $this->SetDrawColor(226, 232, 240);
        foreach ($cols as [$etq, $w, $al]) {
            $this->Cell($w, 6.5, self::t($etq), 1, 0, $al === 'R' ? 'R' : ($al === 'C' ? 'C' : 'L'), true);
        }
        $this->Ln();
        $this->SetTextColor(0);
    }

    /**
     * Fila de tabla; repite la cabecera si cambia de página.
     *
     * @param list<array{0:string,1:float,2:string}> $cols
     * @param list<string> $celdas
     * @param array{fill?:bool,bold?:bool,color?:array{0:int,1:int,2:int},colores?:array<int,array{0:int,1:int,2:int}>} $op
     */
    public function fila(array $cols, array $celdas, array $op = [], ?array $repetir = null): void
    {
        if ($this->GetY() > $this->GetPageHeight() - 22) {
            $this->AddPage();
            if ($repetir) {
                $this->cabecera($repetir);
            }
        }
        $this->SetFont('Arial', !empty($op['bold']) ? 'B' : '', 8);
        $this->SetDrawColor(226, 232, 240);
        $fill = !empty($op['fill']);
        $this->SetFillColor(248, 250, 252);
        foreach ($cols as $i => [, $w, $al]) {
            $color = $op['colores'][$i] ?? $op['color'] ?? [0, 0, 0];
            $this->SetTextColor(...$color);
            $this->Cell($w, 6.2, self::t((string) ($celdas[$i] ?? '')), 1, 0, $al === 'R' ? 'R' : ($al === 'C' ? 'C' : 'L'), $fill);
        }
        $this->Ln();
        $this->SetTextColor(0);
    }

    public function vacio(string $mensaje): void
    {
        $this->Ln(20);
        $this->SetFont('Arial', 'I', 11);
        $this->SetTextColor(120);
        $this->Cell(0, 8, self::t($mensaje), 0, 1, 'C');
        $this->SetTextColor(0);
    }
}
