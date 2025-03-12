<div class="container-fluid" style="padding-top:15px;padding-bottom:15px">
    <?php
    session_start();
    require_once "./controllers/mainController.php";
    ?>
    <div class="card">
        <div class="card-header font-weight-bold">Solicitud de Insumos a Sucursal</div>
        <div class="card-body">
            <h4 class="card-title font-weight-bold">Categorías Disponibles</h4>
            <div class="row justify-content-center">
                <div class="col-md-12">
                    <div class="list-group" id="list-tab" role="tablist">
                        <?php
                        $categorias = conexion();
                        $categorias = $categorias->query("SELECT * FROM Categorias");
                        if ($categorias->rowCount() > 0) {
                            $categorias = $categorias->fetchAll();
                            foreach ($categorias as $row) {
                                echo '
                        <a class="list-group-item list-group-item-action text-white bg-dark" href="index.php?page=requestProducts&category_id=' . $row['CategoriaID'] . '">' . $row['Nombre'] . '</a>';
                            }
                        } else {
                            echo '<p class="has-text-centered" >No hay categorías registradas</p>';
                        }
                        $categorias = null;
                        ?>
                    </div>
                </div>
            </div>
            <hr>
            <div class="row justify-content-center">
                <?php
                $categoria_id = (isset($_GET['category_id'])) ? $_GET['category_id'] : 0;

                /*== Verificando categoria ==*/
                $check_categoria = conexion();
                $check_categoria = $check_categoria->query("SELECT * FROM Categorias WHERE CategoriaID='$categoria_id'");

                if ($check_categoria->rowCount() > 0) {

                    $check_categoria = $check_categoria->fetch();

                    require_once "./controllers/mainController.php";

                    # Eliminar producto #
                    if (isset($_GET['productIdDel'])) {
                        require_once "./controllers/deleteProduct.php";
                    }

                    if (isset($_GET['id'])) {
                        require_once "deleteProductList.php";
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
                    $url = "index.php?page=requestProducts&category_id=$categoria_id&pages="; /* <== */
                    $registros = 15;
                    $busqueda = "";

                    # Paginador producto #
                    require_once "./controllers/showProductsListController.php";
                } else {
                    echo '
                    <div class="has-text-centered col-md-12">
				        <div class="alert alert-secondary alert-dismissible fade show" role="alert">
                            <strong>Seleccione una categoría para empezar</strong>
                        </div>
			        </div>';
                }
                $check_categoria = null;
                ?>
            </div>
            <hr>
            <div class="row justify-content-center">
                <?php
                if (isset($_POST['agregar'])) {
                    // Obtiene los datos del producto
                    $producto = $_POST['idProduct'];
                    $precio = $_POST['precioProduct'];
                    $cantidad = $_POST['cantidadProduct'];
                    $nombre = $_POST['nameProduct'];

                    // Si no existe la variable de sesión carrito, la creamos
                    if (!isset($_SESSION['INV'])) {
                        $_SESSION['INV'] = [];
                    }

                    // Comprobamos si el producto ya está en el carrito
                    $productoExistente = false;
                    foreach ($_SESSION['INV'] as $key => $item) {
                        if ($item['producto'] == $producto) {
                            $_SESSION['INV'][$key]['cantidad'] += $cantidad;
                            $productoExistente = true;
                            break;
                        }
                    }

                    // Si el producto no existe, lo agregamos
                    if (!$productoExistente) {
                        $_SESSION['INV'][] = [
                            'producto' => $producto,
                            'precio' => $precio,
                            'nombre' => $nombre,
                            'cantidad' => $cantidad
                        ];
                    }

                    echo "<div class='row justify-content-center col-md-12'>Producto agregado a la Lista de solicitud. <a href='index.php?vista=solicitarInsumos'>Ver Lista</a></div><br>";
                }

                ?>
                <div class="col-md-10">
                    <h5 class="card-title h5 font-weight-bold">Lista de productos solicitados a almacen</h5>
                    <?php
                    $table = "";
                    // Mostrar los productos en el carrito
                    if (isset($_SESSION['INV']) && count($_SESSION['INV']) > 0) {
                        $table = "<div class='table-responsive'>
        <table class='table table-bordered' width='100%' cellspacing='0'><tr><th>Producto</th><th>Precio</th><th>Cantidad</th><th>Total</th><th>Acciones</th></tr>";

                        $total = 0;
                        foreach ($_SESSION['INV'] as $key => $item) {
                            $totalItem = $item['precio'] * $item['cantidad'];
                            $total += $totalItem;
                            $table .= "<tr>
                                    <td>{$item['nombre']}</td>
                                    <td>\${$item['precio']}</td>
                                    <td>{$item['cantidad']}</td>
                                    <td>\${$totalItem}</td>
                                    <td><a href='index.php?page=deleteProductList&id={$key}' class='btn btn-danger btn-sm'>Eliminar</a></td>
                                </tr>";
                        }

                        $table .= '<tr><td colspan="3">Total</td><td>$' . $total . '</td><td>
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#confirmModal">
                            Enviar Solicitud
                        </button>
                        </td></tr>';
                        $table .= '</table></div>';
                    } else {
                        $table .= '<div class="has-text-centered alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>¡La lista esta vacía!</strong>
                    </div>';
                    }

                    echo $table;
                    ?>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">Confirmar Solicitud a Almacén</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    ¿Está seguro de que desea enviar la lista de productos solicitados?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" action="generar_pdf.php" id="confirmForm">
                        <button type="submit" class="btn btn-primary">Confirmar Pedido</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>