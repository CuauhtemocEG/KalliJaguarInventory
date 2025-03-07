<?php
	require_once "mainController.php";

    /*== Almacenando datos ==*/
    $nombre=limpiar_cadena($_POST['sucursalName']);
    $direccion=limpiar_cadena($_POST['sucursalAddress']);


    /*== Verificando campos obligatorios ==*/
    if($nombre==""){
        echo '
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong>¡Ocurrio un error!</strong><br>
                No has llenado todos los campos que son obligatorios
            </div>';
        exit();
    }


    /*== Verificando integridad de los datos ==*/
    if(verificar_datos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ ]{4,50}",$nombre)){
        echo '
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong>¡Ocurrio un error!</strong><br>
                El campo "Sucursal" no cumple con el formato solicitado.
            </div>';
        exit();
    }

    if($direccion!=""){
    	if(verificar_datos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ ]{5,150}",$direccion)){
	        echo '
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                    <strong>¡Ocurrio un error!</strong><br>
                    El campo "Dirección" no cumple con el formato solicitado.
                </div>';
	        exit();
	    }
    }


    /*== Verificando Sucursal ==*/
    $check_nombre=conexion();
    $check_nombre=$check_nombre->query("SELECT nombre FROM Sucursales WHERE nombre='$nombre'");
    if($check_nombre->rowCount()>0){
        echo '
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
                <strong>¡Ocurrio un error!</strong><br>
                La Sucursal ingresada ya se encuentra registrada, por favor elija otro.
            </div>';
        exit();
    }
    $check_nombre=null;


    /*== Guardando datos ==*/
    $guardar_categoria=conexion();
    $guardar_categoria=$guardar_categoria->prepare("INSERT INTO Sucursales(nombre,direccion) VALUES(:nombre,:direccion)");

    $marcadores=[
        ":nombre"=>$nombre,
        ":direccion"=>$direccion
    ];

    $guardar_categoria->execute($marcadores);

    if($guardar_categoria->rowCount()==1){
        echo '
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
                <strong>¡Sucursal Registrada!</strong><br>
                La sucursal se registró con éxito en la Base de Datos.
            </div>
        ';
    }else{
        echo '
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
                <strong>¡Ocurrio un error!</strong><br>
                No se pudo registrar la sucursal, por favor intente nuevamente.
            </div>
        ';
    }
    $guardar_categoria=null;