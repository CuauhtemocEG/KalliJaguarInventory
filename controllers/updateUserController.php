<?php
require_once "../includes/session_start.php";

require_once "mainController.php";

/*== Almacenando id ==*/
$id = limpiar_cadena($_POST['idUser']);

/*== Verificando usuario ==*/
$check_usuario = conexion();
$check_usuario = $check_usuario->query("SELECT * FROM Usuarios WHERE UsuarioID='$id'");

if ($check_usuario->rowCount() <= 0) {
    echo '
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong>¡Ocurrio un error!</strong><br>
                El Usuario no existe en el sistema.
            </div>';
    exit();
} else {
    $datos = $check_usuario->fetch();
}
$check_usuario = null;


/*== Almacenando datos del administrador ==*/
$admin_usuario = limpiar_cadena($_POST['adminUser']);
$admin_clave = limpiar_cadena($_POST['adminPassword']);


/*== Verificando campos obligatorios del administrador ==*/
if ($admin_usuario == "" || $admin_clave == "") {
    echo '
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong>¡Ocurrio un error!</strong><br>
                No es posible actualizar los datos si no ha ingresado su Nombre de Usuario y Password para autenticar su cuenta.
            </div>';
    exit();
}

/*== Verificando integridad de los datos (admin) ==*/
if (verificar_datos("[a-zA-Z0-9]{4,20}", $admin_usuario)) {
    echo '
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong>¡Ocurrio un error!</strong><br>
                Su Nombre de Usuario no coincide con el formato solicitado.
            </div>';
    exit();
}

if (verificar_datos("[a-zA-Z0-9$@.-]{7,100}", $admin_clave)) {
    echo '
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong>¡Ocurrio un error!</strong><br>
                Su Password no coincide con el formato solicitado.
            </div>';
    exit();
}


/*== Verificando el administrador en DB ==*/
$check_admin = conexion();
$check_admin = $check_admin->query("SELECT Username,Password FROM Usuarios WHERE Username='$admin_usuario' AND UsuarioID='" . $_SESSION['id'] . "'");
if ($check_admin->rowCount() == 1) {

    $check_admin = $check_admin->fetch();

    if ($check_admin['Username'] != $admin_usuario || !password_verify($admin_clave, $check_admin['Password'])) {
        echo '
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <strong>¡Ocurrio un error!</strong><br>
                    Nombre de Usuario o Password de administrador incorrectos.
                </div>';
        exit();
    }
} else {
    echo '
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong>¡Ocurrio un error!</strong><br>
                Nombre de Usuario o Password de administrador incorrectos.
            </div>';
    exit();
}
$check_admin = null;


/*== Almacenando datos del usuario ==*/
$nombre = limpiar_cadena($_POST['userName']);
$rol = limpiar_cadena($_POST['userRol']);

$usuario = limpiar_cadena($_POST['user']);
$email = limpiar_cadena($_POST['userEmail']);

$clave_1 = limpiar_cadena($_POST['userPassword']);
$clave_2 = limpiar_cadena($_POST['userPassword2']);


/*== Verificando campos obligatorios del usuario ==*/
if ($nombre == "" || $rol == "" || $usuario == "") {
    echo '
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong>¡Ocurrio un error!</strong><br>
                No has llenado todos los campos que son obligatorios.
            </div>';
    exit();
}


/*== Verificando integridad de los datos (usuario) ==*/
if (verificar_datos("[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,40}", $nombre)) {
    echo '
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong>¡Ocurrio un error!</strong><br>
                El Nombre no coincide con el formato solicitado.
            </div>';
    exit();
}

if (verificar_datos("[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,40}", $rol)) {
    echo '
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong>¡Ocurrio un error!</strong><br>
                El Rol no coincide con el formato solicitado, es distinto a los 3 tipos requeridos.
            </div>';
    exit();
}

if (verificar_datos("[a-zA-Z0-9]{4,20}", $usuario)) {
    echo '
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong>¡Ocurrio un error!</strong><br>
                El Username no coincide con el formato solicitado.
            </div>';
    exit();
}


/*== Verificando email ==*/
if ($email != "" && $email != $datos['email']) {
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $check_email = conexion();
        $check_email = $check_email->query("SELECT email FROM Usuarios WHERE email='$email'");
        if ($check_email->rowCount() > 0) {
            echo '
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <strong>¡Ocurrio un error!</strong><br>
                        El correo electrónico ingresado ya se encuentra registrado, por favor elija otro.
                    </div>';
            exit();
        }
        $check_email = null;
    } else {
        echo '
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <strong>¡Ocurrio un error!</strong><br>
                    Ha ingresado un correo electrónico no valido.
                </div>';
        exit();
    }
}


/*== Verificando usuario ==*/
if ($usuario != $datos['Username']) {
    $check_usuario = conexion();
    $check_usuario = $check_usuario->query("SELECT Username FROM Usuarios WHERE Username='$usuario'");
    if ($check_usuario->rowCount() > 0) {
        echo '
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <strong>¡Ocurrio un error!</strong><br>
                    El Nombre de Usuario ingresado ya se encuentra registrado, por favor elija otro.
                </div>';
        exit();
    }
    $check_usuario = null;
}


/*== Verificando claves ==*/
if ($clave_1 != "" || $clave_2 != "") {
    if (verificar_datos("[a-zA-Z0-9$@.-]{7,100}", $clave_1) || verificar_datos("[a-zA-Z0-9$@.-]{7,100}", $clave_2)) {
        echo '
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <strong>¡Ocurrio un error!</strong><br>
                    El Password ingresado no coincide con el formato requerido.
                </div>';
        exit();
    } else {
        if ($clave_1 != $clave_2) {
            echo '
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <strong>¡Ocurrio un error!</strong><br>
                    Las Passwords ingresadas no coinciden con el formato requerido.
                </div>';
            exit();
        } else {
            $clave = password_hash($clave_1, PASSWORD_BCRYPT, ["cost" => 10]);
        }
    }
} else {
    $clave = $datos['Password'];
}


/*== Actualizar datos ==*/
$actualizar_usuario = conexion();
$actualizar_usuario = $actualizar_usuario->prepare("UPDATE Usuarios SET Nombre=:nombre,Rol=:rol,Username=:usuario,Password=:clave,email=:email WHERE UsuarioID=:id");

$marcadores = [
    ":nombre" => $nombre,
    ":rol" => $rol,
    ":usuario" => $usuario,
    ":clave" => $clave,
    ":email" => $email,
    ":id" => $id
];

if ($actualizar_usuario->execute($marcadores)) {
    echo '
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <strong>¡Registro Actualizado!</strong><br>
            El usuario se actualizó con éxito en la Base de Datos.
        </div>';
} else {
    echo '
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <strong>¡Ocurrio un error!</strong><br>
            No se pudo actualizar el registro, por favor intente nuevamente.
        </div>
        ';
}
$actualizar_usuario = null;
