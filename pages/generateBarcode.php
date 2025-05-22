<?php
require_once '../vendor/autoload.php';

use Picqer\Barcode\BarcodeGeneratorPNG;

// Parámetros desde URL
$upc = isset($_GET['upc']) ? $_GET['upc'] : '000000000000';
$nombre = isset($_GET['nombre']) ? urldecode($_GET['nombre']) : 'Producto';

// Validar formato EAN-13 (13 dígitos)
$upc = str_pad(preg_replace('/\D/', '', $upc), 13, '0', STR_PAD_LEFT);

$generator = new BarcodeGeneratorPNG();
$barcode = $generator->getBarcode($upc, $generator::TYPE_EAN_13, 3, 100);

// Cargar logo
$logoPath = '../img/404.png'; // cambia según tu ruta
$logo = imagecreatefrompng($logoPath);

// Crear imagen final
$barcodeImg = imagecreatefromstring($barcode);
$barcodeWidth = imagesx($barcodeImg);
$barcodeHeight = imagesy($barcodeImg);
$logoWidth = imagesx($logo);
$logoHeight = imagesy($logo);

$font = __DIR__ . '/arial.ttf'; // fuente TrueType
$fontSize = 14;

// Calcular dimensiones
$finalWidth = $logoWidth + $barcodeWidth + 40;
$finalHeight = max($logoHeight, $barcodeHeight) + 50;

$finalImg = imagecreatetruecolor($finalWidth, $finalHeight);
$white = imagecolorallocate($finalImg, 255, 255, 255);
$black = imagecolorallocate($finalImg, 0, 0, 0);
imagefilledrectangle($finalImg, 0, 0, $finalWidth, $finalHeight, $white);

// Copiar logo e imagen
imagecopy($finalImg, $logo, 10, 10, 0, 0, $logoWidth, $logoHeight);
imagecopy($finalImg, $barcodeImg, $logoWidth + 30, 10, 0, 0, $barcodeWidth, $barcodeHeight);

// Añadir texto del nombre abajo centrado
imagettftext($finalImg, $fontSize, 0, 20, $finalHeight - 10, $black, $font, $nombre);

// Salida
header('Content-Type: image/png');
imagepng($finalImg);

// Limpiar
imagedestroy($finalImg);
imagedestroy($logo);
imagedestroy($barcodeImg);
?>