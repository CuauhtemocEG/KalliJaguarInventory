<?php

declare(strict_types=1);

namespace App\Services;

use PDO;

/** PDF de una requisición: pedido, aprobación y lo realmente recibido en almacén. */
final class RequisicionPdf
{
    private const COLORES = [
        'Enviada' => [180, 83, 9], 'Aprobada' => [3, 105, 161], 'Parcial' => [194, 65, 12],
        'Recibida' => [4, 120, 87], 'Rechazada' => [185, 28, 28], 'Cancelada' => [100, 116, 139],
    ];

    /** @param array<string,mixed> $r resultado de Requisiciones::detalle() */
    public static function generar(PDO $db, array $r, bool $verCostos): string
    {
        $subs = ['Folio ' . $r['Folio'], 'Solicitó: ' . $r['solicitante'] . ($r['sucursal'] ? ' · ' . $r['sucursal'] : '')];
        $pdf = new ReportePdf('Requisición de compra', $subs);

        $lineas = $r['lineas'];
        $pedidoTotal = $recibidoTotal = $importe = 0.0;
        foreach ($lineas as $l) {
            $recibido = (float) $l['CantidadRecibida'];
            $importe += $recibido * (float) ($l['CostoUnitario'] ?? 0);
            $recibidoTotal += $recibido > 0 ? 1 : 0;
            $pedidoTotal += 1;
        }

        $kpis = [
            ['Estado', (string) $r['Estado']],
            ['Fecha', self::fecha((string) $r['FechaCreacion'])],
            ['Requerida', $r['FechaRequerida'] ? self::fecha((string) $r['FechaRequerida']) : '-'],
            ['Líneas recibidas', (int) $recibidoTotal . ' de ' . (int) $pedidoTotal],
        ];
        if ($verCostos) {
            $kpis[] = ['Importe recibido', ReportePdf::dinero($importe)];
        }
        $pdf->kpis($kpis);

        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(60);
        $meta = [];
        if (!empty($r['Proveedor'])) {
            $meta[] = 'Proveedor: ' . $r['Proveedor'];
        }
        if (!empty($r['aprobador'])) {
            $meta[] = 'Aprobó: ' . $r['aprobador'] . ($r['FechaAprobacion'] ? ' (' . self::fecha((string) $r['FechaAprobacion']) . ')' : '');
        }
        foreach ($meta as $m) {
            $pdf->Cell(0, 5, ReportePdf::t($m), 0, 1);
        }
        if (!empty($r['MotivoRechazo'])) {
            $pdf->SetTextColor(185, 28, 28);
            $pdf->MultiCell(0, 5, ReportePdf::t('Motivo del rechazo: ' . $r['MotivoRechazo']));
        }
        if (!empty($r['Notas'])) {
            $pdf->SetTextColor(60);
            $pdf->MultiCell(0, 5, ReportePdf::t('Notas: ' . $r['Notas']));
        }
        $pdf->SetTextColor(0);
        $pdf->Ln(2);

        $pdf->seccion('Productos', (string) $r['Estado']);
        $cols = $verCostos
            ? [['Producto', 66.0, 'L'], ['Pedido', 24.0, 'R'], ['Aprobado', 24.0, 'R'], ['Recibido', 24.0, 'R'], ['Dif.', 20.0, 'R'], ['Costo', 16.0, 'R'], ['Importe', 16.0, 'R']]
            : [['Producto', 82.0, 'L'], ['Pedido', 28.0, 'R'], ['Aprobado', 28.0, 'R'], ['Recibido', 28.0, 'R'], ['Dif.', 24.0, 'R']];
        $pdf->cabecera($cols);

        foreach ($lineas as $i => $l) {
            $tipo = (string) $l['Tipo'];
            $aprobado = $l['CantidadAprobada'] === null ? null : (float) $l['CantidadAprobada'];
            $recibido = (float) $l['CantidadRecibida'];
            $dif = $aprobado !== null && $recibido > 0 ? $recibido - $aprobado : null;
            $difTxt = '-';
            $colorDif = [0, 0, 0];
            if ($dif !== null && abs($dif) > 0.0005) {
                $difTxt = ($dif > 0 ? '+' : '-') . Unidades::formato($tipo, abs($dif));
                $colorDif = $dif > 0 ? [194, 65, 12] : [185, 28, 28];
            }
            $celdas = [
                ReportePdf::corto((string) $l['Descripcion'], $verCostos ? 38 : 50) . ($l['ProductoID'] ? '' : ' *'),
                Unidades::formato($tipo, (float) $l['CantidadSolicitada']),
                $aprobado === null ? '-' : Unidades::formato($tipo, $aprobado),
                $recibido > 0 ? Unidades::formato($tipo, $recibido) : '-',
                $difTxt,
            ];
            $colores = [4 => $colorDif];
            if ($verCostos) {
                $costo = $l['CostoUnitario'] !== null ? (float) $l['CostoUnitario'] : null;
                $celdas[] = $costo !== null && $recibido > 0 ? ReportePdf::dinero($costo) : '-';
                $celdas[] = $costo !== null && $recibido > 0 ? ReportePdf::dinero($costo * $recibido) : '-';
            }
            $pdf->fila($cols, $celdas, ['fill' => $i % 2 === 1, 'colores' => $colores], $cols);
        }
        if ($verCostos) {
            $pdf->fila($cols, array_merge(array_fill(0, count($cols) - 1, ''), [ReportePdf::dinero($importe)]), ['bold' => true, 'fill' => true]);
        }
        if (array_filter($lineas, static fn ($l) => !$l['ProductoID'])) {
            $pdf->SetFont('Arial', 'I', 7.5);
            $pdf->SetTextColor(120);
            $pdf->Cell(0, 5, ReportePdf::t('* Línea de texto libre, aún sin vincular a un producto del catálogo.'), 0, 1);
            $pdf->SetTextColor(0);
        }

        $st = $db->prepare(
            'SELECT rr.Cantidad, rr.CostoUnitario, rr.Fecha, l.Descripcion, l.Tipo, COALESCE(u.Nombre, \'-\') AS usuario
             FROM RequisicionRecepciones rr
             INNER JOIN RequisicionLineas l ON l.LineaID = rr.LineaID
             LEFT JOIN Usuarios u ON u.UsuarioID = rr.UsuarioID
             WHERE l.RequisicionID = ? ORDER BY rr.Fecha, rr.RecepcionID'
        );
        $st->execute([(int) $r['RequisicionID']]);
        $recs = $st->fetchAll(PDO::FETCH_ASSOC);
        if ($recs) {
            $pdf->Ln(4);
            $pdf->seccion('Recepciones en almacén', count($recs) . ' registro(s)');
            $rc = [['Fecha', 32.0, 'L'], ['Producto', 74.0, 'L'], ['Recibió', 40.0, 'L'], ['Cantidad', 24.0, 'R'], ['Costo', 20.0, 'R']];
            $pdf->cabecera($rc);
            foreach ($recs as $i => $x) {
                $pdf->fila($rc, [
                    self::fecha((string) $x['Fecha'], true),
                    ReportePdf::corto((string) $x['Descripcion'], 46),
                    ReportePdf::corto((string) $x['usuario'], 24),
                    Unidades::formato((string) $x['Tipo'], (float) $x['Cantidad']),
                    $verCostos && $x['CostoUnitario'] !== null ? ReportePdf::dinero((float) $x['CostoUnitario']) : '-',
                ], ['fill' => $i % 2 === 1], $rc);
            }
        }

        return $pdf->Output('S');
    }

    private static function fecha(string $f, bool $hora = false): string
    {
        $t = strtotime($f);

        return $t ? date($hora ? 'd/m/Y H:i' : 'd/m/Y', $t) : '-';
    }
}
