<?php
require_once "./controllers/mainController.php";

$campos = "Productos.ProductoID,Productos.UPC,Productos.Nombre as productName,Productos.Descripcion,Productos.PrecioUnitario,Productos.Cantidad,Productos.image,Productos.CategoriaID,Productos.UsuarioID,Productos.Tipo,Categorias.CategoriaID,Categorias.Nombre as CatName,Usuarios.UsuarioID,Usuarios.Nombre,Usuarios.Username";

$checkInventory = conexion();
$checkInventory = $checkInventory->query("SELECT $campos FROM Productos INNER JOIN Categorias ON Productos.CategoriaID=Categorias.CategoriaID INNER JOIN Usuarios ON Productos.UsuarioID=Usuarios.UsuarioID");
$datos = $checkInventory->fetchAll();

$total = conexion();
$total = $total->query("SELECT COUNT(*) FROM Productos WHERE Cantidad < 5");
$totalCount = (int) $total->fetchColumn();

$totalProd = conexion();
$totalProd = $totalProd->query("SELECT COUNT(*) FROM Productos");
$totalCountProd = (int) $totalProd->fetchColumn();

?>
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Mis Solicitudes abiertas </h1>
        <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
                class="fas fa-download fa-sm text-white-50"></i> Nueva Solicitud</a>
    </div>

    <!-- Content Row -->
    <div class="row">

        <!-- Earnings (Monthly) Card Example -->
        <div class="col-xl-6 col-md-12 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col-md-8 mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                fecha de la solicitud: {{fecha}}</div>
                            <div class="h5 mb-1 font-weight-bold text-gray-800">${{Monto total}}</div>
                            <div class="h6 mb-0 font-weight-bold text-gray-800">Cantidad de articulos:
                                {{articulos}}
                            </div>
                        </div>
                        <div class="col mr-2">
                            <a href="#"
                                class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
                                    class="fas fa-download fa-sm text-white-50"></i> Ver Solicitud</a>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-truck fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Earnings (Monthly) Card Example -->
        <div class="col-xl-3 col-md-12 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col-md-8 mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                fecha de la solicitud: {{fecha}}</div>
                            <div class="h5 mb-1 font-weight-bold text-gray-800">${{Monto total}}</div>
                            <div class="h6 mb-0 font-weight-bold text-gray-800">Cantidad de articulos:
                                {{articulos}}
                            </div>
                        </div>
                        <div class="col mr-2">
                            <a href="#"
                                class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
                                    class="fas fa-download fa-sm text-white-50"></i> Ver Solicitud</a>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-truck fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Earnings (Monthly) Card Example -->
        <div class="col-xl-3 col-md-12 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col-md-8 mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                fecha de la solicitud: {{fecha}}</div>
                            <div class="h5 mb-1 font-weight-bold text-gray-800">${{Monto total}}</div>
                            <div class="h6 mb-0 font-weight-bold text-gray-800">Cantidad de articulos:
                                {{articulos}}
                            </div>
                        </div>
                        <div class="col mr-2">
                            <a href="#"
                                class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
                                    class="fas fa-download fa-sm text-white-50"></i> Ver Solicitud</a>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-truck fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Requests Card Example -->
        <div class="col-xl-3 col-md-12 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col-md-8 mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                fecha de la solicitud: {{fecha}}</div>
                            <div class="h5 mb-1 font-weight-bold text-gray-800">${{Monto total}}</div>
                            <div class="h6 mb-0 font-weight-bold text-gray-800">Cantidad de articulos:
                                {{articulos}}
                            </div>
                        </div>
                        <div class="col mr-2">
                            <a href="#"
                                class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
                                    class="fas fa-download fa-sm text-white-50"></i> Ver Solicitud</a>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-truck fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Content Row -->

    </div>

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Mis Solicitudes Cerradas </h1>
    </div>

    <div class="row">

        <!-- Earnings (Monthly) Card Example -->
        <div class="col-xl-3 col-md-12 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col-md-8 mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                fecha de la solicitud: {{fecha}}</div>
                            <div class="h5 mb-1 font-weight-bold text-gray-800">${{Monto total}}</div>
                            <div class="h6 mb-0 font-weight-bold text-gray-800">Cantidad de articulos:
                                {{articulos}}
                            </div>
                        </div>
                        <div class="col mr-2">
                            <a href="#"
                                class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
                                    class="fas fa-download fa-sm text-white-50"></i> Ver Solicitud</a>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-truck fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Earnings (Monthly) Card Example -->
        <div class="col-xl-3 col-md-12 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col-md-8 mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                fecha de la solicitud: {{fecha}}</div>
                            <div class="h5 mb-1 font-weight-bold text-gray-800">${{Monto total}}</div>
                            <div class="h6 mb-0 font-weight-bold text-gray-800">Cantidad de articulos:
                                {{articulos}}
                            </div>
                        </div>
                        <div class="col mr-2">
                            <a href="#"
                                class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
                                    class="fas fa-download fa-sm text-white-50"></i> Ver Solicitud</a>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-truck fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Earnings (Monthly) Card Example -->
        <div class="col-xl-3 col-md-12 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col-md-8 mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                fecha de la solicitud: {{fecha}}</div>
                            <div class="h5 mb-1 font-weight-bold text-gray-800">${{Monto total}}</div>
                            <div class="h6 mb-0 font-weight-bold text-gray-800">Cantidad de articulos:
                                {{articulos}}
                            </div>
                        </div>
                        <div class="col mr-2">
                            <a href="#"
                                class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
                                    class="fas fa-download fa-sm text-white-50"></i> Ver Solicitud</a>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-truck fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Requests Card Example -->
        <div class="col-xl-3 col-md-12 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col-md-8 mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                fecha de la solicitud: {{fecha}}</div>
                            <div class="h5 mb-1 font-weight-bold text-gray-800">${{Monto total}}</div>
                            <div class="h6 mb-0 font-weight-bold text-gray-800">Cantidad de articulos:
                                {{articulos}}
                            </div>
                        </div>
                        <div class="col mr-2">
                            <a href="#"
                                class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
                                    class="fas fa-download fa-sm text-white-50"></i> Ver Solicitud</a>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-truck fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Content Row -->

    </div>
</div>