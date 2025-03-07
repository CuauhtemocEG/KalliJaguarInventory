<div class="container-fluid" style="padding-top:15px; padding-bottom:15px">
    <div class="card text-center">
        <div class="card-header font-weight-bold">Búsqueda de Usuarios</div>
        <div class="card-body">
            <?php
            require_once "./controllers/mainController.php";

            if (isset($_POST['searchModule'])) {
                require_once "./includes/baseSearch.php";
            }

            if (!isset($_SESSION['busqueda_user']) && empty($_SESSION['busqueda_user'])) {
            ?>
                <div class="row">
                    <div class="col-md-12">
                        <form action="" method="POST" autocomplete="off">
                            <input type="hidden" name="searchModule" value="user">
                            <div class="field is-grouped">
                                <p class="control is-expanded">
                                    <input class="form-control" type="text" name="txt_buscador" placeholder="¿Qué usuario estas buscando?" pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ ]{1,30}" maxlength="30">
                                </p>
                                <p class="control">
                                    <button class="btn btn-warning" type="submit">Buscar</button>
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
            <?php } else { ?>
                <form class="has-text-centered" action="" method="POST" autocomplete="off">
                    <div class="row">
                        <div class="col-md-12">
                            <input type="hidden" name="searchModule" value="user">
                            <input type="hidden" name="eliminar_buscador" value="user">
                            <p class="mb-2">Estas buscando <strong>"<?php echo $_SESSION['busqueda_user']; ?>"</strong></p>
                            <button type="submit" class="btn btn-danger is-rounded">Eliminar busqueda</button>
                        </div>
                    </div>
                </form>

                <hr>
            <?php
                # Eliminar usuario #
                if (isset($_GET['idUserDel'])) {
                    require_once "./controllers/deleteUser.php";
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
                $url = "index.php?page=showUser&pages=";
                $registros = 15;
                $busqueda = $_SESSION['busqueda_user']; /* <== */

                # Paginador usuario #
                require_once "./controllers/showUserController.php";
            }
            ?>
        </div>
    </div>
</div>