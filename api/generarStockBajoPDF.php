<?php
require_once('../fpdf/fpdf.php');
require_once('../controllers/mainController.php');

class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, utf8_decode('Productos con Inventario Bajo'), 0, 1, 'C');
        $this->Ln(5);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, utf8_decode('Página ') . $this->PageNo(), 0, 0, 'C');
    }

    function TituloSeccion($titulo) {
        $this->SetFont('Arial', 'B', 12);
        $this->SetFillColor(230, 230, 230);
        $this->Cell(0, 10, utf8_decode($titulo), 0, 1, 'L', true);
    }
}

function formatearCantidad($cantidad, $tipo) {
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

try {
    $conn = conexion();

    // Productos pesables con stock bajo
    $stmtPesables = $conn->query("SELECT Nombre, Cantidad, Tipo FROM Productos WHERE Tipo='Pesable' AND Cantidad < 5");
    $pesables = $stmtPesables->fetchAll(PDO::FETCH_ASSOC);

    // Productos por unidad con stock bajo
    $stmtUnidades = $conn->query("SELECT Nombre, Cantidad, Tipo FROM Productos WHERE Tipo='Unidad' AND Cantidad < 5");
    $unidades = $stmtUnidades->fetchAll(PDO::FETCH_ASSOC);

    if (count($pesables) === 0 && count($unidades) === 0) {
        $pdf->Cell(0, 10, utf8_decode('No hay productos con inventario bajo.'), 0, 1);
    } else {
        // Sección Pesables
        if (count($pesables) > 0) {
            $pdf->TituloSeccion('Productos Pesables con Stock Bajo');
            $pdf->SetFillColor(200, 220, 255);
            $pdf->Cell(100, 7, 'Producto', 1, 0, 'C', true);
            $pdf->Cell(60, 7, 'Cantidad', 1, 1, 'C', true);
            foreach ($pesables as $p) {
                $pdf->Cell(100, 6, utf8_decode(ucwords(strtolower($p['nombre']))), 1);
                $pdf->Cell(60, 6, utf8_decode(formatearCantidad($p['Cantidad'], $p['Tipo'])), 1, 1, 'C');
            }
            $pdf->Ln(5);
        }

        // Sección Unidades
        if (count($unidades) > 0) {
            $pdf->TituloSeccion('Productos por Unidad con Stock Bajo');
            $pdf->SetFillColor(200, 220, 255);
            $pdf->Cell(100, 7, 'Producto', 1, 0, 'C', true);
            $pdf->Cell(60, 7, 'Cantidad', 1, 1, 'C', true);
            foreach ($unidades as $u) {
                $pdf->Cell(100, 6, utf8_decode($u['Nombre']), 1);
                $pdf->Cell(60, 6, utf8_decode(formatearCantidad($u['Cantidad'], $u['Tipo'])), 1, 1, 'C');
            }
        }
    }

} catch (PDOException $e) {
    $pdf->Cell(0, 10, 'Error al obtener datos: ' . $e->getMessage(), 0, 1);
}

ob_end_clean();
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="stock_bajo.pdf"');
$pdf->Output('D', 'stock_bajo.pdf');
exit; ?>
