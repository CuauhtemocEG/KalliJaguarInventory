<div class="container-fluid" style="padding-top:15px; padding-bottom:15px">
    <div class="card text-center">
        <div class="card-header font-weight-bold">Búsqueda de Productos por Categoría</div>
        <div class="card-body">
            <?php
            require_once "./controllers/mainController.php";
            ?>
            <div class="row">
                <div class="col-md-4">
                    <h2 class="card-title h2 has-text-centered">Lista de Categorías</h2><hr class="bg-dark">
                    <?php
                    $categorias = conexion();
                    $categorias = $categorias->query("SELECT * FROM Categorias");
                    if ($categorias->rowCount() > 0) {
                        $categorias = $categorias->fetchAll();
                        foreach ($categorias as $row) {
                            echo '<div class="list-group">
                        <a class="list-group-item list-group-item-action" href="index.php?page=productsByCategory&idCategory=' . $row['CategoriaID'] . '" class="button is-link is-inverted is-fullwidth">' . $row['Nombre'] . '</a>
                        </div>';
                        }
                    } else {
                        echo '
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <strong>¡No hay categorías registradas!</strong><br>
                        </div>';
                    }
                    $categorias = null;
                    ?>
                </div>
                <div class="col-md-8">
                    <?php
                    $categoria_id = (isset($_GET['idCategory'])) ? $_GET['idCategory'] : 0;

                    /*== Verificando categoria ==*/
                    $check_categoria = conexion();
                    $check_categoria = $check_categoria->query("SELECT * FROM Categorias WHERE CategoriaID='$categoria_id'");

                    if ($check_categoria->rowCount() > 0) {

                        $check_categoria = $check_categoria->fetch();

                        echo '
                        <h2 class="card-title h2 has-text-centered">' . $check_categoria['Nombre'] . '</h2><hr class="bg-dark">';

                        #<p class="has-text-centered pb-6" >' . $check_categoria['categoria_ubicacion'] . '</p>

                        require_once "./controllers/mainController.php";

                        # Eliminar producto #
                        if (isset($_GET['product_id_del'])) {
                            require_once "./php/producto_eliminar.php";
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
                        $url = "index.php?page=productsByCategory&idCategory=$categoria_id&page="; /* <== */
                        $registros = 15;
                        $busqueda = "";

                        # Paginador producto #
                        require_once "./controllers/showProductController.php";
                    } else {
                        echo '<h2 class="card-title h3 has-text-centered" >Seleccione una categoría</h2>';
                    }
                    $check_categoria = null;
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>