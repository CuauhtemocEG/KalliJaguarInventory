<?php
$inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;
$tabla = "";

if (isset($busqueda) && $busqueda != "") {

	$consulta_datos = "SELECT * FROM Sucursales WHERE nombre LIKE '%$busqueda%' OR direccion LIKE '%$busqueda%' ORDER BY nombre ASC LIMIT $inicio,$registros";

	$consulta_total = "SELECT COUNT(SucursalID) FROM Sucursales WHERE nombre LIKE '%$busqueda%' OR direccion LIKE '%$busqueda%'";
} else {

	$consulta_datos = "SELECT * FROM Sucursales ORDER BY nombre ASC LIMIT $inicio,$registros";

	$consulta_total = "SELECT COUNT(SucursalID) FROM Sucursales";
}

$conexion = conexion();

$datos = $conexion->query($consulta_datos);
$datos = $datos->fetchAll();

$total = $conexion->query($consulta_total);
$total = (int) $total->fetchColumn();

$Npaginas = ceil($total / $registros);

$tabla .= '
	<div class="table-responsive">
        <table class="table table-bordered" width="100%" cellspacing="0">
            <thead>
                <tr class="has-text-centered">
                	<th>#</th>
                    <th>Nombre</th>
                    <th>Ubicación</th>
                    <th colspan="2">Opciones</th>
                </tr>
            </thead>
            <tbody>
	';

if ($total >= 1 && $pagina <= $Npaginas) {
	$contador = $inicio + 1;
	$pag_inicio = $inicio + 1;
	foreach ($datos as $rows) {
		$tabla .= '
				<tr class="has-text-centered" >
					<td>' . $contador . '</td>
                    <td>' . $rows['nombre'] . '</td>
                    <td>' . substr($rows['direccion'], 0, 25) . '</td>
                    <td>
                        <a href="index.php?page=updateSucursal&idSucursalUp=' . $rows['SucursalID'] . '" class="btn btn-warning btn-sm">Actualizar</a>
                    </td>
                    <td>
                        <a href="' . $url . $pagina . '&idSucursalDel=' . $rows['SucursalID'] . '" class="btn btn-danger btn-sm">Eliminar</a>
                    </td>
                </tr>
            ';
		$contador++;
	}
	$pag_final = $contador - 1;
} else {
	if ($total >= 1) {
		$tabla .= '
				<tr class="has-text-centered" >
					<td colspan="5">
						<a href="' . $url . '1" class="button is-link is-rounded is-small mt-4 mb-4">
							Haga clic acá para recargar el listado
						</a>
					</td>
				</tr>
			';
	} else {
		$tabla .= '
				<tr class="has-text-centered" >
					<td colspan="5">
						No hay registros en el sistema
					</td>
				</tr>
			';
	}
}


$tabla .= '</tbody></table></div>';

if ($total > 0 && $pagina <= $Npaginas) {
	$tabla .= '<hr><p class="text-center">Mostrando Sucursales <strong>' . $pag_inicio . '</strong> al <strong>' . $pag_final . '</strong> de un <strong>total de ' . $total . '</strong></p><br>';
}

$conexion = null;
echo $tabla;

if ($total >= 1 && $pagina <= $Npaginas) {
	echo paginador_tablas($pagina, $Npaginas, $url, 7);
}
