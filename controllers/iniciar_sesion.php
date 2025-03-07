<?php
	/*== Almacenando datos ==*/
    $usuario=limpiar_cadena($_POST['login_usuario']);
    $clave=limpiar_cadena($_POST['login_clave']);


    /*== Verificando campos obligatorios ==*/
    if($usuario=="" || $clave==""){
        echo '
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong>¡Ocurrio un error!</strong><br>
                No has llenado todos los campos obligatorios para iniciar sesión.
            </div>';
        exit();
    }


    /*== Verificando integridad de los datos ==*/
    if(verificar_datos("[a-zA-Z0-9]{4,20}",$usuario)){
        echo '
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong>¡Ocurrio un error!</strong><br>
                El campo "Usuario" no coincide con el formato solicitado, verifica nuevamente.
            </div>
        ';
        exit();
    }

    if(verificar_datos("[a-zA-Z0-9$@.-]{7,100}",$clave)){
        echo '
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong>¡Ocurrio un error!</strong><br>
                El campo "Clave" no coincide con el formato solicitado, verifica nuevamente.
            </div>
        ';
        exit();
    }


    $check_user=conexion();
    $check_user=$check_user->query("SELECT * FROM Usuarios WHERE Username='$usuario'");
    if($check_user->rowCount()==1){

    	$check_user=$check_user->fetch();

    	if($check_user['Username']==$usuario && password_verify($clave, $check_user['Password'])){

    		$_SESSION['id']=$check_user['UsuarioID'];
    		$_SESSION['nombre']=$check_user['Nombre'];
    		$_SESSION['rol']=$check_user['Rol'];
    		$_SESSION['usuario']=$check_user['Username'];

    		if(headers_sent()){
				echo "<script> window.location.href='index.php?page=home'; </script>";
			}else{
				header("Location: index.php?page=home");
			}

    	}else{
    		echo '
	            <div class="alert alert-danger">
	                <strong>¡Ocurrio un error!</strong><br>
	                Usuario o clave incorrectos.
	            </div>
	        ';
    	}
    }else{
    	echo '
            <div class="alert alert-danger">
                <strong>¡Ocurrio un error!</strong><br>
                Usuario o clave incorrectos.
            </div>
        ';
    }
    $check_user=null;