<?php /** Layout principal. Variable: $page (nombre de la vista en views/pages). */ ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    
    <meta name="description" content="Sistema completo de gestión de inventarios para restaurantes y negocios">
    <meta name="author" content="<?= e(config('app.developer') ?: config('app.company')) ?>">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <meta name="theme-color" content="#F5C842">
    <meta name="msapplication-TileColor" content="#1e293b">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="<?= e(config('app.short_name')) ?>">
    <meta name="format-detection" content="telephone=no">
    
    <link rel="icon" href="<?= asset('img/icons/icon-96x96.png') ?>" type="image/png">
    <link rel="apple-touch-icon" href="<?= asset('img/icons/icon-152x152.png') ?>">
    <link rel="apple-touch-icon" sizes="152x152" href="<?= asset('img/icons/icon-152x152.png') ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= asset('img/icons/icon-192x192.png') ?>">
    <link rel="apple-touch-icon" sizes="167x167" href="<?= asset('img/icons/icon-192x192.png') ?>">
    
    <link rel="manifest" href="<?= app_url('manifest.json') ?>">
    
    <title><?= e(config('app.name')) ?></title>
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link href="<?= asset('css/sb-admin-2.min.css') ?>" rel="stylesheet">
    <link href="<?= asset('css/style.css') ?>" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script src="<?= asset('js/csrf.js') ?>"></script>
</head>

<body id="page-top">
    <?php
    if ($page === 'login' || $page === '404') {
        include base_path("views/pages/{$page}.php");
    } elseif ($page === 'logout') {
        include BASE_PATH . '/views/pages/logout.php';
        exit();
    } else {
        $navbars = [
            'Administrador' => 'navbar-tailwind',
            'Supervisor' => 'navbar-supervisor-tailwind',
            'Logistica' => 'navbar-logistica-tailwind',
        ];
        $navbar = $navbars[$_SESSION['rol'] ?? ''] ?? null;

        if ($navbar !== null) {
            include base_path("views/partials/{$navbar}.php");
        }

        include base_path("views/pages/{$page}.php");

        include base_path('views/partials/script.php');

        if ($navbar !== null) {
            include base_path('views/partials/navbar-tailwind-bottom.php');
        }
    }
    ?>
    <script src="<?= asset('vendor/jquery/jquery.min.js') ?>"></script>
    <script src="<?= asset('vendor/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
    <script src="<?= asset('vendor/jquery-easing/jquery.easing.min.js') ?>"></script>
    <script src="<?= asset('js/sb-admin-2.min.js') ?>"></script>
    <script src="<?= asset('js/functions.js') ?>"></script>
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
                        <input type="hidden" name="id" value="<?= e($_SESSION['id'] ?? '') ?>">
                            <b><label>Sucursal de Destino:</label></b>
                            <select class="form-control" id="inputSucursal" name="idSucursal">
                                <option value="" selected>Seleccione una Sucursal</option>
                                <?php
                                if (!empty($_SESSION['id'])) {
                                    $sucursal = conexion();
                                    $sucursal = $sucursal->query("SELECT * FROM Sucursales");
                                    foreach ($sucursal->fetchAll() as $row) {
                                        echo '<option value="' . e($row['SucursalID']) . '" >' . e($row['nombre']) . '</option>';
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