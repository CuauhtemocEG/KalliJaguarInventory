<?php
require_once "./controllers/mainController.php";

$userID = $_SESSION["id"];
$nameUser = $_SESSION["nombre"];

$showComanda = conexion();
$showComanda = $showComanda->query("SELECT MAX(FechaMovimiento) AS FechaMovimiento, ComandaID, MAX(SucursalID) AS SucursalID, MAX(MovimientoID) AS MovimientoID, COUNT(DISTINCT ProductoID) AS TotalProductos, SUM(Cantidad) AS TotalCantidad FROM MovimientosInventario WHERE TipoMovimiento = 'Salida' AND UsuarioID = $userID GROUP BY ComandaID ORDER BY MovimientoID DESC");
$datos = $showComanda->fetchAll();

?>
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Mis Solicitudes abiertas </h1>
        <a href="index.php?page=requestProducts" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
                class="fas fa-download fa-sm text-white-50"></i> Nueva Solicitud</a>
    </div>

    <div class="row">
        <?php foreach ($datos as $row) { ?>

            <?php $dataSucursal = conexion();
            $dataSucursal = $dataSucursal->query("SELECT nombre FROM Sucursales WHERE SucursalID = " . $row['SucursalID'] . "");
            $nameSucursal = $dataSucursal->fetchColumn(); ?>


            <div class="col-xl-6 col-md-12 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col-md-8 mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-2">
                                    fecha de la solicitud: <?php echo $row['FechaMovimiento']; ?></div>
                                <div class="h5 mb-2 font-weight-bold text-gray-800"><?php echo $row['ComandaID']; ?></div>
                                <div class="h6 mb-0 font-weight-bold text-gray-800">Cantidad de Productos:
                                    <?php echo $row['TotalProductos']; ?>
                                </div>
                                <div class="text-xs font-weight-bold text-secondary text-uppercase mt-2">
                                    Sucursal de Destino: <?php echo $nameSucursal; ?></div>
                            </div>
                            <div class="col mr-2">
                                <a href="index.php?page=showPDF&ComandaID=<?php echo $row['ComandaID']; ?>"
                                    class="d-sm-inline-block btn btn-sm btn-primary shadow-sm mt-1 mb-1"><i
                                        class="fas fa-download fa-sm text-white-50"></i> Ver Solicitud</a>
                                <a href="index.php?page=cancelRequest&ComandaID=<?php echo $row['ComandaID']; ?>"
                                    class="d-sm-inline-block btn btn-sm btn-danger shadow-sm"><i
                                        class="fas fa-trash fa-sm text-white-50"></i> Cancelar Solicitud</a>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-truck fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div><?php } ?>

    </div>
</div>