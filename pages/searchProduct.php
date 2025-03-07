<div class="container-fluid" style="padding-top:15px; padding-bottom:15px">
    <div class="card text-center">
        <div class="card-header font-weight-bold">Búsqueda de Productos</div>
        <div class="card-body">
            <?php
            require_once "./controllers/mainController.php";

            if (isset($_POST['searchModule'])) {
                require_once "./includes/baseSearch.php";
            }

            if (!isset($_SESSION['busqueda_producto']) && empty($_SESSION['busqueda_producto'])) {
            ?>
                <div class="row">
                    <div class="col-md-12">
                        <form action="" method="POST" autocomplete="off">
                            <input type="hidden" name="searchModule" value="producto">
                            <div class="field is-grouped">
                                <p class="control is-expanded">
                                    <input class="form-control" type="text" name="txt_buscador" placeholder="¿Qué Producto estas buscando?" pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ ]{1,30}" maxlength="30">
                                </p>
                                <p class="control">
                                    <button class="btn btn-warning" type="submit">Buscar</button>
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
            <?php } else { ?>
                <div class="row">
                    <div class="col-md-12">
                        <form class="has-text-centered" action="" method="POST" autocomplete="off">
                            <input type="hidden" name="searchModule" value="producto">
                            <input type="hidden" name="eliminar_buscador" value="producto">
                            <p>Estas buscando <strong>"<?php echo $_SESSION['busqueda_producto']; ?>"</strong></p>
                            <br>
                            <button type="submit" class="btn btn-danger">Eliminar busqueda</button>
                        </form>
                    </div>
                </div>
                <hr>
            <?php
                # Eliminar producto #
                if (isset($_GET['idProductDel'])) {
                    require_once "./controllers/deleteProduct.php";
                }

                if (!isset($_GET['page'])) {
                    $pagina = 1;
                } else {
                    $pagina = (int) $_GET['page'];
                    if ($pagina <= 1) {
                        $pagina = 1;
                    }
                }

                $categoria_id = (isset($_GET['idCategory'])) ? $_GET['idCategory'] : 0;

                $pagina = limpiar_cadena($pagina);
                $url = "index.php?page=searchProduct&pages="; /* <== */
                $registros = 15;
                $busqueda = $_SESSION['busqueda_producto']; /* <== */

                # Paginador producto #
                require_once "./controllers/showProductController.php";
            }
            ?>
        </div>
    </div>
</div>