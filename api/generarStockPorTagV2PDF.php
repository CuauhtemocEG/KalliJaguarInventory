<?php
ob_start();
error_reporting(E_ERROR | E_PARSE); 
require_once('../fpdf/fpdf.php');
require_once('../controllers/mainController.php');

function convertirTexto($texto) {
    if (function_exists('iconv')) {
        return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $texto);
    } elseif (function_exists('mb_convert_encoding')) {
        return mb_convert_encoding($texto, 'ISO-8859-1', 'UTF-8');
    } else {
        return @utf8_decode($texto);
    }
}

class PDF extends FPDF {
    private $tagColor = '';
    private $tagName = '';
    private $reportNumber = '';
    private $firstPage = true;
    
    function Header() {
        $logoPath = '../img/Logo-Negro.png';
        if (file_exists($logoPath)) {
            $this->Image($logoPath, 15, 10, 12);
        }
        
        $this->SetFont('Arial', 'B', 10);
        $this->SetTextColor(33, 37, 41);
        $this->SetXY(130, 10);
        $this->Cell(65, 5, convertirTexto('KALLI INVENTORY'), 0, 1, 'R');
        
        $this->SetFont('Arial', '', 7);
        $this->SetTextColor(108, 117, 125);
        $this->SetX(130);
        $this->Cell(65, 3, convertirTexto('Sistema de Gestión de Inventario'), 0, 1, 'R');
        $this->SetX(130);
        $this->Cell(65, 3, convertirTexto('Reporte de Stock y Solicitud'), 0, 1, 'R');
        
        $this->SetY(26);
        $this->SetDrawColor(52, 58, 64);
        $this->SetLineWidth(0.3);
        $this->Line(15, 26, 195, 26);
        
        $this->SetY(30);
        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor(33, 37, 41);
        $this->Cell(0, 5, convertirTexto('ORDEN DE SOLICITUD DE PRODUCTOS'), 0, 1, 'C');
        
        if ($this->firstPage) {
            $this->SetFont('Arial', '', 8);
            $this->SetTextColor(108, 117, 125);
            
            $this->SetY(38);
            $this->SetFillColor(248, 249, 250);
            $this->SetDrawColor(206, 212, 218);
            $this->Rect(15, 38, 90, 18, 'FD');
            
            $this->SetXY(18, 40);
            $this->SetFont('Arial', 'B', 8);
            $this->SetTextColor(52, 58, 64);
            $this->Cell(25, 4, convertirTexto('Folio:'), 0, 0);
            $this->SetFont('Arial', '', 8);
            $this->SetTextColor(33, 37, 41);
            $this->Cell(50, 4, $this->reportNumber, 0, 1);
            
            $this->SetX(18);
            $this->SetFont('Arial', 'B', 8);
            $this->SetTextColor(52, 58, 64);
            $this->Cell(25, 4, convertirTexto('Fecha:'), 0, 0);
            $this->SetFont('Arial', '', 8);
            $this->SetTextColor(33, 37, 41);
            $this->Cell(50, 4, date('d/m/Y H:i:s'), 0, 1);
            
            $this->SetX(18);
            $this->SetFont('Arial', 'B', 8);
            $this->SetTextColor(52, 58, 64);
            $this->Cell(25, 4, convertirTexto('Categoría:'), 0, 0);
            $this->SetFont('Arial', '', 8);
            $this->SetTextColor(33, 37, 41);
            $this->Cell(50, 4, convertirTexto($this->tagName ?: 'Todos los Tags'), 0, 1);
            
            $this->SetFillColor(240, 253, 244);
            $this->SetDrawColor(34, 197, 94);
            $this->SetLineWidth(0.5);
            $this->Rect(110, 38, 85, 18, 'FD');
            
            $this->SetXY(113, 44);
            $this->SetFont('Arial', 'B', 10);
            $this->SetTextColor(22, 163, 74);
            $this->Cell(0, 5, convertirTexto('ORDEN DE COMPRA'), 0, 1, 'L');
            
            $this->SetTextColor(33, 37, 41);
            $this->Ln(6);
        } else {
            $this->Ln(3);
        }
    }

