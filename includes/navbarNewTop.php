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
                <i class="fas fa-fw fa-cog"></i>
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
                <i class="fas fa-fw fa-wrench"></i>
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
                <i class="fas fa-fw fa-folder"></i>
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
                    <a class="collapse-item" href="index.php?page=productsByCategory">Productos x Categoría</a>
                    <a class="collapse-item" href="index.php?page=searchProduct">Buscar Producto</a>
                </div>
            </div>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="index.php?page=requestProducts">
                <i class="fas fa-fw fa-chart-area"></i>
                <span>Solicitar Insumos</span></a>
        </li>
        <hr class="sidebar-divider d-none d-md-block">
        <div class="text-center d-none d-md-inline">
            <button class="rounded-circle border-0" id="sidebarToggle"></button>
        </div>
        <div class="sidebar-card d-none d-lg-flex">
            <p class="text-center mb-2"><strong>Bienvenido al nuevo landing</strong> constantemente el sitio tendrá mejoras, si detectas algún problema avisa al administrador del sitio.</p>
        </div>

    </ul>
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                    <i class="fa fa-bars"></i>
                </button>
                <ul class="navbar-nav ml-auto">
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
                                <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                Mi Cuenta
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                Cerrar Sesión
                            </a>
                        </div>
                    </li>
                </ul>
            </nav>