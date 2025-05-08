<?php
session_start();
function conexion()
{
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

    if ($row['Cantidad'] >= 1 && $row['Tipo'] == 'Unidad' ) {
        $txtDisponibilidad = '<span class="badge badge-success">Disponible</span>';
    } elseif ($row['Cantidad'] >= 0.1 && $row['Tipo'] == 'Pesable') {
        $txtDisponibilidad = '<span class="badge badge-success">Disponible</span>';
    } else {
        $txtDisponibilidad = '<span class="badge badge-danger">No disponible</span>';
    }

    $tipoProducto = $row['Tipo'];
    $idProducto = $row['ProductoID'];
    $step = ($tipoProducto === 'Pesable') ? 0.01 : 1;

    $value = isset($_SESSION['INV'][$idProducto]) ? $_SESSION['INV'][$idProducto]['cantidad'] : 0;

    if ($tipoProducto === 'Pesable') {
        $cantidadVisible = ($value < 1 && $value > 0) ? number_format($value * 1000, 0) . ' gr' : number_format($value, 2) . ' Kg';
    } else {
        $cantidadVisible = (int)$value . ' Un';
    }

    if ($row['Tipo'] == "Pesable") {
        $result = "<i class='fas fa-balance-scale'></i> Kg";
        $unidades = number_format($row['Cantidad'], 2, '.', '');
        $tipoClass = 'text-success';
        $step = '0.25';
    } else {
        $result = "Unidades";
        $unidades = (int) $row['Cantidad'];
        $result = "<i class='fas fa-cube'></i> Unidades";
        $tipoClass = 'text-warning';
        $step = '1';
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
            
                <input class="form-control col-md-12" type="text" id="cantidadVisible_' . $row['ProductoID'] . '" value="' . $cantidadVisible . '" readonly>
                <input type="hidden" name="cantidadProduct" id="cantidad_' . $row['ProductoID'] . '" value="' . $value . '" step="' . $step . '">

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
        let value = parseFloat(input.value);
        const step = parseFloat(input.getAttribute('step'));

        value += step;
        input.value = value.toFixed(2);

        updateVisible(productId);
        toggleButton(input);
    }

    function decreaseQuantity(productId) {
        const input = document.getElementById('cantidad_' + productId);
        let value = parseFloat(input.value);
        const step = parseFloat(input.getAttribute('step'));

        value -= step;
        if (value < 0) value = 0;

        input.value = value.toFixed(2);

        updateVisible(productId);
        toggleButton(input);
    }

    function updateVisible(productId) {
        const input = document.getElementById('cantidad_' + productId);
        const visible = document.getElementById('cantidadVisible_' + productId);
        const value = parseFloat(input.value);

        const step = parseFloat(input.getAttribute('step'));

        if (step === 0.25) {
            
            if (value < 1 && value > 0) {
                visible.value = Math.round(value * 1000) + ' gr';
            } else {
                visible.value = value.toFixed(2) + ' Kg';
            }
        } else {
            
            visible.value = parseInt(value) + ' Un';
        }
    }

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

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('input[name="cantidadProduct"]').forEach(input => {
            toggleButton(input); 
            input.addEventListener('input', () => toggleButton(input));
        });
    });
</script>