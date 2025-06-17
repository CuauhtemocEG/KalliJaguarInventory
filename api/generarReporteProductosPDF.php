<?php
ob_start();
require_once('../fpdf/fpdf.php');
require_once('../controllers/mainController.php');

$fechaInicio = $_POST['fecha_desde'] ?? '';
$fechaFin = $_POST['fecha_hasta'] ?? '';

class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, utf8_decode('Resumen General de Productos Solicitados'), 0, 1, 'C');
        $this->Ln(5);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, utf8_decode('Página ') . $this->PageNo(), 0, 0, 'C');
    }
}

function formatearCantidadTotal($cantidad, $tipo) {
    if (strtolower($tipo) === 'pesable') {
        if ($cantidad >= 1.0) {
            return number_format($cantidad, 2) . ' Kg';
        } else {
            return number_format($cantidad * 1000, 0) . ' grs';
        }
    } else {
        return number_format($cantidad, 0) . ' Unidad(es)';
    }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);

if (!$fechaInicio || !$fechaFin) {
    $pdf->Cell(0, 10, utf8_decode('Por favor seleccione un rango de fechas.'), 0, 1);
} else {
    try {
        $conn = conexion();

        $query = "
            SELECT 
                p.Nombre,
                p.Tipo,
                SUM(m.Cantidad) AS TotalCantidad,
                p.PrecioUnitario,
                (SUM(m.Cantidad) * (p.PrecioUnitario * 1.16)) AS Subtotal
            FROM MovimientosInventario m
            JOIN Productos p ON m.ProductoID = p.ProductoID
            WHERE m.TipoMovimiento = 'Salida'
              AND m.FechaMovimiento BETWEEN :fechaInicio AND :fechaFin
            GROUP BY p.Nombre, p.Tipo, p.PrecioUnitario
            ORDER BY p.Nombre ASC
        ";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':fechaInicio', $fechaInicio);
        $stmt->bindParam(':fechaFin', $fechaFin);
        $stmt->execute();
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($productos) === 0) {
            $pdf->Cell(0, 10, utf8_decode('No se encontraron productos en el rango seleccionado.'), 0, 1);
        } else {
            $pdf->SetFillColor(200, 220, 255);
            $pdf->Cell(70, 7, utf8_decode('Producto'), 1, 0, 'C', true);
            $pdf->Cell(35, 7, utf8_decode('Cantidad'), 1, 0, 'C', true);
            $pdf->Cell(35, 7, utf8_decode('Precio Unitario'), 1, 0, 'C', true);
            $pdf->Cell(35, 7, utf8_decode('Subtotal (c/IVA)'), 1, 1, 'C', true);

            $totalGeneral = 0;

            foreach ($productos as $producto) {
                $pdf->Cell(70, 6, utf8_decode($producto['Nombre']), 1);
                $pdf->Cell(35, 6, utf8_decode(formatearCantidadTotal($producto['TotalCantidad'], $producto['Tipo'])), 1, 0, 'C');
                $precioIVA = $producto['PrecioUnitario'] * 1.16;
                $pdf->Cell(35, 6, '$' . number_format($precioIVA, 2), 1, 0, 'C');
                $pdf->Cell(35, 6, '$' . number_format($producto['Subtotal'], 2), 1, 1, 'C');
                $totalGeneral += $producto['Subtotal'];
            }

            $pdf->SetFont('Arial', 'B', 11);
            $pdf->Cell(140, 8, utf8_decode('Total General:'), 1);
            $pdf->Cell(35, 8, '$' . number_format($totalGeneral, 2), 1, 1, 'C');
        }

    } catch (PDOException $e) {
        $pdf->Cell(0, 10, utf8_decode('Error de conexión o consulta: ') . $e->getMessage(), 0, 1);
    }
}

ob_end_clean();
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="reporteProductos-' . $fechaInicio . '_'. $fechaFin . '.pdf"');
$pdf->Output('D', 'reporteProductos-' . $fechaInicio . '_'. $fechaFin . '.pdf');
exit;
