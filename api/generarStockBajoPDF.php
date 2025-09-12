<?php
// Headers CORS para permitir solicitudes desde diferentes subdominios
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Manejar OPTIONS request para preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once('../fpdf/fpdf.php');
require_once('../controllers/mainController.php');

class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, utf8_decode('Productos con Inventario Bajo por Categoría'), 0, 1, 'C');
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

    $stmt = $conn->query("SELECT p.Nombre, p.Cantidad, p.Tipo, c.Nombre AS Categoria FROM Productos p INNER JOIN Categorias c ON p.CategoriaID = c.CategoriaID WHERE p.Cantidad < 5 ORDER BY c.Nombre, p.Nombre");
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($productos) === 0) {
        $pdf->Cell(0, 10, utf8_decode('No hay productos con inventario bajo.'), 0, 1);
    } else {
        $categorias = [];
        foreach ($productos as $producto) {
            $categorias[$producto['Categoria']][] = $producto;
        }

        foreach ($categorias as $categoria => $items) {
            $pdf->TituloSeccion("Categoría: " . $categoria);
            $pdf->SetFillColor(200, 220, 255);
            $pdf->Cell(100, 7, 'Producto', 1, 0, 'C', true);
            $pdf->Cell(60, 7, 'Cantidad', 1, 1, 'C', true);

            foreach ($items as $item) {
                $pdf->Cell(100, 6, utf8_decode(ucwords(strtolower($item['Nombre']))), 1);
                $pdf->Cell(60, 6, utf8_decode(formatearCantidad($item['Cantidad'], $item['Tipo'])), 1, 1, 'C');
            }
            $pdf->Ln(5);
        }
    }

} catch (PDOException $e) {
    $pdf->Cell(0, 10, 'Error al obtener datos: ' . $e->getMessage(), 0, 1);
}

ob_end_clean();
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="stock_bajo_por_categoria.pdf"');
$pdf->Output('D', 'stock_bajo_por_categoria.pdf');
exit;