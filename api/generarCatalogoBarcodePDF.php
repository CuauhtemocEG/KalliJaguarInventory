<?php
ob_start();
require_once('../fpdf/fpdf.php');
require_once('../controllers/mainController.php');

function convertirTexto($texto) {
    if (function_exists('iconv')) {
        return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $texto);
    } elseif (function_exists('mb_convert_encoding')) {
        return mb_convert_encoding($texto, 'ISO-8859-1', 'UTF-8');
    } else {
        return $texto;
    }
}

class CatalogoPDF extends FPDF {
    private $catalogoTitle = '';
    private $totalProductos = 0;
    private $fechaGeneracion = '';
    
    function __construct($title = 'Catálogo de Productos con Códigos de Barra') {
        parent::__construct();
        $this->catalogoTitle = $title;
        $this->fechaGeneracion = date('d/m/Y H:i');
    }
    
    function Header() {
        $logoPath = __DIR__ . '/../img/Logo-Negro.png';
        if (file_exists($logoPath)) {
            $this->Image($logoPath, 10, 6, 12);
        }
        
        $this->SetFont('Arial', 'B', 14);
        $this->SetX(40);
        $this->Cell(0, 6, convertirTexto($this->catalogoTitle), 0, 1, 'L');
        
        $this->SetFont('Arial', 'I', 8);
        $this->SetX(40);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 4, convertirTexto('Generado: ' . $this->fechaGeneracion), 0, 1, 'L');
        $this->SetTextColor(0, 0, 0);
        
        $this->Ln(3);
        $this->SetDrawColor(200, 200, 200);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(2);
    }
    
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(128, 128, 128);
        $this->Cell(95, 10, convertirTexto('Total: ' . $this->totalProductos . ' productos'), 0, 0, 'L');
        $this->Cell(95, 10, convertirTexto('Pág. ') . $this->PageNo() . '/{nb}', 0, 0, 'R');
    }
    
    function setTotalProductos($total) {
        $this->totalProductos = $total;
    }
    
    function TituloTag($tag, $cantidad) {
        $this->SetFont('Arial', 'B', 11);
        $this->SetFillColor(45, 45, 45);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(0, 8, convertirTexto("  $tag  ($cantidad productos)"), 0, 1, 'L', true);
        $this->SetTextColor(0, 0, 0);
        $this->Ln(2);
    }
}

function obtenerBarcodeImage($productoId) {
    $url = 'https://kallijaguar-inventory.com/v2/productos/generateBarcode.php?productoId=' . $productoId;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    
    $imageData = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($httpCode !== 200 || empty($imageData)) {
        throw new Exception("Error al obtener barcode: HTTP $httpCode - $error");
    }
    
    $tempFile = tempnam(sys_get_temp_dir(), 'bc_') . '.png';
    file_put_contents($tempFile, $imageData);
    
    return $tempFile;
}

