<?php
	require_once "mainController.php";

    /*== Almacenando datos ==*/
    $nombre=limpiar_cadena($_POST['nameCategory']);

    /*== Verificando campos obligatorios ==*/
    if($nombre==""){
        echo '
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
                <strong>¡Ocurrio un error!</strong><br>
                No has llenado el campo requerido.
            </div>';
        exit();
    }


    /*== Verificando integridad de los datos ==*/
    //if(verificar_datos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ ]{4,50}",$nombre)){
    //    echo '
    //        <div class="alert alert-danger alert-dismissible fade show" role="alert">
    //            <button type="button" class="close" data-dismiss="alert" //aria-label="Close">
    //            <span aria-hidden="true">&times;</span>
    //            </button>
    //            <strong>¡Ocurrio un error!</strong><br>
    //            El Nombre de la Categoría no coincide con el formato solicitado.
    //        </div>';
    //    exit();
    //}


    /*== Verificando nombre ==*/
    $check_nombre=conexion();
    $check_nombre=$check_nombre->query("SELECT Nombre FROM Categorias WHERE Nombre='$nombre'");
    if($check_nombre->rowCount()>0){
        echo '
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
                <strong>¡Ocurrio un error!</strong><br>
                El Nombre de la Categoría ya se encuentra registrado, por favor elija otro.
            </div>';
        exit();
    }
    $check_nombre=null;


    /*== Guardando datos ==*/
    $guardar_categoria=conexion();
    $guardar_categoria=$guardar_categoria->prepare("INSERT INTO Categorias(Nombre) VALUES(:nombre)");

    $marcadores=[
        ":nombre"=>$nombre
    ];

    $guardar_categoria->execute($marcadores);

    if($guardar_categoria->rowCount()==1){
        echo '
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
                <strong>¡Categoría Registrada!</strong><br>
                La Categoría se registro con éxito en la Base de Datos.
            </div>
        ';
    }else{
        echo '
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
                <strong>¡Ocurrio un error!</strong><br>
                No se pudo registrar la Categoría, por favor intente nuevamente.
            </div>
        ';
    }
    $guardar_categoria=null;