    function Footer() {
        $this->SetY(-20);
        
        $this->SetDrawColor(206, 212, 218);
        $this->Line(15, $this->GetY(), 195, $this->GetY());
        
        $this->Ln(2);
        
        $this->SetFont('Arial', 'I', 7);
        $this->SetTextColor(108, 117, 125);
        
        $this->SetX(15);
        $this->Cell(90, 3, convertirTexto('Documento generado por Kalli Inventory System'), 0, 0, 'L');
        
        $this->SetFont('Arial', 'B', 7);
        $this->Cell(0, 3, convertirTexto('Página ') . $this->PageNo() . ' de {nb}', 0, 0, 'C');
        
        $this->SetFont('Arial', 'I', 7);
        $this->Cell(0, 3, date('d/m/Y H:i'), 0, 0, 'R');
        
    }

    function setTagInfo($tagName, $tagColor) {
        $this->tagName = $tagName;
        $this->tagColor = $tagColor;
    }
    
    function setReportNumber($number) {
        $this->reportNumber = $number;
    }
    
    function setFirstPage($isFirst) {
        $this->firstPage = $isFirst;
    }

    function TituloSeccion($titulo, $color = '#6366f1') {
        $this->SetFont('Arial', 'B', 11);
        $this->SetTextColor(33, 37, 41);
        
        $rgb = $this->hexToRgb($color);
        $this->SetFillColor($rgb[0], $rgb[1], $rgb[2]);
        
        $this->Rect($this->GetX(), $this->GetY(), 3, 8, 'F');
        
        $this->SetX($this->GetX() + 5);
        $this->Cell(0, 8, convertirTexto($titulo), 0, 1, 'L');
        $this->Ln(2);
    }

