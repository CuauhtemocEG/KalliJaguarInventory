<div id="wrapper">
    <ul class="navbar-nav bg-gradient-dark sidebar sidebar-dark accordion" id="accordionSidebar">
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php?page=home">
            <div class="sidebar-brand-icon">
                <img src="./img/Kalli-Amarillo.png" class="img-responsive" style="width:50%;">
            </div>
            <div class="sidebar-brand-text mx-3">Kalli Jaguar</div>
        </a>
        <hr class="sidebar-divider my-0">
        <li class="nav-item">
            <a class="nav-link" href="index.php?page=home">
                <span>Ir al Inicio</span></a>
        </li>
        <hr class="sidebar-divider">
        <div class="sidebar-heading">Gestión</div>
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo"
                aria-expanded="true" aria-controls="collapseTwo">
                <i class="fa fa-home"></i>
                <span>Sucursales</span>
            </a>
            <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Gestión Sucursales:</h6>
                    <a class="collapse-item" href="index.php?page=addSucursal">Agregar Sucursal</a>
                    <a class="collapse-item" href="index.php?page=showSucursal">Lista de Sucursales</a>
                </div>
            </div>
        </li>
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseUtilities"
                aria-expanded="true" aria-controls="collapseUtilities">
                <i class="fa fa-user"></i>
                <span>Usuarios</span>
            </a>
            <div id="collapseUtilities" class="collapse" aria-labelledby="headingUtilities"
                data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Gestión Usuarios:</h6>
                    <a class="collapse-item" href="index.php?page=addUser">Agregar Usuario</a>
                    <a class="collapse-item" href="index.php?page=showUser">Lista de Usuarios</a>
                    <a class="collapse-item" href="index.php?page=searchUser">Buscar Usuarios</a>
                </div>
            </div>
        </li>
        <hr class="sidebar-divider">
        <div class="sidebar-heading">Catálogo de Almacén</div>
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePages"
                aria-expanded="true" aria-controls="collapsePages">
                <i class="fa fa-archive"></i>
                <span>Producto</span>
            </a>
            <div id="collapsePages" class="collapse" aria-labelledby="headingPages" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Categorías:</h6>
                    <a class="collapse-item" href="index.php?page=addCategory">Agregar Categoría</a>
                    <a class="collapse-item" href="index.php?page=showCategory">Lista de Categoría</a>
                    <a class="collapse-item" href="index.php?page=searchCategory">Buscar Categoría</a>
                    <div class="collapse-divider"></div>
                    <h6 class="collapse-header">Producto:</h6>
                    <a class="collapse-item" href="index.php?page=addProduct">Agregar Productos</a>
                    <a class="collapse-item" href="index.php?page=showProduct">Lista de Productos</a>
                    <a class="collapse-item" href="index.php?page=productsByCategory">Productos por Categoría</a>
                    <a class="collapse-item" href="index.php?page=searchProduct">Buscar Producto</a>
                </div>
            </div>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="index.php?page=requestProducts">
                <i class="fa fa-truck"></i>
                <span>Solicitar Insumos</span></a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="index.php?page=showRequest">
                <i class="fa fa-list-alt"></i>
                <span>Mis Solicitudes</span></a>
        </li>
        <?php if($_SESSION['id']=='1'){?><li class="nav-item">
            <a class="nav-link" href="index.php?page=showAllRequest">
                <i class="fa fa-list-alt"></i>
                <span>Solicitudes Generales</span></a>
        </li><?php } ?>
        <hr class="sidebar-divider d-none d-md-block">
    </ul>
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                    <i class="fa fa-bars"></i>
                </button>
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#" id="cartToggle" data-toggle="tooltip" title="Ver carrito">
                            <i class="fa fa-shopping-cart"></i>
                        </a>
                    </li>
                    <div class="topbar-divider d-none d-sm-block"></div>
                    <li class="nav-item dropdown no-arrow">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo $_SESSION['nombre'] ?></span>
                            <img class="img-profile rounded-circle"
                                src="img/undraw_profile.svg">
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                            aria-labelledby="userDropdown">
                            <a class="dropdown-item" href="index.php?page=updateUser&idUserUpdate=<?php echo $_SESSION['id']; ?>">
                                <i class="fa fa-user-circle fa-sm fa-fw mr-2 text-gray-400"></i>
                                Mi Cuenta
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                <i class="fa fa-sign-out fa-sm fa-fw mr-2 text-gray-400"></i>
                                Cerrar Sesión
                            </a>
                        </div>
                    </li>
                </ul>
            </nav>

            <div id="cartPanel" class="cart-panel">
                <div class="cart-header">
                    <h4>Lista de Solicitud</h4>
                    <button id="closeCart" class="btn btn-danger btn-sm">Cerrar</button>
                </div>
                <div class="cart-body" id="cartBody">
                    <?php
                    if (isset($_SESSION['INV']) && count($_SESSION['INV']) > 0) {
                        $total = 0;
                        $totalItem = 0;
                        $res = 0;
                        foreach ($_SESSION['INV'] as $key => $item) {
                            $unidadesRes = '';

                            if ($item['tipo'] == "Pesable") {
                                if ($item['cantidad'] >= 1.0) {
                                    $unidadesRes = 'Kg';
                                    $res = number_format($item["cantidad"], 3);
                                } else {
                                    $unidadesRes = 'grs';
                                    $res = number_format($item["cantidad"], 3);
                                }
                            } else {
                                $unidadesRes = 'Un';
                                $res = number_format($item["cantidad"],0);
                            }
                            
                            $totalItem += $item['cantidad'];
                            $total += $item['cantidad'] * $item['precio'];

                            echo '<div class="col-md-12 mb-3">
    <div class="card mb-3 shadow-sm border-light">
        <div class="card-body d-flex flex-column position-relative">
            
            <span class="badge badge-danger badge-counter position-absolute" style="top: 0; right: 0;">
                '.$res . ' ' .$unidadesRes.'
            </span>
            
            <div class="text-md font-weight-bold text-dark mb-2">
                '.$item["nombre"].'
            </div>
            
            <div class="d-flex justify-content-between text-muted mb-3">
                <small>Precio unitario: $'.number_format($item["precio"], 2).'</small>
                <small>Total: $'. number_format($item["precio"] * $item["cantidad"], 2).'</small>
            </div>
            
            <div class="d-flex justify-content-end">
                <a href="index.php?page=deleteProductList&id='.$key.'" class="btn btn-danger btn-sm">
                    <i class="fas fa-trash-alt"></i> Eliminar
                </a>
            </div>
        </div>
    </div>
</div>';
                        }
                        echo '
                <div class="row justify-content-center">
                    <div class="col-12">
                        <div class="card shadow-lg p-4">
                            <h6 class="card-title text-center mb-4">Resumen de la Solicitud</h6>
                            
                            <div class="d-flex justify-content-between mb-3">
                                <strong>Total de productos:</strong>
                                <span class="h5 text-primary">'.$totalItem.'</span>
                            </div>
                            
                            <div class="d-flex justify-content-between mb-3">
                                <strong>Total a pagar:</strong>
                                <span class="h5 text-success">$'. number_format($total, 2) .'</span>
                            </div>

                            <div class="d-flex justify-content-center">
                                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#confirmModal">
                                    <i class="fas fa-check-circle"></i> Enviar Solicitud
                                </button>
                            </div>
                        </div>
                    </div>
                </div>';
                    } else {
                        echo '<p>No hay productos en el carrito.</p>';
                    }
                    ?>
                </div>
            </div>