<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../vendor/autoload.php';

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
	$barcodeData = $generator->getBarcode($ean13, $generator::TYPE_EAN_13, 1.7 * $scale, 70 * $scale);

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

	ob_start();
	imagepng($finalImage);
	$imgData = ob_get_clean();

	imagedestroy($finalImage);
	imagedestroy($barcodeImage);
	imagedestroy($logo);

	return base64_encode($imgData);
}

$inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;
$tabla = "";

$campos = "Productos.ProductoID,Productos.UPC,Productos.Nombre as productName,Productos.Descripcion,Productos.PrecioUnitario,Productos.Cantidad,Productos.image,Productos.CategoriaID,Productos.UsuarioID,Productos.Tipo,Categorias.CategoriaID,Categorias.Nombre as CatName,Usuarios.UsuarioID,Usuarios.Nombre,Usuarios.Username";

if (isset($busqueda) && $busqueda != "") {

	$consulta_datos = "SELECT $campos FROM Productos INNER JOIN Categorias ON Productos.CategoriaID=Categorias.CategoriaID INNER JOIN Usuarios ON Productos.UsuarioID=Usuarios.UsuarioID WHERE Productos.UPC LIKE '%$busqueda%' OR Productos.Nombre LIKE '%$busqueda%' ORDER BY Productos.Nombre ASC LIMIT $inicio,$registros";

	$consulta_total = "SELECT COUNT(ProductoID) FROM Productos WHERE UPC LIKE '%$busqueda%' OR Nombre LIKE '%$busqueda%'";
} elseif ($categoria_id > 0) {

	$consulta_datos = "SELECT $campos FROM Productos INNER JOIN Categorias ON Productos.CategoriaID=Categorias.CategoriaID INNER JOIN Usuarios ON Productos.UsuarioID=Usuarios.UsuarioID WHERE Productos.CategoriaID='$categoria_id' ORDER BY Productos.Nombre ASC LIMIT $inicio,$registros";

	$consulta_total = "SELECT COUNT(ProductoID) FROM Productos WHERE CategoriaID='$categoria_id'";
} else {

	$consulta_datos = "SELECT $campos FROM Productos INNER JOIN Categorias ON Productos.CategoriaID=Categorias.CategoriaID INNER JOIN Usuarios ON Productos.UsuarioID=Usuarios.UsuarioID ORDER BY Productos.Nombre ASC LIMIT $inicio,$registros";

	$consulta_total = "SELECT COUNT(ProductoID) FROM Productos";
}

$conexion = conexion();

$datos = $conexion->query($consulta_datos);
$datos = $datos->fetchAll();

$total = $conexion->query($consulta_total);
$total = (int) $total->fetchColumn();

$Npaginas = ceil($total / $registros);

