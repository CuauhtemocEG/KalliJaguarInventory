<div class="container-fluid" style="padding-top:15px; padding-bottom:15px">
    <div class="card text-center">
        <div class="card-header font-weight-bold">Búsqueda de categorías</div>
        <div class="card-body">
            <?php
            require_once "./controllers/mainController.php";

            if (isset($_POST['searchModule'])) {
                require_once "./includes/baseSearch.php";
            }

            if (!isset($_SESSION['busqueda_categoria']) && empty($_SESSION['busqueda_categoria'])) {
            ?>
                <div class="row">
                    <div class="col-md-12">
                        <form action="" method="POST" autocomplete="off">
                            <input type="hidden" name="searchModule" value="categoria">
                            <div class="field is-grouped">
                                <p class="control is-expanded">
                                    <input class="form-control" type="text" name="txt_buscador" placeholder="¿Qué categoría estas buscando?" pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ ]{1,30}" maxlength="30">
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
                            <input type="hidden" name="searchModule" value="categoria">
                            <input type="hidden" name="eliminar_buscador" value="categoria">
                            <p>Estas buscando <strong>"<?php echo $_SESSION['busqueda_categoria']; ?>"</strong></p>
                            <br>
                            <button type="submit" class="btn btn-danger">Eliminar busqueda</button>
                        </form>
                    </div>
                </div>
                <hr>
            <?php
                # Eliminar categoria #
                if (isset($_GET['idCategoryDel'])) {
                    require_once "./controllers/deleteCategory.php";
                }

                if (!isset($_GET['page'])) {
                    $pagina = 1;
                } else {
                    $pagina = (int) $_GET['page'];
                    if ($pagina <= 1) {
                        $pagina = 1;
                    }
                }

                $pagina = limpiar_cadena($pagina);
                $url = "index.php?page=searchCategory&pages="; /* <== */
                $registros = 15;
                $busqueda = $_SESSION['busqueda_categoria']; /* <== */

                # Paginador categoria #
                require_once "./controllers/showCategoryController.php";
            }
            ?>
        </div>
    </div>
</div>