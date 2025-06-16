<?php
require_once "./controllers/mainController.php";
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$userID = $_SESSION["id"];
$nameUser = $_SESSION["nombre"];

$showComanda = conexion();
$showComanda = $showComanda->query("SELECT MAX(FechaMovimiento) AS FechaMovimiento, Status, ComandaID, Status, MAX(SucursalID) AS SucursalID, MAX(MovimientoID) AS MovimientoID, COUNT(DISTINCT ProductoID) AS TotalProductos, SUM(Cantidad) AS TotalCantidad FROM MovimientosInventario WHERE TipoMovimiento = 'Salida' AND UsuarioID = $userID GROUP BY ComandaID, Status, UsuarioID ORDER BY MovimientoID DESC");
$datos = $showComanda->fetchAll();
$num = 0;
?>
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Mis Solicitudes abiertas </h1>
        <a href="index.php?page=requestProducts" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
                class="fas fa-download fa-sm text-white-50"></i> Nueva Solicitud</a>
    </div>

    <div class="row">
        <?php foreach ($datos as $row) { ?>

            <?php
            $num += 1;
            $dataSucursal = conexion();
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
                                <div class="col-auto mb-2">
                                    <span class="badge <?php
                                                        switch ($row['Status']) {
                                                            case 'Abierto':
                                                                echo 'badge-info';
                                                                break;
                                                            case 'En transito':
                                                                echo 'badge-warning';
                                                                break;
                                                            case 'Cerrado':
                                                                echo 'badge-success';
                                                                break;
                                                            case 'Cancelado':
                                                                echo 'badge-danger';
                                                                break;
                                                            default:
                                                                echo 'badge-secondary';
                                                                break;
                                                        }
                                                        ?> text-uppercase"><?php echo $row['Status']; ?></span>
                                </div>
                                <a href="index.php?page=showPDF&ComandaID=<?php echo $row['ComandaID']; ?>"
                                    class="d-sm-inline-block btn btn-sm btn-primary shadow-sm mt-1 mb-1"><i
                                        class="fas fa-download fa-sm text-white-50"></i> Ver Solicitud</a>
                                <a class="d-sm-inline-block btn btn-sm btn-info shadow-sm mt-1 mb-1"
                                    data-toggle="collapse"
                                    href="#collapseComanda<?php echo $row['ComandaID']; ?>"
                                    role="button"
                                    aria-expanded="false"
                                    aria-controls="collapseComanda<?php echo $row['ComandaID']; ?>">
                                    <i class="fas fa-eye fa-sm text-white-50"></i> Ver más
                                </a>
                                <?php if ($row['Status'] === 'Abierto') { ?><a class="d-sm-inline-block btn btn-sm btn-danger shadow-sm" href="#" data-toggle="modal" data-target="#deleteModal_<?php echo $row['ComandaID']; ?>"><i
                                            class="fas fa-trash fa-sm text-white-50"></i> Cancelar Solicitud</a><?php } ?>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-truck fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="collapse col-12" id="collapseComanda<?php echo $row['ComandaID']; ?>">
                <div class="card card-body border-left-info shadow-sm mb-4">
                    <div id="detalleComanda_<?php echo $row['ComandaID']; ?>">
                        <div class="text-center text-muted">Cargando información...</div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="deleteModal_<?php echo $row['ComandaID']; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">¿Estas Seguro de eliminar la comanda?</h5>
                            <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                        <div class="modal-body">Presiona <b>"Cancelar Comanda"</b> para elminar permanentemente el registro de la base de datos (el stock regresará a estar disponible).</div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                            <a class="btn btn-primary" href="index.php?page=cancelRequest&ComandaID=<?php echo $row['ComandaID']; ?>">Cancelar Comanda</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const collapses = document.querySelectorAll("[id^='collapseComanda']");

        collapses.forEach(collapse => {
            collapse.addEventListener("show.bs.collapse", function() {
                const comandaID = this.id.replace("collapseComanda", "");
                const container = document.getElementById("detalleComanda_" + comandaID);
                container.innerHTML = "<div class='text-muted'>Cargando detalles...</div>";

                fetch(`https://stagging.kallijaguar-inventory.com/api/comandaDetails/getComandaDetails.php?ComandaID=${comandaID}`)
                    .then(res => res.text())
                    .then(html => {
                        console.log("Respuesta del backend:", html);
                        container.innerHTML = html;
                    })
                    .catch(err => {
                        container.innerHTML = "<div class='text-danger'>Error al cargar detalles.</div>";
                        console.error(err);
                    });
            });
        });
    });
</script>