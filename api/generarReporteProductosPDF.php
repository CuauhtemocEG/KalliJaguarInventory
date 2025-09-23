<?php
ob_start();
require_once('../fpdf/fpdf.php');
require_once('../controllers/mainController.php');

$fechaInicio = $_POST['fecha_desde'] ?? date('Y-m-01 00:00:00');
$fechaFin = $_POST['fecha_hasta'] ?? date('Y-m-d 23:59:59');

class PDF extends FPDF {
    private $fechaInicio;
    private $fechaFin;
    
    function __construct($fechaInicio = '', $fechaFin = '') {
        parent::__construct();
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
    }
    
    function Header() {
        // Logo
        $logoPath = '../img/logo.png';
        if (file_exists($logoPath)) {
            $this->Image($logoPath, 10, 6, 30);
        }
        
        // Información de la empresa
        $this->SetFont('Arial', 'B', 16);
        $this->SetXY(50, 10);
        $this->Cell(0, 8, utf8_decode('Kalli Jaguar'), 0, 1);
        
        $this->SetFont('Arial', '', 10);
        $this->SetXY(50, 18);
        $this->Cell(0, 5, utf8_decode('Sistema de Gestión de Inventario'), 0, 1);
        $this->SetXY(50, 23);
        $this->Cell(0, 5, utf8_decode('Generado: ') . date('d/m/Y H:i:s'), 0, 1);
        
        // Información del reporte centrada
        $this->SetFont('Arial', 'B', 12);
        $this->SetXY(120, 10);
        $this->Cell(70, 6, utf8_decode('Reporte de Productos'), 0, 1, 'C');
        
        // Período en una sola línea, centrado
        $this->SetFont('Arial', 'B', 10);
        $this->SetXY(120, 18);
        $periodoTexto = 'Período Analizado';
        $this->Cell(70, 5, utf8_decode($periodoTexto), 0, 1, 'C');
        
        $this->SetFont('Arial', '', 9);
        $this->SetXY(120, 23);
        $fechaFormateada = date('d/m/Y H:i', strtotime($this->fechaInicio)) . ' al ' . date('d/m/Y H:i', strtotime($this->fechaFin));
        $this->Cell(70, 5, utf8_decode($fechaFormateada), 0, 1, 'C');
        
        // Línea separadora
        $this->SetY(35);
        $this->SetDrawColor(200, 200, 200);
        $this->Line(10, 35, 200, 35);
        
        // Título principal
        $this->SetY(40);
        $this->SetFont('Arial', 'B', 14);
        $this->SetFillColor(39, 174, 96);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(0, 10, utf8_decode('Resumen General de Productos Solicitados'), 1, 1, 'C', true);
        
        // Resetear colores
        $this->SetTextColor(0, 0, 0);
        $this->SetDrawColor(0, 0, 0);
        $this->Ln(5);
    }

    function Footer() {
        $this->SetY(-20);
        
        // Línea separadora
        $this->SetDrawColor(200, 200, 200);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        
        $this->Ln(2);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(100, 100, 100);
        
        // Información del pie
        $this->Cell(0, 5, utf8_decode('Kalli Jaguar Inventory - Sistema de Inventario'), 0, 1, 'C');
        $this->Cell(0, 5, utf8_decode('Página ') . $this->PageNo() . ' - Generado el ' . date('d/m/Y H:i:s'), 0, 0, 'C');
        
        // Resetear color
        $this->SetTextColor(0, 0, 0);
    }
    
    function ProductosHeader() {
        // Headers de la tabla con estilo profesional
        $this->SetFillColor(52, 152, 219);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 10);
        
        $this->Cell(70, 10, utf8_decode('PRODUCTO'), 1, 0, 'C', true);
        $this->Cell(35, 10, utf8_decode('CANTIDAD'), 1, 0, 'C', true);
        $this->Cell(40, 10, utf8_decode('PRECIO UNITARIO'), 1, 0, 'C', true);
        $this->Cell(45, 10, utf8_decode('SUBTOTAL (c/IVA)'), 1, 1, 'C', true);
        
