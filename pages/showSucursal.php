<div class="container-fluid" style="padding-top:15px; padding-bottom:15px">
    <div class="card text-center">
        <div class="card-header font-weight-bold">Lista de Sucursales</div>
        <div class="card-body">
            <?php
            require_once "./controllers/mainController.php";

            # Eliminar usuario #
            if (isset($_GET['idSucursalDel'])) {
                require_once "./controllers/deleteSucursal.php";
            }

            if (!isset($_GET['pages'])) {
                $pagina = 1;
            } else {
                $pagina = (int) $_GET['pages'];
                if ($pagina <= 1) {
                    $pagina = 1;
                }
            }

            $pagina = limpiar_cadena($pagina);
            $url = "index.php?page=showSucursal&pages=";
            $registros = 15;
            $busqueda = "";

            # Paginador usuario #
            require_once "./controllers/showSucursalController.php";
            ?>
        </div>
    </div>
</div>