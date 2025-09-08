<?php
// Habilitar reporte de errores para debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../fpdf/fpdf.php';
require_once '../controllers/mainController.php';

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
    // Log de debugging
    error_log("Iniciando generación de reporte de productos solicitados");
    
    $conexion = conexion();
    if (!$conexion) {
        throw new Exception('No se pudo establecer conexión con la base de datos');
    }
    
    $fechaDesde = $_POST['fecha_desde'] ?? '';
    $fechaHasta = $_POST['fecha_hasta'] ?? '';
    $tag = $_POST['tag'] ?? '';
    $limite = $_POST['limite'] ?? '';
    
    error_log("Parámetros recibidos: fechaDesde=$fechaDesde, fechaHasta=$fechaHasta, tag=$tag, limite=$limite");
    
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
            SUM(m.CantidadSolicitada) AS total_solicitado
        FROM 
            MovimientosInventario m
        INNER JOIN 
            Productos p ON m.ProductoID = p.ProductoID
        WHERE 
            p.Tag = :tag
            AND m.Fecha BETWEEN :fecha_desde AND :fecha_hasta
            AND m.TipoMovimiento = 'Salida'
        GROUP BY 
            p.ProductoID, p.Nombre, p.Cantidad
        ORDER BY 
            total_solicitado DESC
    ";
    
    if (!empty($limite)) {
        $query .= " LIMIT :limite";
    }
    
    error_log("Consulta SQL: " . $query);
    
    $stmt = $conexion->prepare($query);
    if (!$stmt) {
        throw new Exception('Error preparando la consulta: ' . implode(', ', $conexion->errorInfo()));
    }
    $stmt->bindParam(':tag', $tag);
    $stmt->bindParam(':fecha_desde', $fechaDesde);
    $stmt->bindParam(':fecha_hasta', $fechaHasta);
    
    if (!empty($limite)) {
        $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
    }
    
    $ejecutado = $stmt->execute();
    if (!$ejecutado) {
        throw new Exception('Error ejecutando la consulta: ' . implode(', ', $stmt->errorInfo()));
    }
    
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("Productos encontrados: " . count($productos));
    
    // Establecer header para PDF solo si todo está bien
    header('Content-Type: application/pdf');
    
    // Crear PDF
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    
    // Título
    $pdf->Cell(0, 10, utf8_decode('Reporte de Productos Más Solicitados'), 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, utf8_decode("Tag: $tag | Período: $fechaDesde al $fechaHasta"), 0, 1, 'C');
    $pdf->Ln(10);
    
    // Encabezados de tabla
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(70, 10, utf8_decode('Nombre del Producto'), 1, 0, 'C');
    $pdf->Cell(25, 10, 'Stock', 1, 0, 'C');
    $pdf->Cell(30, 10, utf8_decode('Total Solicitado'), 1, 0, 'C');
    $pdf->Cell(40, 10, 'Solicitado', 1, 1, 'C');
    
    // Datos
    $pdf->SetFont('Arial', '', 9);
    
    if (empty($productos)) {
        $pdf->Cell(165, 10, utf8_decode('No se encontraron productos para el período seleccionado'), 1, 1, 'C');
    } else {
        foreach ($productos as $producto) {
            $pdf->Cell(70, 8, utf8_decode($producto['nombre_producto']), 1, 0, 'L');
            $pdf->Cell(25, 8, $producto['stock'], 1, 0, 'C');
            $pdf->Cell(30, 8, $producto['total_solicitado'], 1, 0, 'C');
            $pdf->Cell(40, 8, '', 1, 1, 'C'); // Columna en blanco "Solicitado"
        }
    }
    
    // Pie de página
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'I', 8);
    $pdf->Cell(0, 10, utf8_decode('Generado el: ' . date('d/m/Y H:i:s')), 0, 1, 'R');
    
    $filename = "reporte_productos_solicitados_{$tag}_{$fechaDesde}_{$fechaHasta}.pdf";
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $pdf->Output('D', $filename);
    
} catch (Exception $e) {
    error_log("Error en generarReporteProductosSolicitadosPDF.php: " . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage(), 'debug' => true]);
}
?>
