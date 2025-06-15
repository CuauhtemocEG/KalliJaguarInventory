<?php
require_once('./controllers/mainController.php');
$pdo = conexion();

// Consulta que une Logs_stock con Usuarios
$sql = "SELECT ls.id, ls.UPC, ls.StockBefore, ls.StockAfter, ls.Fecha, 
               u.Nombre AS Usuario
        FROM Logs_stock ls
        INNER JOIN Usuarios u ON ls.UsuarioID = u.UsuarioID
        ORDER BY ls.Fecha DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid mt-4">
    <h4 class="text-dark mb-4"><i class="fa fa-history"></i> Historial de Actualización de Stock</h4>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="logsTable" width="100%" cellspacing="0">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>UPC</th>
                            <th>Stock Anterior</th>
                            <th>Stock Nuevo</th>
                            <th>Fecha</th>
                            <th>Usuario</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($logs as $log): ?>
                            <tr>
                                <td><?= htmlspecialchars($log['id']) ?></td>
                                <td><?= htmlspecialchars($log['UPC']) ?></td>
                                <td><?= htmlspecialchars($log['StockBefore']) ?></td>
                                <td><?= htmlspecialchars($log['StockAfter']) ?></td>
                                <td><?= date("d/m/Y H:i:s", strtotime($log['Fecha'])) ?></td>
                                <td><?= htmlspecialchars($log['Usuario']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($logs)): ?>
                            <tr><td colspan="6" class="text-center">No hay registros disponibles</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script>
    $(document).ready(function() {
        $('#logsTable').DataTable({
            "order": [[ 0, "desc" ]],
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-MX.json"
            }
        });
    });
</script>