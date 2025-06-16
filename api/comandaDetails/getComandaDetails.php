<?php
require_once '../../controllers/mainController.php';

if (!isset($_GET['ComandaID'])) {
    http_response_code(400);
    echo "ComandaID no proporcionado.";
    exit;
}

$comandaID = intval($_GET['ComandaID']);
$conn = conexion();

$query = $conn->prepare("
    SELECT 
    m.ProductoID, 
    p.Nombre, 
    p.Descripcion, 
    p.PrecioUnitario, 
    m.Cantidad, 
    m.PrecioFinal, 
    (m.Cantidad * (p.PrecioUnitario*1.16)) AS Subtotal, 
    u.Nombre AS Solicitante, 
    s.nombre AS Sucursal 
    FROM MovimientosInventario m 
    JOIN Productos p ON m.ProductoID = p.ProductoID 
    JOIN Usuarios u ON m.UsuarioID = u.UsuarioID 
    JOIN Sucursales s ON m.SucursalID = s.SucursalID 
    WHERE m.ComandaID = :comandaID 
    AND m.TipoMovimiento = 'Salida'");
$query->execute([':comandaID' => $comandaID]);
$productos = $query->fetchAll(PDO::FETCH_ASSOC);

if (!$productos) {
    echo "<div class='text-muted'>No se encontraron productos para esta comanda.</div>";
    exit;
}

$solicitante = $productos[0]['Solicitante'];
$sucursal = $productos[0]['Sucursal'];
$total = 0;
?>

<div class="mb-2">
    <strong>Solicitante:</strong> <?php echo $solicitante; ?><br>
    <strong>Sucursal destino:</strong> <?php echo $sucursal; ?>
</div>

<table class="table table-sm table-bordered">
    <thead class="thead-light">
        <tr>
            <th>Producto</th>
            <th>Descripción</th>
            <th>Cantidad</th>
            <th>Precio</th>
            <th>Subtotal</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($productos as $producto): 
            $total += $producto['Subtotal'];
        ?>
        <tr>
            <td><?php echo $producto['Nombre']; ?></td>
            <td><?php echo $producto['Descripcion']; ?></td>
            <td><?php echo $producto['Cantidad']; ?></td>
            <td>$<?php echo number_format($producto['PrecioFinal'], 2); ?></td>
            <td>$<?php echo number_format($producto['Subtotal'], 2); ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <th colspan="4" class="text-right">Total:</th>
            <th>$<?php echo number_format($total, 2); ?></th>
        </tr>
    </tfoot>
</table>
