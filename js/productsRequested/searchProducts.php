<?php
session_start();
function conexion(){
    $pdo = new PDO('mysql:host=localhost:3306
;dbname=kallijag_inventory_stage', 'kallijag_stage', 'uNtiL.horSe@5');
    return $pdo;
}

$query = isset($_GET['query']) ? $_GET['query'] : '';

$campos = "Productos.ProductoID,Productos.UPC,Productos.Nombre as nombreProducto,Productos.PrecioUnitario,Productos.Cantidad,Productos.Tipo,Productos.image,Productos.CategoriaID as productCategory,Productos.UsuarioID,Categorias.CategoriaID,Categorias.Nombre as categoryName,Usuarios.UsuarioID,Usuarios.Nombre as userName";

$consulta_datos = "SELECT $campos FROM Productos INNER JOIN Categorias ON Productos.CategoriaID=Categorias.CategoriaID INNER JOIN Usuarios ON Productos.UsuarioID=Usuarios.UsuarioID WHERE Productos.UPC LIKE '%$query%' OR Productos.Nombre LIKE '%$query%' ORDER BY Productos.Nombre";

$conexion = conexion();
$datos = $conexion->query($consulta_datos);
$datos = $datos->fetchAll();

$tabla = '';
$tabla .= '<div class="container-fluid mb-3">
				<div class="row">';
foreach ($datos as $row) {
    $result = "";

    $txtDisponibilidad = "";

    if ($row['Cantidad'] >= 1) {
        $txtDisponibilidad = '<span class="badge badge-success">Disponible</span>';
    } else {
        $txtDisponibilidad = '<span class="badge badge-danger">No disponible</span>';
    }

    if ($row['Tipo'] == "Pesable") {
        $result = "<i class='fas fa-balance-scale'></i> Kg";
        $unidades = number_format($row['Cantidad'], 2, '.', '');
        $tipoClass = 'text-success';
        $step = '0.1';
    } else {
        $result = "Unidades";
        $unidades = (int) $row['Cantidad'];
        $result = "<i class='fas fa-cube'></i> Unidades";
        $tipoClass = 'text-warning';
        $step = '1';
    }

    if (isset($_SESSION['INV'][$row['ProductoID']])) {
        $value = $_SESSION['INV'][$row['ProductoID']]['cantidad'];
    } else {
        $value = ($row['Tipo'] == "Pesable") ? '0.0' : '0';
    }

    $cantidadRequested = '';
    if ($row['Cantidad'] > 0) {
        $cantidadRequested = '
        <form class="add-product-form">
            <input type="hidden" name="idProduct" value="' . $row['ProductoID'] . '">
            <input type="hidden" name="precioProduct" value="' . $row['PrecioUnitario'] . '">
            <input type="hidden" name="nameProduct" value="' . $row['nombreProducto'] . '">
            <input type="hidden" name="typeProduct" value="' . $row['Tipo'] . '">
            <strong>Cantidad a solicitar:</strong><br>
            <div class="input-group">
                <button type="button" class="btn btn-outline-secondary" onclick="decreaseQuantity(' . $row['ProductoID'] . ')">-</button>
                <input class="form-control col-md-12" type="number" name="cantidadProduct" value="' . $value . '" step="' . $step . '" min="0" id="cantidad_' . $row['ProductoID'] . '">
                <button type="button" class="btn btn-outline-secondary" onclick="increaseQuantity(' . $row['ProductoID'] . ')">+</button>
            </div>
            <hr>
            <div class="has-text-centered">
                <button type="submit" class="btn btn-warning btn-sm btn-add-to-cart" name="agregar" disabled>Agregar Producto</button>
            </div>
        </form>';
    }

    $tabla .= '
				<div class="col-md-3">
				<div class="card mb-2">';
    if (is_file("./img/producto/" . $row['image'])) {
        $tabla .= '<img class="card-img-top mx-auto d-block img-responsive w-50" src="./img/producto/' . $row['image'] . '">';
    } else {
        $tabla .= '<img class="card-img-top mx-auto d-block img-responsive w-50" src="./img/producto.png">';
    }
    $tabla .= '</img>
			        <div class="card-body">
                        <h5 class="card-title"><strong>' . $row['nombreProducto'] . '</strong></h5>
						<hr>
			              <p class="card-text">
			                <strong>UPC:</strong> ' . $row['UPC'] . '<br>
                            <strong>Precio:</strong> $' . $row['PrecioUnitario'] . '<br>
							<strong>Disponible:</strong> ' . $unidades . ' ' . $result . '<br>
                            ' . $txtDisponibilidad . '
			              </p>
						  ' . $cantidadRequested . '
			        </div>
			    </div>
				</div>';
}
$tabla .= '</div>
				</div>';

echo $tabla;
?>
<script>
	function increaseQuantity(productId) {
		const input = document.getElementById('cantidad_' + productId);
		const step = parseFloat(input.step);
		let value = parseFloat(input.value);

		value += step;
		input.value = formatValue(value, step);
		toggleButton(input);
	}

	function decreaseQuantity(productId) {
		const input = document.getElementById('cantidad_' + productId);
		const step = parseFloat(input.step);
		let value = parseFloat(input.value);

		value -= step;
		if (value < 0) value = 0;

		input.value = formatValue(value, step);
		toggleButton(input);
	}

	function formatValue(value, step) {
		return (step < 1) ? value.toFixed(2) : parseInt(value);
	}

	document.addEventListener('DOMContentLoaded', function() {

		document.querySelectorAll('input[name="cantidadProduct"]').forEach(input => {
			toggleButton(input);
			input.addEventListener('input', () => toggleButton(input));
		});
	});

	function toggleButton(input) {
		const form = input.closest('form');
		const btn = form.querySelector('.btn-add-to-cart');
		const value = parseFloat(input.value);

		if (!isNaN(value) && value > 0) {
			btn.removeAttribute('disabled');
		} else {
			btn.setAttribute('disabled', true);
		}
	}
</script>