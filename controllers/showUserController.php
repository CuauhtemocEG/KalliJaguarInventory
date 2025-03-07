<?php
	$inicio = ($pagina>0) ? (($pagina * $registros)-$registros) : 0;
	$tabla="";

	if(isset($busqueda) && $busqueda!=""){

		$consulta_datos="SELECT * FROM Usuarios WHERE ((UsuarioID!='".$_SESSION['id']."') AND (Nombre LIKE '%$busqueda%' OR Rol LIKE '%$busqueda%' OR Username LIKE '%$busqueda%' OR email LIKE '%$busqueda%')) ORDER BY Nombre ASC LIMIT $inicio,$registros";

		$consulta_total="SELECT COUNT(UsuarioID) FROM Usuarios WHERE ((UsuarioID!='".$_SESSION['id']."') AND (Nombre LIKE '%$busqueda%' OR Rol LIKE '%$busqueda%' OR Username LIKE '%$busqueda%' OR email LIKE '%$busqueda%'))";

	}else{

		$consulta_datos="SELECT * FROM Usuarios WHERE UsuarioID!='".$_SESSION['id']."' ORDER BY Nombre ASC LIMIT $inicio,$registros";

		$consulta_total="SELECT COUNT(UsuarioID) FROM Usuarios WHERE UsuarioID!='".$_SESSION['id']."'";
		
	}

	$conexion=conexion();

	$datos = $conexion->query($consulta_datos);
	$datos = $datos->fetchAll();

	$total = $conexion->query($consulta_total);
	$total = (int) $total->fetchColumn();

	$Npaginas =ceil($total/$registros);

	$tabla.='
	<div class="table-container">
        <table class="table is-bordered is-striped is-narrow is-hoverable is-fullwidth">
            <thead>
                <tr class="has-text-centered">
                	<th>#</th>
                    <th>Nombre</th>
                    <th>Rol</th>
                    <th>Usuario</th>
                    <th>Email</th>
                    <th colspan="2">Opciones</th>
                </tr>
            </thead>
            <tbody>
	';

	if($total>=1 && $pagina<=$Npaginas){
		$contador=$inicio+1;
		$pag_inicio=$inicio+1;
		foreach($datos as $rows){
			$tabla.='
				<tr class="has-text-centered" >
					<td>'.$contador.'</td>
                    <td>'.$rows['Nombre'].'</td>
                    <td>'.$rows['Rol'].'</td>
                    <td>'.$rows['Username'].'</td>
                    <td>'.$rows['email'].'</td>
                    <td>
                        <a href="index.php?page=updateUser&idUserUpdate='.$rows['UsuarioID'].'" class="btn btn-warning btn-sm">Actualizar</a>
                    </td>
                    <td>
                        <a href="'.$url.$pagina.'&idUserDel='.$rows['UsuarioID'].'" class="btn btn-danger btn-sm">Eliminar</a>
                    </td>
                </tr>
            ';
            $contador++;
		}
		$pag_final=$contador-1;
	}else{
		if($total>=1){
			$tabla.='
				<tr class="has-text-centered" >
					<td colspan="7">
						<a href="'.$url.'1" class="button is-link is-rounded is-small mt-4 mb-4">
							Haga clic acá para recargar el listado
						</a>
					</td>
				</tr>
			';
		}else{
			$tabla.='
				<tr class="has-text-centered" >
					<td colspan="7">
						<div class="alert alert-info alert-dismissible fade show" role="alert">
                			<strong>¡No hay registros en el sistema!</strong>
            			</div>
					</td>
				</tr>
			';
		}
	}


	$tabla.='</tbody></table></div>';

	if($total>0 && $pagina<=$Npaginas){
		$tabla.='<hr><p class="text-center">Mostrando Usuarios <strong>' . $pag_inicio . '</strong> al <strong>' . $pag_final . '</strong> de un <strong>total de ' . $total . '</strong></p><br>';
	}

	$conexion=null;
	echo $tabla;

	if($total>=1 && $pagina<=$Npaginas){
		echo paginador_tablas($pagina,$Npaginas,$url,7);
	}