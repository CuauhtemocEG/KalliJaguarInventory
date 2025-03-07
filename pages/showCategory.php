<div class="container-fluid" style="padding-top:15px; padding-bottom:15px">
    <div class="card">
        <div class="card-header font-weight-bold">Lista de Categorías</div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <?php
                    require_once "./controllers/mainController.php";

                    # Eliminar categoria #
                    if (isset($_GET['idCategoryDel'])) {
                        require_once "./controllers/deleteCategory.php";
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
                    $url = "index.php?page=showCategory&pages="; /* <== */
                    $registros = 15;
                    $busqueda = "";

                    # Paginador categoria #
                    require_once "./controllers/showCategoryController.php";
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>