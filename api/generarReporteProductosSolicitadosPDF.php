<?php
// NO mostrar errores en el output para evitar corromper el PDF
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once '../fpdf/fpdf.php';
require_once '../controllers/mainController.php';

// Limpiar cualquier output previo
ob_clean();

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

try {
    $conexion = conexion();
    if (!$conexion) {
        throw new Exception('No se pudo establecer conexión con la base de datos');
    }
    
    $fechaDesde = $_POST['fecha_desde'] ?? '';
    $fechaHasta = $_POST['fecha_hasta'] ?? '';
    $tag = $_POST['tag'] ?? '';
    $tipo = $_POST['tipo'] ?? '';
    $limite = $_POST['limite'] ?? '';
    
    if (empty($fechaDesde) || empty($fechaHasta) || empty($tag)) {
        throw new Exception('Faltan parámetros requeridos');
    }
    
    // Validar fechas
    if (strtotime($fechaDesde) > strtotime($fechaHasta)) {
        throw new Exception('La fecha desde no puede ser mayor que la fecha hasta');
    }
    
    // Consulta para obtener productos más solicitados por tag
    // Ahora incluye TODOS los productos del tag, incluso si no se han solicitado
    $query = "
        SELECT 
            p.Nombre AS nombre_producto,
            p.Cantidad AS stock,
            p.Tipo AS tipo_producto,
            COALESCE(SUM(m.Cantidad), 0) AS total_solicitado
        FROM 
            Productos p
        LEFT JOIN 
            MovimientosInventario m ON p.ProductoID = m.ProductoID 
            AND m.FechaMovimiento BETWEEN :fecha_desde AND :fecha_hasta
            AND m.TipoMovimiento = 'Salida'
        WHERE 
            p.Tag = :tag";
    
    // Agregar filtro de tipo si está especificado
    if (!empty($tipo)) {
        $query .= " AND p.Tipo = :tipo";
    }
    
    $query .= "
        GROUP BY 
            p.ProductoID, p.Nombre, p.Cantidad, p.Tipo
        ORDER BY 
            total_solicitado DESC, p.Nombre ASC
    ";
    
    if (!empty($limite)) {
        $query .= " LIMIT :limite";
    }
    
    $stmt = $conexion->prepare($query);
    if (!$stmt) {
        throw new Exception('Error preparando la consulta: ' . implode(', ', $conexion->errorInfo()));
    }
    $stmt->bindParam(':tag', $tag);
    $stmt->bindParam(':fecha_desde', $fechaDesde);
    $stmt->bindParam(':fecha_hasta', $fechaHasta);
    
    if (!empty($tipo)) {
        $stmt->bindParam(':tipo', $tipo);
    }
    
    if (!empty($limite)) {
        $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
    }
    
    $ejecutado = $stmt->execute();
    if (!$ejecutado) {
        throw new Exception('Error ejecutando la consulta: ' . implode(', ', $stmt->errorInfo()));
    }
    
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Limpiar buffer antes de generar PDF
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    // Establecer headers para PDF
    header('Content-Type: application/pdf');
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
    
    // Crear PDF con clase personalizada
    class PDFProductosSolicitados extends FPDF {
        private $tag;
        private $tipo;
        private $fechaDesde;
        private $fechaHasta;
        
        function __construct($tag = '', $tipo = '', $fechaDesde = '', $fechaHasta = '') {
            parent::__construct();
            $this->tag = $tag;
            $this->tipo = $tipo;
            $this->fechaDesde = $fechaDesde;
            $this->fechaHasta = $fechaHasta;
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
            
            // Tag y período en una sola línea, centrado
            $this->SetFont('Arial', 'B', 10);
            $this->SetXY(120, 18);
            $periodoTexto = 'Tag: ' . $this->tag;
            $this->Cell(70, 5, utf8_decode($periodoTexto), 0, 1, 'C');
            
            $this->SetFont('Arial', '', 9);
            $this->SetXY(120, 23);
            $fechaFormateada = date('d/m/Y', strtotime($this->fechaDesde)) . ' al ' . date('d/m/Y', strtotime($this->fechaHasta));
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
            $this->Cell(0, 10, utf8_decode('Reporte de Productos por Tag con Solicitudes'), 1, 1, 'C', true);
            
            // Información adicional
            if (!empty($this->tipo)) {
                $this->SetFont('Arial', '', 10);
                $this->SetFillColor(236, 240, 241);
                $this->SetTextColor(0, 0, 0);
                $this->Cell(0, 6, utf8_decode("Filtrado por tipo: " . $this->tipo), 1, 1, 'C', true);
            }
            
            // Resetear colores
            $this->SetTextColor(0, 0, 0);
            $this->SetDrawColor(0, 0, 0);
            $this->Ln(5);
        }

        function Footer() {
            $this->SetY(-25);
            
            // Línea separadora
            $this->SetDrawColor(200, 200, 200);
            $this->Line(10, $this->GetY(), 200, $this->GetY());
            
            $this->Ln(2);
            $this->SetFont('Arial', 'I', 8);
            $this->SetTextColor(100, 100, 100);
            
            // Información del pie
            $this->Cell(0, 5, utf8_decode('Kalli Jaguar Inventory - Sistema de Inventario'), 0, 1, 'C');
            $this->Cell(0, 5, utf8_decode('Página ') . $this->PageNo() . ' - Generado el ' . date('d/m/Y H:i:s'), 0, 1, 'C');
            
            // Resetear color
            $this->SetTextColor(0, 0, 0);
        }
    }

    $pdf = new PDFProductosSolicitados($tag, $tipo, $fechaDesde, $fechaHasta);
    $pdf->AddPage();
    
    // Encabezados de tabla con diseño profesional
    $pdf->SetFillColor(52, 115, 223); // Color azul corporativo
    $pdf->SetTextColor(255, 255, 255); // Texto blanco
    $pdf->SetFont('Arial', 'B', 10);
    
    $pdf->Cell(8, 10, '#', 1, 0, 'C', true);
    $pdf->Cell(85, 10, utf8_decode('Nombre del Producto'), 1, 0, 'C', true);
    $pdf->Cell(25, 10, 'Stock', 1, 0, 'C', true);
    $pdf->Cell(30, 10, utf8_decode('Total Solicitado'), 1, 0, 'C', true);
    $pdf->Cell(32, 10, 'Observaciones', 1, 1, 'C', true);
    
    // Restaurar color de texto para el contenido
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', '', 9);
    
    if (empty($productos)) {
        $pdf->SetFillColor(240, 240, 240);
        $pdf->Cell(180, 12, utf8_decode('No se encontraron productos para el período seleccionado'), 1, 1, 'C', true);
    } else {
        $contador = 1;
        foreach ($productos as $producto) {
            // Alternar colores de fila para mejor legibilidad
            $fill = ($contador % 2 == 0);
            $pdf->SetFillColor(248, 249, 250);
            
            // Truncar nombre si es muy largo
            $nombre = strlen($producto['nombre_producto']) > 40 ? 
                     substr($producto['nombre_producto'], 0, 37) . '...' : 
                     $producto['nombre_producto'];
            
            // Aplicar formato según el tipo de producto
            $tipoProducto = $producto['tipo_producto'];
            
            // Formatear stock
            if ($tipoProducto == "Pesable") {
                $unidadStock = ($producto['stock'] >= 1.0) ? 'Kg' : 'grs';
                $stockFormateado = ($producto['stock'] >= 1.0) ? 
                    number_format($producto['stock'], 2) : 
                    number_format($producto['stock'], 3);
                $stockTexto = $stockFormateado . ' ' . $unidadStock;
            } else {
                $stockTexto = number_format($producto['stock'], 0) . ' Unidad(es)';
            }
            
            // Formatear total solicitado
            if ($producto['total_solicitado'] == 0) {
                $solicitadoTexto = 'No solicitado';
            } else {
                if ($tipoProducto == "Pesable") {
                    $unidadSolicitado = ($producto['total_solicitado'] >= 1.0) ? 'Kg' : 'grs';
                    $solicitadoFormateado = ($producto['total_solicitado'] >= 1.0) ? 
                        number_format($producto['total_solicitado'], 2) : 
                        number_format($producto['total_solicitado'], 3);
                    $solicitadoTexto = $solicitadoFormateado . ' ' . $unidadSolicitado;
                } else {
                    $solicitadoTexto = number_format($producto['total_solicitado'], 0) . ' Unidad(es)';
                }
            }
            
            $pdf->Cell(8, 8, $contador, 1, 0, 'C', $fill);
            $pdf->Cell(85, 8, utf8_decode($nombre), 1, 0, 'L', $fill);
            $pdf->Cell(25, 8, $stockTexto, 1, 0, 'C', $fill);
            $pdf->Cell(30, 8, $solicitadoTexto, 1, 0, 'C', $fill);
            $pdf->Cell(32, 8, '', 1, 1, 'C', $fill); // Columna en blanco "Observaciones"
            
            $contador++;
        }
    }
    
    // Nota importante específica para productos solicitados
    $pdf->Ln(5);
    $pdf->SetFont('Arial', '', 7);
    $pdf->SetTextColor(150, 150, 150);
    $pdf->MultiCell(180, 3, utf8_decode('NOTA: Este reporte muestra TODOS los productos del tag seleccionado. La columna "Total Solicitado" muestra las cantidades solicitadas en el período especificado, mostrando "No solicitado" para productos sin movimientos. La columna "Observaciones" está disponible para anotaciones manuales.'), 0, 'J');
    
    // Generar nombre del archivo
    $timestamp = date('Ymd_His');
    $filename = "reporte_productos_solicitados_{$tag}_{$timestamp}.pdf";
    
    // Enviar headers finales
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($pdf->Output('S')));
    
    // Enviar el PDF
    $pdf->Output('D', $filename);
    
} catch (Exception $e) {
    error_log("Error en generarReporteProductosSolicitadosPDF.php: " . $e->getMessage());
    
    // Limpiar cualquier output del PDF
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}
?>