if ($total >= 1 && $pagina <= $Npaginas) {
	$contador = $inicio + 1;
	$pag_inicio = $inicio + 1;
	foreach ($datos as $rows) {
		$tabla .= '
<div class="container-fluid mt-3 mb-3">
	<div class="d-flex justify-content-center row">
		<div class="col-md-12">
			<div class="row p-2 bg-white border rounded">
				<div class="col-md-3 mt-1 is-align-items-center">';
		if (is_file("./img/producto/" . $rows['image'])) {
			$tabla .= '<img class="img-fluid img-responsive rounded product-image w-50" src="./img/producto/' . $rows['image'] . '">';
		} else {
			$tabla .= '<img class="img-fluid img-responsive rounded product-image w-50" src="./img/producto.png">';
		}
		$res = "";

		if ($rows['Tipo'] == "Pesable") {
			$res = "Kg";
			$unidades = $rows['Cantidad'];
		} else {
			$res = "Unidades";
			$unidades = (int) $rows['Cantidad'];
		}

		$txtDisponibilidad = "";

		if($rows['Cantidad'] >= 1) {
			$txtDisponibilidad = '<span class="badge badge-pill badge-success">Disponible</span>';
		} else {
			$txtDisponibilidad = '<span class="badge badge-pill badge-danger">No disponible</span>';
		}

		$barcodeId = 'barcodeImage_' . $rows['ProductoID'];
		$buttonId = 'downloadBtn_' . $rows['ProductoID'];

		$rawUpc = preg_replace('/\D/', '', $rows['UPC']);

		if (strlen($rawUpc) === 13) {
			$validUpc = $rawUpc;
		} elseif (strlen($rawUpc) >= 12) {
			$validUpc = generateValidEan13(substr($rawUpc, 0, 12));
		} else {
			$validUpc = str_pad($rawUpc, 12, '0', STR_PAD_LEFT);
			$validUpc = generateValidEan13($validUpc);
		}

		$nombreProducto = $rows['productName'];
		$logoPath = __DIR__ . '/../img/Logo-Negro.png';
		$fontPath = __DIR__ . '/../fonts/arial.ttf';

		$barcodeImgBase64 = generarCodigoConLogo($validUpc, $nombreProducto, $logoPath, $fontPath);


		$generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
		$barcode = base64_encode($generator->getBarcode($validUpc, $generator::TYPE_EAN_13));

		$tabla .= '
    <div class="mt-2 text-center">
        <img id="'.$barcodeId.'" src="data:image/png;base64,' . $barcodeImgBase64 . '" alt="Código de Barras" class="img-fluid" style="max-width:250px;" />
        <div class="mt-2">
            <button id="'. $buttonId .'" class="btn btn-outline-primary btn-sm mt-2">Descargar Código</button>
        </div>
    </div>';

		$tabla .= '</div>
					<div class="col-md-6 mt-1">
						<h5 class="h5">' . $rows['productName'] . '</h5>
						<div class="mt-1 mb-2 spec-1 h6">
							<span>Registrado por: '.$rows['Nombre'].'</span></span>
						</div>
						<p class="text-justify para mb-0">
							<strong>UPC:</strong> '. $rows['UPC'].'<br>
							<strong>Stock Disponible:</strong> ' .$unidades .' '. $res .'<br>
							<strong>Categoría:</strong> '.$rows['CatName'].'<br>
							<strong>Tipo de Inventario:</strong> '.$rows['Tipo'].'
						</p>
					</div>
					<div class="align-items-center align-content-center col-md-3 border-left mt-1">
						<div class="d-flex has-text-centered">
							<h4 class="mr-1 col-md-6">
								<p class="font-weight-bold">Precio Compra:</p> $'.$rows['PrecioUnitario'].'
							</h4>
							<h4 class="mr-1 col-md-6">
								<p class="font-weight-bold">Precio Venta:</p> $'. $rows['PrecioUnitario'] + ($rows['PrecioUnitario'] * 0.16).'
							</h4>
						</div>'
						. $txtDisponibilidad.'
						<div class="d-flex flex-column mt-4"><a href="index.php?page=updateProduct&idProductUp='.$rows['ProductoID'].'" class="btn btn-dark btn-sm" type="button">Actualizar Producto</a><a class="btn btn-secondary btn-sm mt-2 text-white" href="index.php?page=updateProductImage&idProductUp='.$rows['ProductoID'].'" type="button">Actualizar Imagen</a><a href="' . $url . $pagina . '&idProductDel=' . $rows['ProductoID'] . '" class="btn btn-danger btn-sm btn-sm mt-2">Eliminar</a>
						</div>
					</div>
			    </div>
			</div>
		</div>
	</div>';
		$contador++;
	}
	$pag_final = $contador - 1;
} else {
	if ($total >= 1) {
		$tabla .= '
				<p class="has-text-centered" >
					<a href="' . $url . '1" class="button is-link is-rounded is-small mt-4 mb-4">
						Haga clic acá para recargar el listado
					</a>
				</p>
			';
	} else {
		$tabla .= '
				<div class="alert alert-info" role="alert">
  					<b>No hay registros en el sistema</b>
				</div>';
	}
}

if ($total > 0 && $pagina <= $Npaginas) {
	$tabla .= '<p class="text-center">Mostrando productos <strong>' . $pag_inicio . '</strong> al <strong>' . $pag_final . '</strong> de un <strong>total de ' . $total . '</strong></p><br>';
}

$conexion = null;
echo $tabla;

if ($total >= 1 && $pagina <= $Npaginas) {
	echo paginador_tablas($pagina, $Npaginas, $url, 7);
}
