<div class="container-fluid" style="padding-top:15px; padding-bottom:15px">
    <div class="card text-center">
        <div class="card-header font-weight-bold">Lista de productos</div>
        <div class="card-body">
            <?php
            require_once "./controllers/mainController.php";
            # Eliminar producto #
            if (isset($_GET['idProductDel'])) {
                require_once "./controllers/deleteProduct.php";
            }
            if (!isset($_GET['pages'])) {
                $pagina = 1;
            } else {
                $pagina = (int) $_GET['pages'];
                if ($pagina <= 1) {
                    $pagina = 1;
                }
            }
            $categoria_id = (isset($_GET['idCategory'])) ? $_GE['idCategory'] : 0;

            $pagina = limpiar_cadena($pagina);
            $url = "index.php?page=showProduct&pages="; /* <== */
            $registros = 15;
            $busqueda = "";
            # Paginador producto #
            require_once "./controllers/showProductController.php";
            ?>
        </div>
    </div>
</div>