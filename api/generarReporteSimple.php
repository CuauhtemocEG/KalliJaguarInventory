<?php
// Versión simplificada para pruebas
require_once '../fpdf/fpdf.php';
require_once '../controllers/mainController.php';

// Limpiar output buffer
if (ob_get_level()) {
    ob_end_clean();
}

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    $conexion = conexion();
    
    $fechaDesde = $_POST['fecha_desde'] ?? '2024-01-01';
    $fechaHasta = $_POST['fecha_hasta'] ?? '2024-12-31';
    $tag = $_POST['tag'] ?? '';
    $limite = $_POST['limite'] ?? '10';
    
    if (empty($tag)) {
        throw new Exception('Tag requerido');
    }
    
    // Consulta simplificada
    $query = "
        SELECT 
            p.Nombre AS nombre_producto,
            p.Cantidad AS stock,
            COALESCE(SUM(m.Cantidad), 0) AS total_solicitado
        FROM 
            Productos p
        LEFT JOIN 
            MovimientosInventario m ON m.ProductoID = p.ProductoID 
            AND m.TipoMovimiento = 'Salida'
            AND DATE(m.FechaMovimiento) BETWEEN :fecha_desde AND :fecha_hasta
        WHERE 
            p.Tag = :tag
        GROUP BY 
            p.ProductoID, p.Nombre, p.Cantidad
        HAVING
            total_solicitado > 0
        ORDER BY 
            total_solicitado DESC
        LIMIT 20
    ";
    
    $stmt = $conexion->prepare($query);
    $stmt->bindParam(':tag', $tag);
    $stmt->bindParam(':fecha_desde', $fechaDesde);
    $stmt->bindParam(':fecha_hasta', $fechaHasta);
    $stmt->execute();
    
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Headers para PDF
    header('Content-Type: application/pdf');
    header('Cache-Control: no-cache');
    
    // Crear PDF
    $pdf = new FPDF();
    $pdf->AddPage();
    
    // Título
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'Reporte de Productos Mas Solicitados', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, "Tag: $tag | Periodo: $fechaDesde al $fechaHasta", 0, 1, 'C');
    $pdf->Ln(10);
    
    // Encabezados
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(80, 10, 'Nombre del Producto', 1, 0, 'C');
    $pdf->Cell(30, 10, 'Stock', 1, 0, 'C');
    $pdf->Cell(30, 10, 'Solicitado', 1, 0, 'C');
    $pdf->Cell(40, 10, 'Observaciones', 1, 1, 'C');
    
    // Datos
    $pdf->SetFont('Arial', '', 9);
    
    if (empty($productos)) {
        $pdf->Cell(180, 10, 'No se encontraron productos para el periodo seleccionado', 1, 1, 'C');
    } else {
        foreach ($productos as $producto) {
            // Truncar nombre si es muy largo
            $nombre = strlen($producto['nombre_producto']) > 35 ? 
                     substr($producto['nombre_producto'], 0, 32) . '...' : 
                     $producto['nombre_producto'];
                     
            $pdf->Cell(80, 8, $nombre, 1, 0, 'L');
            $pdf->Cell(30, 8, $producto['stock'], 1, 0, 'C');
            $pdf->Cell(30, 8, $producto['total_solicitado'], 1, 0, 'C');
            $pdf->Cell(40, 8, '', 1, 1, 'C'); // Columna en blanco
        }
    }
    
    // Pie de página
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'I', 8);
    $pdf->Cell(0, 10, 'Generado el: ' . date('d/m/Y H:i:s'), 0, 1, 'R');
    
    // Enviar PDF
    $filename = "reporte_productos_{$tag}.pdf";
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $pdf->Output('D', $filename);
    
} catch (Exception $e) {
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}
?>
