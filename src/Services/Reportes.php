<?php

declare(strict_types=1);

namespace App\Services;

use PDO;

/** Reportes PDF del Gestor: comandas, consolidado de productos e inventario por etiqueta. */
final class Reportes
{
    private const ESTADOS = [
        'Abierto' => ['Pendiente', [180, 110, 0]],
        'En transito' => ['En tránsito', [0, 90, 170]],
        'Cerrado' => ['Entregado', [0, 120, 40]],
        'Cancelado' => ['Cancelado', [180, 0, 0]],
    ];

    private static function fechaCorta(string $iso): string
    {
        return date('d/m/Y', strtotime($iso));
    }

    private static function nombreSucursal(PDO $db, int $id): string
    {
        $st = $db->prepare('SELECT nombre FROM Sucursales WHERE SucursalID = ?');
        $st->execute([$id]);
        return (string) ($st->fetchColumn() ?: 'Sucursal #' . $id);
    }

    /** @return list<string> */
    private static function subtitulos(string $desde, string $hasta, string $sucursal): array
    {
        return [
            'Periodo: ' . self::fechaCorta($desde) . ' al ' . self::fechaCorta($hasta),
            'Sucursal: ' . $sucursal,
        ];
    }

    /** Pedidos del periodo agrupados por sucursal, con resumen y totales. */
    public static function comandas(PDO $db, string $desde, string $hasta, ?int $sucursalId, ?string $estado): string
    {
        $where = "m.TipoMovimiento = 'Salida' AND m.ComandaID IS NOT NULL AND m.FechaMovimiento BETWEEN :d AND :h";
        $p = [':d' => $desde . ' 00:00:00', ':h' => $hasta . ' 23:59:59'];
        if ($sucursalId) {
            $where .= ' AND m.SucursalID = :s';
            $p[':s'] = $sucursalId;
        }
        if ($estado !== null) {
            $where .= ' AND m.Status = :e';
            $p[':e'] = $estado;
        }
        $st = $db->prepare(
            "SELECT m.ComandaID, MIN(m.FechaMovimiento) AS fecha, MAX(m.FechaDelivery) AS entrega, MAX(m.Status) AS estado,
                    COALESCE(s.nombre, 'Sin sucursal') AS sucursal, COALESCE(u.Nombre, '-') AS solicitante,
                    COUNT(*) AS articulos, SUM(m.PrecioFinal) AS total
             FROM MovimientosInventario m
             LEFT JOIN Sucursales s ON s.SucursalID = m.SucursalID
             LEFT JOIN Usuarios u ON u.UsuarioID = m.UsuarioID
             WHERE $where
             GROUP BY m.ComandaID, s.nombre, u.Nombre
             ORDER BY sucursal, fecha, m.ComandaID"
        );
        $st->execute($p);
        $filas = $st->fetchAll(PDO::FETCH_ASSOC);

        $subs = self::subtitulos($desde, $hasta, $sucursalId ? self::nombreSucursal($db, $sucursalId) : 'Todas');
        if ($estado !== null) {
            $subs[] = 'Estado: ' . (self::ESTADOS[$estado][0] ?? $estado);
        }
        $pdf = new ReportePdf('Reporte de comandas', $subs, 'P');
        if (!$filas) {
            $pdf->vacio('No hay comandas con los filtros seleccionados.');
            return $pdf->Output('S');
        }

        $porSucursal = [];
        $validas = $canceladas = 0;
        $monto = 0.0;
        foreach ($filas as $f) {
            $porSucursal[$f['sucursal']][] = $f;
            if ($f['estado'] === 'Cancelado') {
                $canceladas++;
            } else {
                $validas++;
                $monto += (float) $f['total'];
            }
        }
        $pdf->kpis([
            ['Pedidos (sin cancelados)', (string) $validas],
            ['Importe c/IVA', ReportePdf::dinero($monto)],
            ['Ticket promedio', ReportePdf::dinero($validas ? $monto / $validas : 0)],
            ['Cancelados', (string) $canceladas],
        ]);

        // Resumen por sucursal
        if (count($porSucursal) > 1) {
            $cols = [['Sucursal', 80, 'L'], ['Pedidos', 25, 'R'], ['Artículos', 25, 'R'], ['Importe c/IVA', 38, 'R'], ['% del total', 22, 'R']];
            $pdf->seccion('Resumen por sucursal');
            $pdf->cabecera($cols);
            $i = 0;
            foreach ($porSucursal as $nombre => $lista) {
                $ok = array_filter($lista, static fn ($f) => $f['estado'] !== 'Cancelado');
                $imp = array_sum(array_map(static fn ($f) => (float) $f['total'], $ok));
                $pdf->fila($cols, [ReportePdf::corto($nombre, 44), (string) count($ok), (string) array_sum(array_map(static fn ($f) => (int) $f['articulos'], $ok)), ReportePdf::dinero($imp), $monto > 0 ? number_format($imp / $monto * 100, 1) . '%' : '-'], ['fill' => $i++ % 2 === 1], $cols);
            }
            $pdf->Ln(4);
        }

        // Detalle por sucursal
        $cols = [['Folio', 34, 'L'], ['Fecha', 24, 'L'], ['Entrega', 22, 'L'], ['Solicitante', 44, 'L'], ['Estado', 22, 'L'], ['Art.', 10, 'R'], ['Total c/IVA', 34, 'R']];
        foreach ($porSucursal as $nombre => $lista) {
            $ok = array_filter($lista, static fn ($f) => $f['estado'] !== 'Cancelado');
            $imp = array_sum(array_map(static fn ($f) => (float) $f['total'], $ok));
            $pdf->seccion($nombre, count($ok) . ' pedido(s) · ' . ReportePdf::dinero($imp));
            $pdf->cabecera($cols);
            foreach ($lista as $i => $f) {
                [$txt, $color] = self::ESTADOS[$f['estado']] ?? [$f['estado'], [0, 0, 0]];
                $cancel = $f['estado'] === 'Cancelado';
                $pdf->fila($cols, [
                    $f['ComandaID'], date('d/m/Y', strtotime($f['fecha'])), $f['entrega'] ? date('d/m/Y', strtotime($f['entrega'])) : '-',
                    ReportePdf::corto($f['solicitante'], 24), $txt, (string) $f['articulos'], $cancel ? '-' : ReportePdf::dinero((float) $f['total']),
                ], ['fill' => $i % 2 === 1, 'colores' => [4 => $color]], $cols);
            }
            $pdf->fila($cols, ['', '', '', '', 'Subtotal', '', ReportePdf::dinero($imp)], ['bold' => true, 'fill' => true], $cols);
            $pdf->Ln(4);
        }
        $pdf->seccion('TOTAL GENERAL', $validas . ' pedido(s) · ' . ReportePdf::dinero($monto));

        return $pdf->Output('S');
    }

