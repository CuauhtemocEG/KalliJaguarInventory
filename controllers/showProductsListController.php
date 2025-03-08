<?php
$inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;
$tabla = "";

$campos = "Productos.ProductoID,Productos.UPC,Productos.Nombre as nombreProducto,Productos.PrecioUnitario,Productos.Cantidad,Productos.image,Productos.CategoriaID as productCategory,Productos.UsuarioID,Categorias.CategoriaID,Categorias.Nombre as categoryName,Usuarios.UsuarioID,Usuarios.Nombre as userName";

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

	#Contenido de la card de productos
	$tabla .= '<div class="container-fluid mb-3">
				<div class="row">';
	foreach ($datos as $rows) {

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

		$tabla .= '
				<div class="col-md-3">
				<div class="card" style="margin-bottom: 10px;">';
		if (is_file("./img/producto/" . $rows['image'])) {
			$tabla .= '<img class="card-img-top mx-auto d-block img-responsive w-50" src="./img/producto/' . $rows['image'] . '">';
		} else {
			$tabla .= '<img class="card-img-top mx-auto d-block img-responsive w-50" src="./img/producto.png">';
		}
		$tabla .= '</img>
			        <div class="card-body">
                        <h5 class="card-title"><strong>' . $rows['nombreProducto'] . '</strong></h5>
						<hr>
			              <p class="card-text">
			                <strong>UPC:</strong> ' . $rows['UPC'] . '<br>
                            <strong>Precio:</strong> $' . $rows['PrecioUnitario'] . '<br>
							<strong>Disponible:</strong> ' . $rows['Cantidad'] . ' '.$unidades.'<br>
							<strong>Categoría:</strong> ' . $rows['categoryName'] . '<br>
							<strong>Registrado por:</strong> ' . $rows['userName'] . '<br>
                            '.$txtDisponibilidad.'
			              </p>
						<form action="" method="post">
						  <input type="hidden" name="idProduct" value="' . $rows['ProductoID'] . '">
						  <input type="hidden" name="precioProduct" value="' . $rows['PrecioUnitario'] . '">
						  <input type="hidden" name="nameProduct" value="' . $rows['NombreProducto'] . '">
						  <strong>Cantidad:</strong><br>
						  <input class="form-control col-md-12" type="text" name="cantidadProduct" value="0.0">
						<hr>
			            	<div class="has-text-centered">
			            	    <button type="submit" class="btn btn-warning btn-sm"" name="agregar">Agregar Producto</	button>
			            	</div>
						</form>
			        </div>
			    </div>
				</div>';
		$contador++;
	}
	$tabla .= '</div>
				</div>
					</div>';
	#Fin del Contenido de la card de productos

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
			<div class="col-md-12">
				<div class="alert alert-warning text-center" role="alert">
  					No hay registros en el sistema
				</div>
			</div>
			';
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
