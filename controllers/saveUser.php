<?php

require_once "mainController.php";

/*== Almacenando datos ==*/
$nombre = limpiar_cadena($_POST['userName']);
$rol = limpiar_cadena($_POST['userRol']);

$usuario = limpiar_cadena($_POST['user']);
$email = limpiar_cadena($_POST['userEmail']);

$clave_1 = limpiar_cadena($_POST['userPassword']);
$clave_2 = limpiar_cadena($_POST['userPassword2']);


/*== Verificando campos obligatorios ==*/
if ($nombre == "" || $rol == "" || $usuario == "" || $clave_1 == "" || $clave_2 == "") {
    echo '
        <div class="alert alert-danger">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <strong>¡Ocurrio un error!</strong><br>
            No has llenado todos los campos que son obligatorios.
        </div>
        ';
    exit();
}


/*== Verificando integridad de los datos ==*/
if (verificar_datos("[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,40}", $nombre)) {
    echo '
        <div class="alert alert-danger">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <strong>¡Ocurrio un error!</strong><br>
            El nombre del Usuario no coincide con el formato solicitado.
        </div>
        ';
    exit();
}

if (verificar_datos("[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,40}", $rol)) {
    echo '
        <div class="alert alert-danger">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <strong>¡Ocurrio un error!</strong><br>
            El Rol del Usuario no coincide con el formato solicitado.
        </div>
        ';
    exit();
}

if (verificar_datos("[a-zA-Z0-9]{4,20}", $usuario)) {
    echo '
        <div class="alert alert-danger">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <strong>¡Ocurrio un error!</strong><br>
            El nombre de Usuario no coincide con el formato solicitado.
        </div>
        ';
    exit();
}

if (verificar_datos("[a-zA-Z0-9$@.-]{7,100}", $clave_1) || verificar_datos("[a-zA-Z0-9$@.-]{7,100}", $clave_2)) {
    echo '
        <div class="alert alert-danger">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <strong>¡Ocurrio un error inesperado!</strong><br>
            Las contraseñas que ha ingresado no cumplen con el formato indicado (mayor a 7 caracteres).
        </div>
        ';
    exit();
}


/*== Verificando email ==*/
if ($email != "") {
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $check_email = conexion();
        $check_email = $check_email->query("SELECT email FROM Usuarios WHERE email='$email'");
        if ($check_email->rowCount() > 0) {
            echo '
                <div class="alert alert-danger">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <strong>¡Ocurrio un error!</strong><br>
                    El correo electrónico ingresado ya se encuentra registrado en el sistema, por favor elija otro.
                </div>';
            exit();
        }
        $check_email = null;
    } else {
        echo '
            <div class="alert alert-warning">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <strong>¡Ocurrio un error!</strong><br>
                    El correo electrónico que has ingresado no es válido.
            </div>';
        exit();
    }
}


/*== Verificando usuario ==*/
$checkUsername = conexion();
$checkUsername = $checkUsername->query("SELECT Username FROM Usuarios WHERE Username='$usuario'");
if ($checkUsername->rowCount() > 0) {
    echo '
        <div class="alert alert-danger">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <strong>¡Ocurrio un error!</strong><br>
            El Username ingresado ya se encuentra registrado en sistema.
        </div>';
    exit();
}
$checkUsername = null;


/*== Verificando claves ==*/
if ($clave_1 != $clave_2) {
    echo '
        <div class="alert alert-danger">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <strong>¡Ocurrio un error!</strong><br>
            Las contraseñas que ha ingresado no coinciden.
        </div>
        ';
    exit();
} else {
    $clave = password_hash($clave_1, PASSWORD_BCRYPT, ["cost" => 10]);
}


/*== Guardando datos ==*/
$guardar_usuario = conexion();
$guardar_usuario = $guardar_usuario->prepare("INSERT INTO Usuarios(Nombre,Rol,Username,Password,email) VALUES(:nombre,:rol,:usuario,:clave,:email)");

$marcadores = [
    ":nombre" => $nombre,
    ":rol" => $rol,
    ":usuario" => $usuario,
    ":clave" => $clave,
    ":email" => $email
];

$guardar_usuario->execute($marcadores);

if ($guardar_usuario->rowCount() == 1) {
    echo '
            <div class="alert alert-success" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
                <strong>¡Operación completada!</strong><br>
                El usuario se ha registrado con éxito.
            </div>
        ';
} else {
    echo '
            <div class="alert alert-info" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
                <strong>¡Ocurrio un error!</strong><br>
                No se pudo registrar el usuario, por favor intente nuevamente
            </div>
        ';
}
$guardar_usuario = null;
