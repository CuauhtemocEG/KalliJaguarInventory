<?php require "./includes/session_start.php";?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="img/Login.jpg" type="image/jpg">
    <title>Kalli Jaguar Inventory</title>
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <script src="https://unpkg.com/html5-qrcode"></script>
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
</head>

<body id="page-top">
    <?php
    if (!isset($_GET['page']) || $_GET['page'] == "") {
        $_GET['page'] = "login";
    }
    if (is_file("./pages/" . $_GET['page'] . ".php") && $_GET['page'] != "login" && $_GET['page'] != "404") {

        /*== Cerrar sesion ==*/
        if ((!isset($_SESSION['id']) || $_SESSION['id'] == "") || (!isset($_SESSION['usuario']) || $_SESSION['usuario'] == "")) {
            include "./pages/logout.php";
            exit();
        }

        if ($_SESSION['rol'] == "Administrador") {
            include "./includes/navbarNewTop.php";
        } elseif ($_SESSION['rol'] == "Supervisor") {
            include "./includes/navbarSup.php";
        } elseif ($_SESSION['rol'] == "Logistica") {
            include "./includes/navbarLog.php";
        } else {
            echo "";
        }

        include "./pages/" . $_GET['page'] . ".php";

        include "./includes/script.php";

        if ($_SESSION['rol'] == "Administrador") {
            include "./includes/navbarNewBottom.php";
        } elseif ($_SESSION['rol'] == "Supervisor") {
            include "./includes/navbarNewBottom.php";
        } elseif ($_SESSION['rol'] == "Logistica") {
            include "./includes/navbarNewBottom.php";
        } else {
            echo "";
        }
    } else {
        if ($_GET['page'] == "login") {
            include "./pages/login.php";
        } else {
            include "./pages/404.php";
        }
    }
    ?>
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
    <script src="js/functions.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.es.min.js"></script>
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">Confirmar Solicitud a Almacén</h5>
                    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    ¿Está seguro de que desea enviar la lista de productos solicitados?
                    <form id="confirmForm">
                        <div class="form-group col-md-12">
                        <input type="hidden" name="id" value="<?php echo isset($_SESSION['id']) ? $_SESSION['id'] : ''; ?>">
                            <b><label>Sucursal de Destino:</label></b>
                            <select class="form-control" id="inputSucursal" name="idSucursal">
                                <option value="" selected>Seleccione una Sucursal</option>
                                <?php
                                $sucursal = conexion();
                                $sucursal = $sucursal->query("SELECT * FROM Sucursales");
                                if ($sucursal->rowCount() > 0) {
                                    $sucursal = $sucursal->fetchAll();
                                    foreach ($sucursal as $row) {
                                        echo '<option value="' . $row['SucursalID'] . '" >' . $row['nombre'] . '</option>';
                                    }
                                }
                                $sucursal = null;
                                ?>
                            </select>
                            <br>
                            <b><label>Fecha de entrega:</label></b>
                            <div class="input-group date" id="datepicker">
                                <input type="text" class="form-control" id="date" name="fecha" />
                                <span class="input-group-append">
                                    <span class="input-group-text bg-light d-block">
                                        <i class="fa fa-calendar"></i>
                                    </span>
                                </span>
                            </div>
                        </div>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Confirmar Pedido</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
<script>
    $(function() {
        $('#datepicker').datepicker({
            format: 'dd/mm/yyyy',
            language: 'es',
            autoclose: true,
            todayHighlight: true
        });
    });
    
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('button[id^="downloadBtn_"]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const productId = btn.id.split('_')[1];
                const image = document.getElementById('barcodeImage_' + productId);

                if (!image || !image.src.startsWith('data:image/png;base64,')) {
                    alert('No se pudo encontrar la imagen o no está en formato base64.');
                    return;
                }

                const a = document.createElement('a');
                a.href = image.src;
                a.download = 'codigo_barras_' + productId + '.png';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            });
        });
    });
</script>

</html>