<?php
$host = 'localhost:3306';
$usuario = 'kallijag_stage';
$clave = 'uNtiL.horSe@5';
$baseDeDatos = 'kallijag_inventory_stage';

$conexion = new mysqli($host, $usuario, $clave, $baseDeDatos);

if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}

$fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');  // Fecha de inicio por defecto el primer día del mes
$fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d');          // Fecha de fin por defecto el día de hoy

// Función para obtener los gastos dentro de un rango de fechas
function obtenerGastos($conexion, $fechaInicio, $fechaFin)
{
    $sql = "SELECT * FROM Gastos WHERE Fecha BETWEEN '$fechaInicio' AND '$fechaFin'";
    $resultado = $conexion->query($sql);
    return $resultado->fetch_all(MYSQLI_ASSOC);
}

// Obtener los gastos
$gastos = obtenerGastos($conexion, $fechaInicio, $fechaFin);

// Función para calcular el total de los gastos
function calcularTotal($gastos)
{
    $total = 0;
    foreach ($gastos as $gasto) {
        $total += $gasto['Monto'];
    }
    return $total;
}

$totalGastos = calcularTotal($gastos);
?>
<div class="container-fluid" style="padding-top:15px; padding-bottom:15px">
    <div class="card">
        <div class="card-header font-weight-bold">Gestión de Gastos - Modulo May</div>
        <div class="card-body">
            <div class="form-rest"></div>
            <!-- Filtro de fechas -->
            <form action="" method="GET">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="fecha_inicio" class="form-label">Fecha de inicio</label>
                        <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="<?php echo $fechaInicio; ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="fecha_fin" class="form-label">Fecha de fin</label>
                        <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="<?php echo $fechaFin; ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" style="visibility: hidden;">Buscar</label>
                        <button type="submit" class="btn btn-primary form-control">Buscar</button>
                    </div>
                </div>
            </form>

            <!-- Resumen de gastos -->
            <h3>Resumen de Gastos</h3>
            <ul class="list-group mb-4">
                <li class="list-group-item">
                    <strong>Total de Gastos: </strong> $<?php echo number_format($totalGastos, 2); ?>
                </li>
            </ul>

            <!-- Mostrar los gastos -->
            <h3>Lista de Gastos</h3>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Descripción</th>
                        <th>Monto</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($gastos) > 0): ?>
                        <?php foreach ($gastos as $gasto): ?>
                            <tr>
                                <td><?php echo $gasto['Descripcion']; ?></td>
                                <td>$<?php echo number_format($gasto['Monto'], 2); ?></td>
                                <td><?php echo $gasto['Fecha']; ?></td>
                                <td>
                                    <a href="deleteExpenses.php?id=<?php echo $gasto['ID']; ?>" class="btn btn-danger btn-sm">Eliminar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">No hay gastos registrados en este rango de fechas.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Agregar gasto -->
            <a href="index.php?page=addExpense" class="btn btn-success mt-3">Agregar Nuevo Gasto</a>
        </div>
    </div>
</div>