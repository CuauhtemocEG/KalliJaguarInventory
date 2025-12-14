<?php
ob_start();
ini_set('display_errors', 1);
error_reporting(E_ALL & ~E_DEPRECATED);

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
        $this->Cell(70, 6, utf8_decode('Reporte de Ventas'), 0, 1, 'C');
        
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
        $this->SetFillColor(52, 73, 94);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(0, 10, utf8_decode('Reporte detallado por Sucursales y Comandas'), 1, 1, 'C', true);
        
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
    
    function SucursalHeader($nombreSucursal) {
        $this->Ln(3);
        
        // Fondo para el nombre de la sucursal
        $this->SetFillColor(41, 128, 185);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, utf8_decode("Sucursal: " . strtoupper($nombreSucursal)), 1, 1, 'L', true);
        
        // Resetear colores
        $this->SetTextColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);
        $this->Ln(2);
    }
    
    function ComandaHeader($comandaID) {
        $this->SetFont('Arial', 'B', 10);
        $this->SetFillColor(236, 240, 241);
        $this->Cell(0, 8, utf8_decode("Comanda N° " . $comandaID), 1, 1, 'L', true);
        
        // Headers de la tabla
        $this->SetFillColor(149, 165, 166);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 9);
        
        $this->Cell(85, 8, utf8_decode('PRODUCTO'), 1, 0, 'C', true);
        $this->Cell(25, 8, utf8_decode('CANTIDAD'), 1, 0, 'C', true);
        $this->Cell(25, 8, utf8_decode('P. UNIT.'), 1, 0, 'C', true);
        $this->Cell(25, 8, utf8_decode('IVA (16%)'), 1, 0, 'C', true);
        $this->Cell(30, 8, utf8_decode('SUBTOTAL'), 1, 1, 'C', true);
        
        // Resetear colores
        $this->SetTextColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);
    }
    
    function TotalComanda($total) {
        $this->SetFont('Arial', 'B', 10);
        $this->SetFillColor(230, 126, 34);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(160, 8, utf8_decode('TOTAL COMANDA'), 1, 0, 'R', true);
        $this->Cell(30, 8, '$' . number_format($total, 2), 1, 1, 'C', true);
        
        // Resetear colores
        $this->SetTextColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);
        $this->Ln(3);
    }
    
    function TotalSucursal($nombreSucursal, $total) {
        $this->Ln(2);
        $this->SetFont('Arial', 'B', 12);
        $this->SetFillColor(39, 174, 96);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(160, 10, utf8_decode('TOTAL SUCURSAL: ' . strtoupper($nombreSucursal)), 1, 0, 'R', true);
        $this->Cell(30, 10, '$' . number_format($total, 2), 1, 1, 'C', true);
        
        // Resetear colores
        $this->SetTextColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);
        $this->Ln(8);
    }
    
    function TotalGeneral($total) {
        $this->Ln(3);
        $this->SetFont('Arial', 'B', 14);
        $this->SetFillColor(192, 57, 43);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(160, 12, utf8_decode('TOTAL GENERAL DEL PERÍODO'), 1, 0, 'R', true);
        $this->Cell(30, 12, '$' . number_format($total, 2), 1, 1, 'C', true);
        
        // Resetear colores
        $this->SetTextColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);
    }
}