    /** Productos surtidos en el periodo: matriz producto × sucursal, agrupada por categoría. */
    public static function consolidado(PDO $db, string $desde, string $hasta, ?int $sucursalId): string
    {
        $where = "m.TipoMovimiento = 'Salida' AND m.ComandaID IS NOT NULL AND m.Status <> 'Cancelado' AND m.FechaMovimiento BETWEEN :d AND :h";
        $p = [':d' => $desde . ' 00:00:00', ':h' => $hasta . ' 23:59:59'];
        if ($sucursalId) {
            $where .= ' AND m.SucursalID = :s';
            $p[':s'] = $sucursalId;
        }
        $st = $db->prepare(
            "SELECT pr.ProductoID, pr.Nombre, pr.Tipo, COALESCE(c.Nombre, 'Sin categoría') AS categoria,
                    COALESCE(s.nombre, 'Sin sucursal') AS sucursal, SUM(m.Cantidad) AS cantidad, SUM(m.PrecioFinal) AS importe,
                    COUNT(DISTINCT m.ComandaID) AS pedidos
             FROM MovimientosInventario m
             INNER JOIN Productos pr ON pr.ProductoID = m.ProductoID
             LEFT JOIN Categorias c ON c.CategoriaID = pr.CategoriaID
             LEFT JOIN Sucursales s ON s.SucursalID = m.SucursalID
             WHERE $where
             GROUP BY pr.ProductoID, pr.Nombre, pr.Tipo, c.Nombre, s.nombre
             ORDER BY categoria, pr.Nombre"
        );
        $st->execute($p);
        $filas = $st->fetchAll(PDO::FETCH_ASSOC);

        $subs = self::subtitulos($desde, $hasta, $sucursalId ? self::nombreSucursal($db, $sucursalId) : 'Todas');
        $pdf = new ReportePdf('Consolidado de productos', $subs, 'L');
        if (!$filas) {
            $pdf->vacio('No hay productos surtidos en el periodo seleccionado.');
            return $pdf->Output('S');
        }

        $sucursales = array_values(array_unique(array_column($filas, 'sucursal')));
        sort($sucursales);
        $productos = [];
        foreach ($filas as $f) {
            $k = $f['categoria'];
            $productos[$k][$f['ProductoID']]['info'] = $f;
            $productos[$k][$f['ProductoID']]['suc'][$f['sucursal']] = (float) $f['cantidad'];
            $productos[$k][$f['ProductoID']]['importe'] = ($productos[$k][$f['ProductoID']]['importe'] ?? 0) + (float) $f['importe'];
            $productos[$k][$f['ProductoID']]['cant'] = ($productos[$k][$f['ProductoID']]['cant'] ?? 0) + (float) $f['cantidad'];
        }

        $total = array_sum(array_column($filas, 'importe'));
        $numProd = array_sum(array_map('count', $productos));
        $pdf->kpis([
            ['Productos distintos', (string) $numProd],
            ['Categorías', (string) count($productos)],
            ['Sucursales', (string) count($sucursales)],
            ['Importe c/IVA', ReportePdf::dinero((float) $total)],
        ]);

        $ancho = $pdf->ancho();
        $fijo = 80 + 28 + 34;
        $wSuc = max(18.0, min(34.0, ($ancho - $fijo) / max(1, count($sucursales))));
        $cols = [['Producto', $ancho - $wSuc * count($sucursales) - 28 - 34, 'L']];
        foreach ($sucursales as $s) {
            $cols[] = [ReportePdf::corto($s, (int) max(8, $wSuc / 1.9)), $wSuc, 'R'];
        }
        $cols[] = ['Total', 28, 'R'];
        $cols[] = ['Importe c/IVA', 34, 'R'];
        $nombreMax = (int) ($cols[0][1] / 1.75);

        $totSuc = array_fill_keys($sucursales, 0.0);
        foreach ($productos as $cat => $lista) {
            $impCat = array_sum(array_column($lista, 'importe'));
            $pdf->seccion($cat, count($lista) . ' producto(s) · ' . ReportePdf::dinero($impCat));
            $pdf->cabecera($cols);
            $i = 0;
            foreach ($lista as $d) {
                $tipo = $d['info']['Tipo'];
                $celdas = [ReportePdf::corto($d['info']['Nombre'], $nombreMax)];
                foreach ($sucursales as $s) {
                    $c = $d['suc'][$s] ?? 0.0;
                    $celdas[] = $c > 0 ? Unidades::formato($tipo, $c) : '-';
                }
                $celdas[] = Unidades::formato($tipo, $d['cant']);
                $celdas[] = ReportePdf::dinero($d['importe']);
                $pdf->fila($cols, $celdas, ['fill' => $i++ % 2 === 1], $cols);
            }
            $pdf->Ln(3);
        }
        foreach ($filas as $f) {
            $totSuc[$f['sucursal']] += (float) $f['importe'];
        }
        $pdf->seccion('IMPORTE POR SUCURSAL', ReportePdf::dinero((float) $total));
        $c2 = [['Sucursal', 100, 'L'], ['Importe c/IVA', 40, 'R'], ['% del total', 30, 'R']];
        $pdf->cabecera($c2);
        $i = 0;
        arsort($totSuc);
        foreach ($totSuc as $s => $imp) {
            $pdf->fila($c2, [$s, ReportePdf::dinero($imp), $total > 0 ? number_format($imp / $total * 100, 1) . '%' : '-'], ['fill' => $i++ % 2 === 1], $c2);
        }

        return $pdf->Output('S');
    }

