<?php
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

		if($rows['Cantidad'] > 1) {
			$txtDisponibilidad = '<h6 class="text-success mt-2">Disponible</h6>';
		} else {
			$txtDisponibilidad = '<h6 class="text-danger mt-2">No disponible</h6>';
		}

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
						<div class="d-flex flex-row align-items-center">
							<h4 class="mr-1">
								<p class="font-weight-bold">Precio Compra:</p> $'.$rows['PrecioUnitario'].'
							</h4>
							<h4 class="mr-1">
								<p class="font-weight-bold">Precio Venta:</p> $'. $rows['PrecioUnitario'] + ($rows['PrecioUnitario'] * 0.16).'
							</h4>
						</div>'
						. $txtDisponibilidad.'
						<div class="d-flex flex-column mt-4"><a href="index.php?page=updateProduct&idProductUp='.$rows['ProductoID'].'" class="btn btn-dark btn-sm" type="button">Actualizar Producto</a><a class="btn btn-secondary btn-sm mt-2 text-white" href="index.php?page=updateProductImage&idProductUp='.$rows['ProductoID'].'" type="button">Actualizar Imagen</a><a href="' . $url . $pagina . '&idProductDel=' . $rows['ProductoID'] . '" class="btn btn-danger btn-sm btn-sm mt-2">Eliminar</a></div>
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
