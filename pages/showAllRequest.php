<?php
require_once "./controllers/mainController.php";

$userID = $_SESSION["id"];
$nameUser = $_SESSION["nombre"];

$db = conexion();
$stmt = $db->query("
    SELECT 
        MAX(Mov.FechaMovimiento) AS FechaMovimiento,
        Mov.ComandaID,
        Mov.Status,
        Mov.UsuarioID,
        MAX(Mov.SucursalID) AS SucursalID,
        MAX(Mov.MovimientoID) AS MovimientoID,
        COUNT(DISTINCT Mov.ProductoID) AS TotalProductos,
        SUM(Mov.Cantidad) AS TotalCantidad,
        users.Nombre AS Solicitante,
        suc.nombre AS SucursalNombre
    FROM MovimientosInventario Mov
    INNER JOIN Usuarios users ON Mov.UsuarioID = users.UsuarioID
    LEFT JOIN Sucursales suc ON Mov.SucursalID = suc.SucursalID
    WHERE TipoMovimiento = 'Salida'
    GROUP BY Mov.ComandaID, Mov.Status, Mov.UsuarioID
    ORDER BY MovimientoID DESC
");

$datos = $stmt->fetchAll();

$hoy = [];
$ayer = [];
$anteriores = [];

foreach ($datos as $row) {
    $fechaMovimiento = new DateTime($row['FechaMovimiento']);
    $fechaHoy = new DateTime();
    $fechaHoy->setTime(0, 0);

    $fechaAyer = (clone $fechaHoy)->modify('-1 day');

    if ($fechaMovimiento >= $fechaHoy) {
        $hoy[] = $row;
    } elseif ($fechaMovimiento >= $fechaAyer && $fechaMovimiento < $fechaHoy) {
        $ayer[] = $row;
    } else {
        $anteriores[] = $row;
    }
}

function renderSolicitudes($solicitudes) {
    foreach ($solicitudes as $row) { ?>
        <div class="col-xl-6 col-md-12 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col-md-8 mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-2">
                                Fecha de la solicitud: <?php echo $row['FechaMovimiento']; ?>
                            </div>
                            <div class="h5 mb-2 font-weight-bold text-gray-800"><?php echo $row['ComandaID']; ?></div>
                            <div class="h6 mb-2 font-weight-bold text-gray-800">
                                Cantidad de Productos: <?php echo $row['TotalProductos']; ?>
                            </div>
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-2">
                                Sucursal de Destino: <?php echo $row['SucursalNombre']; ?>
                            </div>
                            <div class="text-xs font-weight-bold text-primary text-uppercase">
                                Solicitante: <?php echo $row['Solicitante']; ?>
                            </div>
                        </div>
                        <div class="col mr-2">
                            <div class="col-auto mb-2">
                                <span class="badge 
                                    <?php
                                    switch ($row['Status']) {
                                        case 'Abierto': echo 'badge-info'; break;
                                        case 'En transito': echo 'badge-warning'; break;
                                        case 'Cerrado': echo 'badge-success'; break;
                                        case 'Cancelado': echo 'badge-danger'; break;
                                        default: echo 'badge-secondary'; break;
                                    }
                                    ?> text-uppercase">
                                    <?php echo $row['Status']; ?>
                                </span>
                            </div>
                            <a href="index.php?page=showPDF&ComandaID=<?php echo $row['ComandaID']; ?>"
                               class="d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                               <i class="fas fa-download fa-sm text-white-50"></i> Ver Solicitud
                            </a>
                            <?php if ($row['Status'] === 'Abierto') { ?>
                                <a href="index.php?page=changeToTransit&ComandaID=<?php echo $row['ComandaID']; ?>"
                                   class="d-sm-inline-block btn btn-sm btn-secondary shadow-sm mt-2">
                                   <i class="fas fa-genderless fa-sm text-white-50"></i> Cambiar a "En transito"
                                </a>
                            <?php } elseif ($row['Status'] === 'En transito') { ?>
                                <a href="index.php?page=changeToDelivered&ComandaID=<?php echo $row['ComandaID']; ?>"
                                   class="d-sm-inline-block btn btn-sm btn-success shadow-sm mt-2">
                                   <i class="fas fa-genderless fa-sm text-white-50"></i> Cambiar a "Entregado"
                                </a>
                            <?php } ?>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-truck fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php }
}
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Historial de solicitudes</h1>
    </div>

    <div class="row">
        <?php if (count($hoy)) { ?>
            <div class="col-12 mb-3"><h4 class="text-primary">Hoy</h4></div>
            <?php renderSolicitudes($hoy); ?>
        <?php } ?>

        <?php if (count($ayer)) { ?>
            <div class="col-12 mt-4 mb-3"><h4 class="text-warning">Ayer</h4></div>
            <?php renderSolicitudes($ayer); ?>
        <?php } ?>

        <?php if (count($anteriores)) { ?>
            <div class="col-12 mt-4 mb-3"><h4 class="text-secondary">Anteriores</h4></div>
            <?php renderSolicitudes($anteriores); ?>
        <?php } ?>
    </div>
</div>