    /** Existencias actuales por etiqueta, con valor y estado de abasto. */
    public static function inventario(PDO $db, ?string $tag, ?string $tipo, bool $soloBajo): string
    {
        $sql = "SELECT p.ProductoID, p.Nombre, p.UPC, p.Tipo, p.Cantidad, p.StockMinimo, p.PrecioUnitario,
                       COALESCE(t.Nombre, 'Sin etiqueta') AS etiqueta
                FROM Productos p
                LEFT JOIN ProductoTags pt ON pt.ProductoID = p.ProductoID
                LEFT JOIN Tags t ON t.TagID = pt.TagID
                WHERE p.Activo = 1";
        $p = [];
        if ($tag !== null) {
            $sql .= ' AND t.Nombre = :tag';
            $p[':tag'] = $tag;
        }
        if ($tipo !== null) {
            $sql .= ' AND p.Tipo = :tipo';
            $p[':tipo'] = $tipo;
        }
        if ($soloBajo) {
            $sql .= ' AND p.Cantidad <= p.StockMinimo';
        }
        $st = $db->prepare($sql . ' ORDER BY etiqueta, p.Nombre');
        $st->execute($p);
        $filas = $st->fetchAll(PDO::FETCH_ASSOC);

        $subs = ['Existencias al ' . date('d/m/Y H:i'), 'Etiqueta: ' . ($tag ?? 'Todas') . ($tipo ? ' · Tipo: ' . $tipo : '') . ($soloBajo ? ' · Solo stock bajo' : '')];
        $pdf = new ReportePdf('Inventario por etiqueta', $subs, 'P');
        if (!$filas) {
            $pdf->vacio('No hay productos con los filtros seleccionados.');
            return $pdf->Output('S');
        }

        $unicos = [];
        foreach ($filas as $f) {
            $unicos[$f['ProductoID']] = $f;
        }
        $valor = array_sum(array_map(static fn ($f) => (float) $f['Cantidad'] * (float) $f['PrecioUnitario'], $unicos));
        $sin = count(array_filter($unicos, static fn ($f) => (float) $f['Cantidad'] <= 0));
        $bajo = count(array_filter($unicos, static fn ($f) => (float) $f['Cantidad'] > 0 && (float) $f['Cantidad'] <= (float) $f['StockMinimo']));
        $pdf->kpis([
            ['Productos', (string) count($unicos)],
            ['Valor (sin IVA)', ReportePdf::dinero((float) $valor)],
            ['Stock bajo', (string) $bajo],
            ['Sin stock', (string) $sin],
        ]);

        $grupos = [];
        foreach ($filas as $f) {
            $grupos[$f['etiqueta']][] = $f;
        }
        $cols = [['Producto', 56, 'L'], ['UPC', 26, 'L'], ['Stock', 24, 'R'], ['Mínimo', 22, 'R'], ['Estado', 18, 'C'], ['Reponer', 22, 'R'], ['Valor', 22, 'R']];
        foreach ($grupos as $nombre => $lista) {
            $v = array_sum(array_map(static fn ($f) => (float) $f['Cantidad'] * (float) $f['PrecioUnitario'], $lista));
            $pdf->seccion($nombre, count($lista) . ' producto(s) · ' . ReportePdf::dinero((float) $v));
            $pdf->cabecera($cols);
            foreach ($lista as $i => $f) {
                $stock = (float) $f['Cantidad'];
                $min = (float) $f['StockMinimo'];
                [$estado, $color] = $stock <= 0 ? ['Sin stock', [200, 30, 30]] : ($stock <= $min ? ['Bajo', [190, 110, 0]] : ['OK', [0, 130, 50]]);
                $reponer = $min - $stock;
                $pdf->fila($cols, [
                    ReportePdf::corto((string) $f['Nombre'], 32), (string) ($f['UPC'] ?? ''), Unidades::formato($f['Tipo'], $stock),
                    Unidades::formato($f['Tipo'], $min), $estado, $reponer > 0 ? Unidades::formato($f['Tipo'], $reponer) : '-',
                    ReportePdf::dinero($stock * (float) $f['PrecioUnitario']),
                ], ['fill' => $i % 2 === 1, 'colores' => [4 => $color]], $cols);
            }
            $pdf->Ln(3);
        }
        if (!$tag && count($filas) !== count($unicos)) {
            $pdf->SetFont('Arial', 'I', 7.5);
            $pdf->SetTextColor(120);
            $pdf->MultiCell(0, 4, ReportePdf::t('Los productos con varias etiquetas aparecen en cada una; los totales superiores cuentan cada producto una sola vez.'));
        }

        return $pdf->Output('S');
    }
}