        // Resetear colores
        $this->SetTextColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);
    }
    
    function TotalGeneral($total, $cantidadProductos) {
        $this->Ln(3);
        
        // Total general destacado
        $this->SetFont('Arial', 'B', 14);
        $this->SetFillColor(192, 57, 43);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(145, 12, utf8_decode('TOTAL GENERAL DEL PERÍODO'), 1, 0, 'R', true);
        $this->Cell(45, 12, '$' . number_format($total, 2), 1, 1, 'C', true);
        
        // Resetear colores
        $this->SetTextColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);
        
        // Información adicional
        $this->Ln(5);
        $this->SetFont('Arial', 'B', 10);
        $this->SetFillColor(52, 152, 219);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(0, 8, utf8_decode('📊 RESUMEN ESTADÍSTICO'), 1, 1, 'C', true);
        
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('Arial', '', 9);
        $this->Cell(95, 6, utf8_decode('Total de productos diferentes:'), 1, 0, 'L');
        $this->Cell(95, 6, $cantidadProductos . ' productos', 1, 1, 'C');
        
        $promedioProducto = $cantidadProductos > 0 ? $total / $cantidadProductos : 0;
        $this->Cell(95, 6, utf8_decode('Promedio de venta por producto:'), 1, 0, 'L');
        $this->Cell(95, 6, '$' . number_format($promedioProducto, 2), 1, 1, 'C');
        
        $this->Cell(95, 6, utf8_decode('Rango de fechas procesado:'), 1, 0, 'L');
        $this->Cell(95, 6, utf8_decode(date('d/m/Y', strtotime($this->fechaInicio)) . ' al ' . date('d/m/Y', strtotime($this->fechaFin))), 1, 1, 'C');
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

// Crear el PDF con las fechas
$pdf = new PDF($fechaInicio, $fechaFin);
$pdf->AddPage();
$pdf->SetFont('Arial', '', 9);

if (!$fechaInicio || !$fechaFin) {
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(231, 76, 60);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(0, 10, utf8_decode('ERROR: Por favor seleccione un rango de fechas válido.'), 1, 1, 'C', true);
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
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->SetFillColor(241, 196, 15);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->Cell(0, 10, utf8_decode('No se encontraron productos en el período seleccionado.'), 1, 1, 'C', true);
        } else {
            // Header de la tabla con estilo profesional
            $pdf->ProductosHeader();

            $totalGeneral = 0;
            $contador = 0;

            foreach ($productos as $producto) {
                $contador++;
                
                // Alternar color de fondo para mejor legibilidad
                $fill = ($contador % 2 == 0);
                if ($fill) {
                    $pdf->SetFillColor(248, 249, 250);
                }
                
                $pdf->SetFont('Arial', '', 9);
                $pdf->Cell(70, 7, utf8_decode(ucwords(strtolower($producto['Nombre']))), 1, 0, 'L', $fill);
                $pdf->Cell(35, 7, utf8_decode(formatearCantidadTotal($producto['TotalCantidad'], $producto['Tipo'])), 1, 0, 'C', $fill);
                $precioIVA = $producto['PrecioUnitario'] * 1.16;
                $pdf->Cell(40, 7, '$' . number_format($precioIVA, 2), 1, 0, 'C', $fill);
                $pdf->Cell(45, 7, '$' . number_format($producto['Subtotal'], 2), 1, 1, 'C', $fill);
                $totalGeneral += $producto['Subtotal'];
                
                // Resetear fill
                $pdf->SetFillColor(255, 255, 255);
            }

            // Total general con estilo profesional y estadísticas
            $pdf->TotalGeneral($totalGeneral, count($productos));
        }

    } catch (PDOException $e) {
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetFillColor(231, 76, 60);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(0, 10, utf8_decode('Error de conexión: ') . $e->getMessage(), 1, 1, 'C', true);
    }
}

ob_end_clean();
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="reporteProductos_' . date('Y-m-d_H-i', strtotime($fechaInicio)) . '_al_' . date('Y-m-d_H-i', strtotime($fechaFin)) . '.pdf"');
$pdf->Output('D', 'reporteProductos_' . date('Y-m-d_H-i', strtotime($fechaInicio)) . '_al_' . date('Y-m-d_H-i', strtotime($fechaFin)) . '.pdf');
exit;
