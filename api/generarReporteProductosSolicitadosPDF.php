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
    $query = "
        SELECT 
            p.Nombre AS nombre_producto,
            p.Cantidad AS stock,
            p.Tipo AS tipo_producto,
            SUM(m.Cantidad) AS total_solicitado
        FROM 
            MovimientosInventario m
        INNER JOIN 
            Productos p ON m.ProductoID = p.ProductoID
        WHERE 
            p.Tag = :tag
            AND m.FechaMovimiento BETWEEN :fecha_desde AND :fecha_hasta
            AND m.TipoMovimiento = 'Salida'";
    
    // Agregar filtro de tipo si está especificado
    if (!empty($tipo)) {
        $query .= " AND p.Tipo = :tipo";
    }
    
    $query .= "
        GROUP BY 
            p.ProductoID, p.Nombre, p.Cantidad, p.Tipo
        ORDER BY 
            total_solicitado DESC
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
    
    // Crear PDF
    $pdf = new FPDF();
    $pdf->AddPage();
    
    // Agregar logo con mejor proporción
    $logoPath = '../img/logo.png';
    if (file_exists($logoPath)) {
        // Posición centrada horizontalmente para el logo
        $pdf->Image($logoPath, 85, 15, 40, 0); // x, y, width, height (0 = mantener proporción)
    }
    
    // Encabezado principal
    $pdf->SetFont('Arial', 'B', 18);
    $pdf->Cell(0, 35, '', 0, 1); // Espacio para el logo (aumentado)
    $pdf->Cell(0, 10, utf8_decode('Kalli Jaguar Inventory'), 0, 1, 'C');
    
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 8, utf8_decode('Reporte de Productos Más Solicitados'), 0, 1, 'C');
    
    // Información del reporte
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(0, 6, utf8_decode("Tag Filtrado: $tag"), 0, 1, 'C');
    if (!empty($tipo)) {
        $pdf->Cell(0, 6, utf8_decode("Tipo: $tipo"), 0, 1, 'C');
    }
    $pdf->Cell(0, 6, utf8_decode("Período: $fechaDesde al $fechaHasta"), 0, 1, 'C');
    $pdf->Cell(0, 6, utf8_decode("Generado el: " . date('d/m/Y H:i:s')), 0, 1, 'C');
    
    $pdf->Ln(8);
    
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
            if ($tipoProducto == "Pesable") {
                $unidadSolicitado = ($producto['total_solicitado'] >= 1.0) ? 'Kg' : 'grs';
                $solicitadoFormateado = ($producto['total_solicitado'] >= 1.0) ? 
                    number_format($producto['total_solicitado'], 2) : 
                    number_format($producto['total_solicitado'], 3);
                $solicitadoTexto = $solicitadoFormateado . ' ' . $unidadSolicitado;
            } else {
                $solicitadoTexto = number_format($producto['total_solicitado'], 0) . ' Unidad(es)';
            }
            
            $pdf->Cell(8, 8, $contador, 1, 0, 'C', $fill);
            $pdf->Cell(85, 8, utf8_decode($nombre), 1, 0, 'L', $fill);
            $pdf->Cell(25, 8, $stockTexto, 1, 0, 'C', $fill);
            $pdf->Cell(30, 8, $solicitadoTexto, 1, 0, 'C', $fill);
            $pdf->Cell(32, 8, '', 1, 1, 'C', $fill); // Columna en blanco "Observaciones"
            
            $contador++;
        }
        
        // Resumen estadístico
        $totalProductos = count($productos);
        $totalSolicitado = array_sum(array_column($productos, 'total_solicitado'));
        
        $pdf->Ln(5);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(52, 115, 223);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(180, 8, 'RESUMEN ESTADÍSTICO', 1, 1, 'C', true);
        
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFillColor(240, 240, 240);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(90, 6, utf8_decode("Total de productos mostrados: $totalProductos"), 1, 0, 'L', true);
        $pdf->Cell(90, 6, utf8_decode("Total cantidad solicitada: " . number_format($totalSolicitado)), 1, 1, 'L', true);
    }
    
    // Pie de página profesional
    $pdf->Ln(10);
    
    // Línea separadora
    $pdf->SetDrawColor(52, 115, 223);
    $pdf->SetLineWidth(0.5);
    $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
    $pdf->Ln(5);
    
    $pdf->SetFont('Arial', 'I', 8);
    $pdf->SetTextColor(100, 100, 100);
    
    // Información de la empresa y fecha
    $pdf->Cell(90, 4, utf8_decode('Kalli Jaguar Inventory System'), 0, 0, 'L');
    $pdf->Cell(90, 4, utf8_decode('Página 1'), 0, 1, 'R');
    
    $pdf->Cell(90, 4, utf8_decode('Sistema de Gestión de Inventarios'), 0, 0, 'L');
    $pdf->Cell(90, 4, utf8_decode('Generado automáticamente'), 0, 1, 'R');
    
    // Nota importante
    $pdf->Ln(3);
    $pdf->SetFont('Arial', '', 7);
    $pdf->SetTextColor(150, 150, 150);
    $pdf->MultiCell(180, 3, utf8_decode('NOTA: La columna "Observaciones" está disponible para anotaciones manuales durante el uso del reporte. Los datos mostrados corresponden únicamente a movimientos de salida en el período especificado.'), 0, 'J');
    
    // Generar nombre del archivo
    $timestamp = date('Ymd_His');
    $filename = "reporte_productos_solicitados_{$tag}_{$timestamp}.pdf";    // Enviar headers finales
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
