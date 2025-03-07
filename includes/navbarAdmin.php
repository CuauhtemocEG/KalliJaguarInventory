<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="index.php?page=home">
        <img
            src="./img/logo.png"
            width="190"
            height="50"
            class="d-inline-block align-top"
            alt="" />
    </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNavDropdown">
        <ul class="navbar-nav">
            <li class="nav-item dropdown">
                <a
                    class="nav-link dropdown-toggle"
                    href="#"
                    role="button"
                    data-toggle="dropdown"
                    aria-expanded="false">
                    Sucursales
                </a>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="index.php?page=addSucursal">Agregar Sucursal</a>
                    <a class="dropdown-item" href="index.php?page=showSucursal">Lista de Sucursales</a>
                </div>
            </li>
            <li class="nav-item dropdown">
                <a
                    class="nav-link dropdown-toggle"
                    href="#"
                    role="button"
                    data-toggle="dropdown"
                    aria-expanded="false">
                    Usuarios
                </a>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="index.php?page=addUser">Agregar Usuario</a>
                    <a class="dropdown-item" href="index.php?page=showUser">Lista de Usuarios</a>
                    <a class="dropdown-item" href="index.php?page=searchUser">Buscar Usuarios</a>
                </div>
            </li>
            <li class="nav-item dropdown">
                <a
                    class="nav-link dropdown-toggle"
                    href="#"
                    role="button"
                    data-toggle="dropdown"
                    aria-expanded="false">
                    Categorías
                </a>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="index.php?page=addCategory">Agregar Categoría</a>
                    <a class="dropdown-item" href="index.php?page=showCategory">Lista de Categoría</a>
                    <a class="dropdown-item" href="index.php?page=searchCategory">Buscar Categoría</a>
                </div>
            </li>
            <li class="nav-item dropdown">
                <a
                    class="nav-link dropdown-toggle"
                    href="#"
                    role="button"
                    data-toggle="dropdown"
                    aria-expanded="false">
                    Productos
                </a>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="index.php?page=addProduct">Agregar Productos</a>
                    <a class="dropdown-item" href="index.php?page=showProduct">Lista de Productos</a>
                    <a class="dropdown-item" href="index.php?page=productsByCategory">Productos x Categoría</a>
                    <a class="dropdown-item" href="index.php?page=searchProduct">Buscar Producto</a>
                </div>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="index.php?page=requestProducts">Solicitar Insumos <span class="sr-only">(current)</span></a>
            </li>
        </ul>
        <div class="navbar-end">
            <div class="navbar-item">
                <div class="btn-group">
                    <a href="index.php?page=updateUser&idUserUpdate=<?php echo $_SESSION['id']; ?>" class="btn btn-outline-warning">
                        Mi cuenta
                    </a>
                    <a href="index.php?page=logout" class="btn btn-outline-danger">
                        Salir
                    </a>
                </div>
            </div>
        </div>
    </div>
</nav>