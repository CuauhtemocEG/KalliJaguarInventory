<?php
require_once './includes/session_start.php';
require_once './controllers/mainController.php';

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


    /*== Conectar a la base de datos usando prepared statements ==*/
    try {
        $pdo = conexion();
        
        if (!$pdo) {
            echo '
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <strong>¡Error de conexión!</strong><br>
                    No se pudo conectar a la base de datos.
                </div>';
            exit();
        }

        $stmt = $pdo->prepare("SELECT * FROM Usuarios WHERE Username = :usuario LIMIT 1");
        $stmt->bindParam(':usuario', $usuario, PDO::PARAM_STR);
        $stmt->execute();

        if($stmt->rowCount() == 1){
            $check_user = $stmt->fetch(PDO::FETCH_ASSOC);

            if($check_user['Username'] == $usuario && password_verify($clave, $check_user['Password'])){
                
                // Regenerar session ID por seguridad
                session_regenerate_id(true);
                
                $_SESSION['id'] = $check_user['UsuarioID'];
                $_SESSION['nombre'] = $check_user['Nombre'];
                $_SESSION['rol'] = $check_user['Rol'];
                $_SESSION['usuario'] = $check_user['Username'];

                // Log de debug
                error_log("LOGIN EXITOSO (tradicional) - Usuario: " . $usuario . " | Session ID: " . session_id());

                if(headers_sent()){
                    echo "<script> window.location.href='index.php?page=home'; </script>";
                }else{
                    header("Location: index.php?page=home");
                    exit();
                }

            }else{
                echo '
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <strong>¡Ocurrió un error!</strong><br>
                        Usuario o clave incorrectos.
                    </div>
                ';
            }
        }else{
            echo '
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <strong>¡Ocurrió un error!</strong><br>
                    Usuario o clave incorrectos.
                </div>
            ';
        }
        
    } catch (PDOException $e) {
        error_log("Error en login tradicional: " . $e->getMessage());
        echo '
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong>¡Error del servidor!</strong><br>
                Inténtalo más tarde.
            </div>
        ';
    }