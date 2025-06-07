<?php
ob_start();
ini_set('display_errors', 1);
error_reporting(E_ALL & ~E_DEPRECATED);

require_once('../fpdf/fpdf.php');
require_once('../controllers/mainController.php');

$fechaInicio = $_POST['fecha_desde'] ?? '';
$fechaFin = $_POST['fecha_hasta'] ?? '';
class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, utf8_decode('Reporte de Solicitudes por Sucursal'), 0, 1, 'C');
        $this->Ln(5);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, utf8_decode('Página ') . $this->PageNo(), 0, 0, 'C');
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
                m.ComandaID,
                m.SucursalID,
                s.nombre,
                p.Nombre,
                m.Cantidad,
                m.PrecioFinal,
                (m.Cantidad * m.PrecioFinal) AS Subtotal
            FROM MovimientosInventario m
            JOIN Productos p ON m.ProductoID = p.ProductoID
            JOIN Sucursales s ON m.SucursalID = s.SucursalID
            WHERE m.TipoMovimiento = 'Salida'
              AND m.FechaMovimiento BETWEEN :fechaInicio AND :fechaFin
            ORDER BY m.SucursalID, m.ComandaID
        ";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':fechaInicio', $fechaInicio);
        $stmt->bindParam(':fechaFin', $fechaFin);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $datos = [];
        $totales = [];

        foreach ($rows as $row) {
            $sucursal = $row['NombreSucursal'];
            $comanda = $row['ComandaID'];

            if (!isset($datos[$sucursal])) $datos[$sucursal] = [];
            if (!isset($datos[$sucursal][$comanda])) $datos[$sucursal][$comanda] = [];

            $datos[$sucursal][$comanda][] = $row;

            if (!isset($totales[$sucursal])) $totales[$sucursal] = 0;
            $totales[$sucursal] += $row['Subtotal'];
        }

        $totalGeneral = 0;

        foreach ($datos as $sucursal => $comandas) {
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 10, utf8_decode("Sucursal: {$sucursal}"), 0, 1);
            $pdf->SetFont('Arial', '', 10);

            foreach ($comandas as $comandaID => $items) {
                $pdf->Cell(0, 8, utf8_decode("Comanda: {$comandaID}"), 0, 1);
                $pdf->SetFillColor(200, 220, 255);
                $pdf->Cell(80, 6, utf8_decode('Producto'), 1, 0, 'C', true);
                $pdf->Cell(30, 6, utf8_decode('Cantidad'), 1, 0, 'C', true);
                $pdf->Cell(30, 6, utf8_decode('Precio'), 1, 0, 'C', true);
                $pdf->Cell(30, 6, utf8_decode('Subtotal'), 1, 1, 'C', true);

                $totalComanda = 0;

                foreach ($items as $item) {
                    $pdf->Cell(80, 6, utf8_decode($item['NombreProducto']), 1);
                    $pdf->Cell(30, 6, $item['Cantidad'], 1, 0, 'C');
                    $pdf->Cell(30, 6, '$' . number_format($item['PrecioFinal'], 2), 1, 0, 'C');
                    $pdf->Cell(30, 6, '$' . number_format($item['Subtotal'], 2), 1, 1, 'C');
                    $totalComanda += $item['Subtotal'];
                }

                $pdf->SetFont('Arial', 'B', 10);
                $pdf->Cell(140, 6, utf8_decode('Total Comanda:'), 1);
                $pdf->Cell(30, 6, '$' . number_format($totalComanda, 2), 1, 1, 'C');
                $pdf->Ln(2);
                $pdf->SetFont('Arial', '', 10);
            }

            $pdf->SetFont('Arial', 'B', 11);
            $pdf->Cell(140, 6, utf8_decode("Total Sucursal {$sucursal}:"), 1);
            $pdf->Cell(30, 6, '$' . number_format($totales[$sucursal], 2), 1, 1, 'C');
            $pdf->Ln(4);
            $totalGeneral += $totales[$sucursal];
        }

        $pdf->SetFont('Arial', 'B', 13);
        $pdf->Cell(140, 8, utf8_decode('Total General:'), 1);
        $pdf->Cell(30, 8, '$' . number_format($totalGeneral, 2), 1, 1, 'C');

    } catch (PDOException $e) {
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 10, utf8_decode('Error de conexión o consulta: ') . $e->getMessage(), 0, 1);
    }
}
ob_end_clean();
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="reporte_solicitudes.pdf"');
$pdf->Output('D', 'reporte_solicitudes.pdf');
exit;