    function hexToRgb($hex) {
        $hex = str_replace('#', '', $hex);
        if (strlen($hex) == 3) {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        return array($r, $g, $b);
    }

    function TablaResumen($totalProductos, $productosBajoStock, $totalItemsSolicitar) {
        $this->Ln(2);
        
        $this->SetFont('Arial', 'B', 10);
        $this->SetTextColor(33, 37, 41);
        
        $this->Cell(0, 6, convertirTexto('RESUMEN EJECUTIVO'), 0, 1, 'L');
        $this->Ln(3);
        
        $anchoCol = 58;
        $alto = 20;
        $espacio = 3;
        $yInicial = $this->GetY();
        
        $this->SetFillColor(240, 253, 244);
        $this->SetDrawColor(167, 243, 208);
        $this->SetLineWidth(0.5);
        $this->Rect(15, $yInicial, $anchoCol, $alto, 'FD');
        $this->SetXY(20, $yInicial + 4);
        $this->SetFont('Arial', '', 7);
        $this->SetTextColor(34, 197, 94);
        $this->Cell($anchoCol - 10, 4, convertirTexto('Total de Productos'), 0, 1, 'L');
        $this->SetXY(20, $this->GetY());
        $this->SetFont('Arial', 'B', 16);
        $this->SetTextColor(22, 163, 74);
        $this->Cell($anchoCol - 10, 8, $totalProductos, 0, 0, 'L');
        
        $xPos2 = 15 + $anchoCol + $espacio;
        if ($productosBajoStock > 0) {
            $this->SetFillColor(254, 242, 242);
            $this->SetDrawColor(252, 165, 165);
            $color = [239, 68, 68];
        } else {
            $this->SetFillColor(240, 253, 244);
            $this->SetDrawColor(167, 243, 208);
            $color = [34, 197, 94];
        }
        $this->SetLineWidth(0.5);
        $this->Rect($xPos2, $yInicial, $anchoCol, $alto, 'FD');
        $this->SetXY($xPos2 + 5, $yInicial + 4);
        $this->SetFont('Arial', '', 7);
        $this->SetTextColor($color[0], $color[1], $color[2]);
        $this->Cell($anchoCol - 10, 4, convertirTexto('Bajo Stock Mínimo'), 0, 1, 'L');
        $this->SetXY($xPos2 + 5, $this->GetY());
        $this->SetFont('Arial', 'B', 16);
        $this->Cell($anchoCol - 10, 8, $productosBajoStock, 0, 0, 'L');
        
        $xPos3 = 15 + ($anchoCol + $espacio) * 2;
        $this->SetFillColor(239, 246, 255);
        $this->SetDrawColor(147, 197, 253);
        $this->SetLineWidth(0.5);
        $this->Rect($xPos3, $yInicial, $anchoCol, $alto, 'FD');
        $this->SetXY($xPos3 + 5, $yInicial + 4);
        $this->SetFont('Arial', '', 7);
        $this->SetTextColor(59, 130, 246);
        $this->Cell($anchoCol - 10, 4, convertirTexto('Items a Solicitar'), 0, 1, 'L');
        $this->SetXY($xPos3 + 5, $this->GetY());
        $this->SetFont('Arial', 'B', 16);
        $this->Cell($anchoCol - 10, 8, $totalItemsSolicitar, 0, 0, 'L');
        
        $this->SetTextColor(33, 37, 41);
        $this->SetY($yInicial + $alto + 5);
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
        return number_format($cantidad, 0);
    }
}

function calcularASolicitar($stockActual, $stockMinimo) {
    $diferencia = $stockMinimo - $stockActual;
    return $diferencia > 0 ? $diferencia : 0;
}

try {
    date_default_timezone_set('America/Mexico_City');
    
    $tagFiltro = isset($_POST['tag']) ? trim($_POST['tag']) : null;
    $tipoFiltro = isset($_POST['tipo']) ? trim($_POST['tipo']) : null;
    
    $pdf = new PDF();
    $pdf->AliasNbPages();
    
    $reportNumber = 'SOL-' . date('Ymd') . '-' . substr(md5(uniqid(rand(), true)), 0, 6);
    $pdf->setReportNumber($reportNumber);
    
    $conn = conexion();

    $sql = "SELECT 
                p.ProductoID,
                p.Nombre,
                p.UPC,
                p.SKU,
                p.Cantidad as StockActual,
                p.StockMinimo,
                p.Tipo,
                p.Proveedor,
                t.Nombre as TagNombre,
                t.Color as TagColor,
                t.Icono as TagIcono
            FROM Productos p
            LEFT JOIN ProductoTags pt ON p.ProductoID = pt.ProductoID
            LEFT JOIN Tags t ON pt.TagID = t.TagID
            WHERE p.Activo = 1";
    
    $params = [];
    
    if ($tagFiltro) {
        $sql .= " AND t.Nombre = :tag";
        $params[':tag'] = $tagFiltro;
    }
    
    if ($tipoFiltro) {
        $sql .= " AND p.Tipo = :tipo";
        $params[':tipo'] = $tipoFiltro;
    }
    
    $sql .= " ORDER BY t.Nombre, p.Nombre";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($productos) === 0) {
        $pdf->AddPage();
        $pdf->SetFont('Arial', '', 12);
        $pdf->SetTextColor(108, 117, 125);
        $pdf->Ln(20);
        $pdf->Cell(0, 10, convertirTexto('No se encontraron productos con los filtros aplicados.'), 0, 1, 'C');
        $pdf->SetFont('Arial', 'I', 10);
        $pdf->Cell(0, 8, convertirTexto('Verifique los criterios de búsqueda e intente nuevamente.'), 0, 1, 'C');
    } else {
        $tags = [];
        foreach ($productos as $producto) {
            $tag = $producto['TagNombre'] ?: 'Sin Tag Asignado';
            if (!isset($tags[$tag])) {
                $tags[$tag] = [
                    'productos' => [],
                    'color' => $producto['TagColor'] ?: '#6c757d',
                    'icono' => $producto['TagIcono'] ?: 'fa-tag'
                ];
            }
            $tags[$tag]['productos'][] = $producto;
        }

        $esLaPrimeraPagina = true;
        foreach ($tags as $tagNombre => $tagData) {
            $pdf->setTagInfo($tagNombre, $tagData['color']);
            $pdf->setFirstPage($esLaPrimeraPagina);
            $pdf->AddPage();
            
            if ($esLaPrimeraPagina) {
                $esLaPrimeraPagina = false;
                $pdf->setFirstPage(false);
            }

            $items = $tagData['productos'];
            
            $totalProductos = count($items);
            $productosBajoStock = 0;
            $totalItemsSolicitar = 0;
            
            foreach ($items as $item) {
                $aSolicitar = calcularASolicitar($item['StockActual'], $item['StockMinimo']);
                if ($aSolicitar > 0) {
                    $productosBajoStock++;
                    $totalItemsSolicitar++;
                }
            }
            
            $pdf->TablaResumen($totalProductos, $productosBajoStock, $totalItemsSolicitar);
            
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetTextColor(33, 37, 41);
            $pdf->Cell(0, 6, convertirTexto('DETALLE DE PRODUCTOS'), 0, 1, 'L');
            $pdf->Ln(2);
            
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->SetFillColor(71, 85, 105);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetDrawColor(203, 213, 225);
            $pdf->SetLineWidth(0.3);
            
            $pdf->Cell(10, 8, convertirTexto('N°'), 1, 0, 'C', true);
            $pdf->Cell(95, 8, convertirTexto('PRODUCTO'), 1, 0, 'C', true);
            $pdf->Cell(20, 8, convertirTexto('TIPO'), 1, 0, 'C', true);
            $pdf->Cell(22, 8, convertirTexto('ACTUAL'), 1, 0, 'C', true);
            $pdf->Cell(22, 8, convertirTexto('MÍNIMO'), 1, 0, 'C', true);
            $pdf->Cell(26, 8, convertirTexto('A SOLICITAR'), 1, 1, 'C', true);
            
            $pdf->SetTextColor(33, 37, 41);
            
            $pdf->SetFont('Arial', '', 8);
            $contador = 1;
            
            foreach ($items as $item) {
                $aSolicitar = calcularASolicitar($item['StockActual'], $item['StockMinimo']);
                
                if ($aSolicitar > 0) {
                    $pdf->SetFillColor(254, 249, 237);
                    $fill = true;
                } else {
                    $pdf->SetFillColor(249, 250, 251);
                    $fill = true;
                }
                
                $pdf->SetFont('Arial', '', 8);
                $pdf->SetTextColor(71, 85, 105);
                $pdf->Cell(10, 7, $contador++, 1, 0, 'C', $fill);
                
                $pdf->SetFont('Arial', '', 8);
                $pdf->SetTextColor(30, 41, 59);
                $nombreProducto = substr(ucwords(strtolower($item['Nombre'])), 0, 55);
                $pdf->Cell(95, 7, convertirTexto($nombreProducto), 1, 0, 'L', $fill);
                
                $pdf->SetFont('Arial', '', 7);
                $pdf->SetTextColor(71, 85, 105);
                $pdf->Cell(20, 7, convertirTexto($item['Tipo']), 1, 0, 'C', $fill);
                
                if ($aSolicitar > 0) {
                    $pdf->SetFont('Arial', 'B', 8);
                    $pdf->SetTextColor(239, 68, 68);
                } else {
                    $pdf->SetFont('Arial', '', 7);
                    $pdf->SetTextColor(30, 41, 59);
                }
                $pdf->Cell(22, 7, formatearCantidad($item['StockActual'], $item['Tipo']), 1, 0, 'C', $fill);
                
                $pdf->SetFont('Arial', '', 7);
                $pdf->SetTextColor(100, 116, 139);
                $pdf->Cell(22, 7, formatearCantidad($item['StockMinimo'], $item['Tipo']), 1, 0, 'C', $fill);
                
                if ($aSolicitar > 0) {
                    $pdf->SetFont('Arial', 'B', 8);
                    $pdf->SetTextColor(255, 255, 255);
                    $pdf->SetFillColor(239, 68, 68);
                    $pdf->Cell(26, 7, formatearCantidad($aSolicitar, $item['Tipo']), 1, 1, 'C', true);
                } else {
                    $pdf->SetFont('Arial', 'B', 7);
                    $pdf->SetTextColor(34, 197, 94);
                    $pdf->Cell(26, 7, convertirTexto('OK'), 1, 1, 'C', $fill);
                }
            }
            
            $pdf->Ln(4);
            $pdf->SetFont('Arial', 'I', 7);
            $pdf->SetTextColor(108, 117, 125);
            $pdf->MultiCell(0, 3, convertirTexto('NOTA: Los productos marcados en rojo requieren reabastecimiento urgente. Verifique disponibilidad con el proveedor antes de realizar el pedido.'), 0, 'L');
            
        }
    }

    ob_end_clean();
    header('Content-Type: application/pdf');
    $filename = 'orden_solicitud_' . ($tagFiltro ? $tagFiltro . '_' : '') . date('Y-m-d_H-i-s') . '.pdf';
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $pdf->Output('D', $filename);
    exit;

} catch (PDOException $e) {
    ob_end_clean();
    header('Content-Type: text/plain');
    echo 'Error al generar el reporte: ' . $e->getMessage();
    exit;
}