function formatearCantidad($cantidad, $tipo) {
    if (strtolower($tipo) === 'pesable') {
        if ($cantidad >= 1.0) {
            return number_format($cantidad, 2) . ' Kg';
        } else {
            return number_format($cantidad * 1000, 0) . ' g';
        }
    } else {
        return number_format($cantidad, 0) . ' Unid.';
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
    $pdf->Cell(0, 10, utf8_decode('⚠️ ERROR: Por favor seleccione un rango de fechas válido.'), 1, 1, 'C', true);
} else {
    try {
        $conn = conexion();

        // Consulta mejorada con cálculos exactos como el PDF anterior
        $query = "
            SELECT 
                m.ComandaID,
                m.SucursalID,
                s.nombre AS NombreSucursal,
                p.Nombre AS NombreProducto,
                p.Tipo,
                m.Cantidad,
                p.PrecioUnitario,
                m.PrecioFinal,
                m.PrecioFinal AS Subtotal,
                m.FechaMovimiento,
                m.Status
            FROM MovimientosInventario m
            JOIN Productos p ON m.ProductoID = p.ProductoID
            JOIN Sucursales s ON m.SucursalID = s.SucursalID
            WHERE m.TipoMovimiento = 'Salida'
              AND m.FechaMovimiento BETWEEN :fechaInicio AND :fechaFin
              AND m.Status != 'Cancelado'
            ORDER BY s.nombre, m.ComandaID, p.Nombre
        ";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':fechaInicio', $fechaInicio);
        $stmt->bindParam(':fechaFin', $fechaFin);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rows)) {
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->SetFillColor(241, 196, 15);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->Cell(0, 10, utf8_decode('No se encontraron registros en el período seleccionado.'), 1, 1, 'C', true);
        } else {
            $datos = [];
            $totalesSucursal = [];
            $totalComandas = [];

            // Organizar datos por sucursal y comanda
            foreach ($rows as $row) {
                $sucursal = $row['NombreSucursal'];
                $comanda = $row['ComandaID'];

                if (!isset($datos[$sucursal])) $datos[$sucursal] = [];
                if (!isset($datos[$sucursal][$comanda])) $datos[$sucursal][$comanda] = [];

                $datos[$sucursal][$comanda][] = $row;

                // Calcular totales usando la misma lógica que el PDF anterior
                if (!isset($totalesSucursal[$sucursal])) $totalesSucursal[$sucursal] = 0;
                if (!isset($totalComandas[$sucursal][$comanda])) $totalComandas[$sucursal][$comanda] = 0;
                
                $subtotal = $row['Subtotal']; // Usar el subtotal calculado en la consulta
                $totalesSucursal[$sucursal] += $subtotal;
                $totalComandas[$sucursal][$comanda] += $subtotal;
            }

            $totalGeneral = 0;
            $contadorSucursales = 0;

            foreach ($datos as $sucursal => $comandas) {
                $contadorSucursales++;
                
                $pdf->SucursalHeader($sucursal);

                $contadorComandas = 0;
                foreach ($comandas as $comandaID => $items) {
                    $contadorComandas++;
                    
                    $pdf->ComandaHeader($comandaID);

                    foreach ($items as $item) {
                        $pdf->SetFont('Arial', '', 8);
                        
                        $fill = (count($items) % 2 == 0);
                        if ($fill) {
                            $pdf->SetFillColor(248, 249, 250);
                        }
                        
                        $precioConIVA = $item['PrecioUnitario'] * 1.16;
                        $ivaCalculado = $item['PrecioUnitario'] * 0.16;
                        $subtotalItem = $item['PrecioFinal']; // Usar el mismo valor que el dashboard
                        
                        $pdf->Cell(85, 6, utf8_decode($item['NombreProducto']), 1, 0, 'L', $fill);
                        $pdf->Cell(25, 6, utf8_decode(formatearCantidad($item['Cantidad'], $item['Tipo'])), 1, 0, 'C', $fill);
                        $pdf->Cell(25, 6, '$' . number_format($precioConIVA, 2), 1, 0, 'C', $fill);
                        $pdf->Cell(25, 6, '$' . number_format($ivaCalculado * $item['Cantidad'], 2), 1, 0, 'C', $fill);
                        $pdf->Cell(30, 6, '$' . number_format($subtotalItem, 2), 1, 1, 'C', $fill);
                        
                        // Resetear fill
                        $pdf->SetFillColor(255, 255, 255);
                    }

                    // Total de la comanda
                    $pdf->TotalComanda($totalComandas[$sucursal][$comandaID]);
                }

                // Total de la sucursal (destacado)
                $pdf->TotalSucursal($sucursal, $totalesSucursal[$sucursal]);
                $totalGeneral += $totalesSucursal[$sucursal];
                
                // Agregar nueva página si no es la última sucursal
                if ($contadorSucursales < count($datos)) {
                    $pdf->AddPage();
                }
            }

            // Total general (muy destacado)
            $pdf->TotalGeneral($totalGeneral);
        }

    } catch (PDOException $e) {
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetFillColor(231, 76, 60);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(0, 10, utf8_decode('❌ Error de conexión: ') . $e->getMessage(), 1, 1, 'C', true);
    }
}
ob_end_clean();
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="reporteKalli'.$fechaInicio.'-'.$fechaFin.'.pdf"');
$pdf->Output('D', 'reporteKalli'.$fechaInicio.'-'.$fechaFin.'.pdf');
exit;