try {
    $tagFiltro = isset($_POST['tag']) && !empty($_POST['tag']) ? $_POST['tag'] : null;
    $tipoFiltro = isset($_POST['tipo']) && !empty($_POST['tipo']) ? $_POST['tipo'] : null;
    
    $conexion = conexion();
    
    $query = "SELECT DISTINCT p.ProductoID, p.Nombre, p.UPC, p.Tipo, p.PrecioUnitario, 
                     COALESCE(t.Nombre, 'Sin Tag') as Tag
              FROM Productos p
              LEFT JOIN ProductoTags pt ON p.ProductoID = pt.ProductoID
              LEFT JOIN Tags t ON pt.TagID = t.TagID
              WHERE p.Activo = 1";
    $params = [];
    
    if ($tagFiltro) {
        $query .= " AND t.Nombre = :tag";
        $params[':tag'] = $tagFiltro;
    }
    
    if ($tipoFiltro) {
        $query .= " AND p.Tipo = :tipo";
        $params[':tipo'] = $tipoFiltro;
    }
    
    $query .= " ORDER BY Tag ASC, p.Nombre ASC";
    
    $stmt = $conexion->prepare($query);
    $stmt->execute($params);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($productos) === 0) {
        throw new Exception('No se encontraron productos');
    }
    
    // Organizar por tag
    $productosPorTag = [];
    foreach ($productos as $producto) {
        $tag = $producto['Tag'] ?: 'Sin Tag';
        if (!isset($productosPorTag[$tag])) {
            $productosPorTag[$tag] = [];
        }
        $productosPorTag[$tag][] = $producto;
    }
    
    // Crear PDF
    $titleSuffix = $tagFiltro ? " - $tagFiltro" : '';
    $titleSuffix .= $tipoFiltro ? " - $tipoFiltro" : '';
    $pdf = new CatalogoPDF('Catálogo de Códigos de Barra' . $titleSuffix);
    $pdf->AliasNbPages();
    $pdf->setTotalProductos(count($productos));
    
    $tempFiles = [];
    
    // Configuración para 3 columnas
    $columnas = 3;
    $margenIzq = 10;
    $anchoUtil = 190;
    $espacioEntreColumnas = 3;
    $anchoColumna = ($anchoUtil - (($columnas - 1) * $espacioEntreColumnas)) / $columnas;
    $altoFila = 38;
    
    foreach ($productosPorTag as $tag => $productosTag) {
        $pdf->AddPage();
        $pdf->TituloTag($tag, count($productosTag));
        
        $columna = 0;
        $y = $pdf->GetY();
        
        foreach ($productosTag as $index => $producto) {
            try {
                // Obtener imagen del barcode
                $tempFile = obtenerBarcodeImage($producto['ProductoID']);
                $tempFiles[] = $tempFile;
                
                // Calcular posición X según la columna
                $x = $margenIzq + ($columna * ($anchoColumna + $espacioEntreColumnas));
                
                // Agregar imagen del barcode
                $pdf->Image($tempFile, $x, $y, $anchoColumna);
                
                // Agregar nombre del producto debajo
                $pdf->SetXY($x, $y + $altoFila - 10);
                $pdf->SetFont('Arial', 'B', 7);
                $nombreCorto = strlen($producto['Nombre']) > 28 ? substr($producto['Nombre'], 0, 25) . '...' : $producto['Nombre'];
                $pdf->MultiCell($anchoColumna, 3, convertirTexto($nombreCorto), 0, 'C');
                
                // Agregar tipo y precio
                $pdf->SetXY($x, $y + $altoFila - 5);
                $pdf->SetFont('Arial', '', 6);
                $pdf->SetTextColor(100, 100, 100);
                $tipo = $producto['Tipo'] === 'Pesable' ? 'Pesable' : 'Unidad';
                $precioConImpuesto = $producto['PrecioUnitario'] * 1.16;
                $precio = '$' . number_format($precioConImpuesto, 2);
                $info = "$tipo | $precio";
                $pdf->Cell($anchoColumna, 3, convertirTexto($info), 0, 0, 'C');
                $pdf->SetTextColor(0, 0, 0);
                
                $columna++;
                
                // Nueva fila después de 3 columnas
                if ($columna >= $columnas) {
                    $columna = 0;
                    $y += $altoFila + 2;
                    
                    // Nueva página si es necesario
                    if ($y > 250) {
                        $pdf->AddPage();
                        $pdf->TituloTag($tag, count($productosTag));
                        $y = $pdf->GetY();
                    }
                }
                
            } catch (Exception $e) {
                error_log("Error con producto {$producto['ProductoID']}: " . $e->getMessage());
                continue;
            }
        }
    }
    
    // Limpiar archivos temporales
    foreach ($tempFiles as $file) {
        if (file_exists($file)) {
            @unlink($file);
        }
    }
    
    // Limpiar todos los buffers
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // Enviar PDF
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="catalogo_barcodes_' . date('Ymd_His') . '.pdf"');
    $pdf->Output('D', 'catalogo_barcodes_' . date('Ymd_His') . '.pdf');
    
} catch (Exception $e) {
    if (isset($tempFiles)) {
        foreach ($tempFiles as $file) {
            if (file_exists($file)) {
                @unlink($file);
            }
        }
    }
    
    error_log("Error en catálogo: " . $e->getMessage());
    
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
exit();
