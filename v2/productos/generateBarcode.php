<?php
ob_start();
session_name("INV");
session_start();

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../controllers/mainController.php';

use Picqer\Barcode\BarcodeGeneratorPNG;

function generateValidEan13($code12)
{
    $sum = 0;
    for ($i = 0; $i < 12; $i++) {
        $digit = (int) $code12[$i];
        $sum += ($i % 2 === 0) ? $digit : $digit * 3;
    }
    $checkDigit = (10 - ($sum % 10)) % 10;
    return $code12 . $checkDigit;
}

function generarCodigoConLogo($ean13, $nombreProducto, $logoPath, $fontPath, $scale = 1.5)
{
    $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
    $barcodeData = $generator->getBarcode($ean13, $generator::TYPE_EAN_13, 2 * $scale, 45 * $scale);
    
    $barcodeImage = imagecreatefromstring($barcodeData);
    $barcodeWidth = imagesx($barcodeImage);
    $barcodeHeight = imagesy($barcodeImage);
    
    $logo = imagecreatefrompng($logoPath);
    $logoWidth = imagesx($logo);
    $logoHeight = imagesy($logo);
    
    $padding = 16;
    $maxLogoHeight = $barcodeHeight * 1.5;
    
    if ($logoHeight > $maxLogoHeight) {
        $ratio = $maxLogoHeight / $logoHeight;
        $newLogoWidth = (int)($logoWidth * $ratio);
        $newLogoHeight = (int)$maxLogoHeight;
        
        $resizedLogo = imagecreatetruecolor($newLogoWidth, $newLogoHeight);
        imagesavealpha($resizedLogo, true);
        $transColor = imagecolorallocatealpha($resizedLogo, 0, 0, 0, 127);
        imagefill($resizedLogo, 0, 0, $transColor);
        
        imagecopyresampled($resizedLogo, $logo, 0, 0, 0, 0, $newLogoWidth, $newLogoHeight, $logoWidth, $logoHeight);
        imagedestroy($logo);
        $logo = $resizedLogo;
        $logoWidth = $newLogoWidth;
        $logoHeight = $newLogoHeight;
    }
    
    $finalWidth = $barcodeWidth + $logoWidth + ($padding * 3);
    $textHeight = 30;
    $finalHeight = max($barcodeHeight, $logoHeight) + $textHeight + ($padding * 2) + 20;
    
    $finalImage = imagecreatetruecolor($finalWidth, $finalHeight);
    $white = imagecolorallocate($finalImage, 255, 255, 255);
    $black = imagecolorallocate($finalImage, 0, 0, 0);
    imagefilledrectangle($finalImage, 0, 0, $finalWidth, $finalHeight, $white);
    
    imagecopy($finalImage, $barcodeImage, $padding, $padding, 0, 0, $barcodeWidth, $barcodeHeight);
    
    $logoY = $padding + (int)(($barcodeHeight - $logoHeight) / 2);
    imagecopy($finalImage, $logo, $barcodeWidth + 2 * $padding, $logoY, 0, 0, $logoWidth, $logoHeight);
    
    $fontSize = 19;
    $textBox = imagettfbbox($fontSize, 0, $fontPath, $nombreProducto);
    $textWidth = $textBox[2] - $textBox[0];
    $textX = (int)(($finalWidth - $textWidth) / 2);
    $textY = max($barcodeHeight, $logoHeight) + $padding + 18;
    imagettftext($finalImage, $fontSize, 0, $textX, $textY, $black, $fontPath, $nombreProducto);
    
    $eanFontSize = 16;
    $textBox2 = imagettfbbox($eanFontSize, 0, $fontPath, $ean13);
    $textWidth2 = $textBox2[2] - $textBox2[0];
    $textX2 = (int)(($finalWidth - $textWidth2) / 2);
    $textY2 = $textY + 30;
    imagettftext($finalImage, $eanFontSize, 0, $textX2, $textY2, $black, $fontPath, $ean13);
    
    return $finalImage;
}

try {
    // Verificar que el usuario esté autenticado
    if (!isset($_SESSION['id'])) {
        throw new Exception('No autenticado');
    }
    
    if (!isset($_GET['productoId'])) {
        throw new Exception('ProductoID requerido');
    }
    
    $productoId = (int)$_GET['productoId'];
    
    // Obtener datos del producto
    $conexion = conexion();
    $stmt = $conexion->prepare("SELECT Nombre, UPC FROM Productos WHERE ProductoID = ?");
    $stmt->execute([$productoId]);
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$producto) {
        throw new Exception('Producto no encontrado');
    }
    
    // Procesar UPC
    $rawUpc = preg_replace('/\D/', '', $producto['UPC']);
    
    if (strlen($rawUpc) === 13) {
        $validUpc = $rawUpc;
    } elseif (strlen($rawUpc) >= 12) {
        $validUpc = generateValidEan13(substr($rawUpc, 0, 12));
    } else {
        $validUpc = str_pad($rawUpc, 12, '0', STR_PAD_LEFT);
        $validUpc = generateValidEan13($validUpc);
    }
    
    $logoPath = __DIR__ . '/../../img/Logo-Negro.png';
    $fontPath = __DIR__ . '/../../fonts/arial.ttf';
    
    // Verificar que los archivos existan
    if (!file_exists($logoPath)) {
        throw new Exception('Logo no encontrado: ' . $logoPath);
    }
    if (!file_exists($fontPath)) {
        throw new Exception('Fuente no encontrada: ' . $fontPath);
    }
    
    $barcodeImage = generarCodigoConLogo($validUpc, $producto['Nombre'], $logoPath, $fontPath);
    
    // Limpiar buffer y enviar headers
    ob_end_clean();
    header('Content-Type: image/png');
    header('Cache-Control: no-cache, must-revalidate');
    
    // Enviar imagen
    imagepng($barcodeImage);
    imagedestroy($barcodeImage);
    
} catch (Exception $e) {
    // Limpiar buffer y enviar headers
    ob_end_clean();
    header('Content-Type: image/png');
    
    // Generar imagen de error
    $errorImg = imagecreate(400, 100);
    $bgColor = imagecolorallocate($errorImg, 255, 255, 255);
    $textColor = imagecolorallocate($errorImg, 255, 0, 0);
    imagestring($errorImg, 5, 10, 40, 'Error: ' . $e->getMessage(), $textColor);
    imagepng($errorImg);
    imagedestroy($errorImg);
}
exit();
