<?php
require_once "mainController.php";
// Obtenemos la fecha actual
$fechaActual = date('Y-m-d');

// Generamos el calendario para el mes actual
$primerDiaDelMes = date('Y-m-01', strtotime($fechaActual));
$ultimoDiaDelMes = date('Y-m-t', strtotime($fechaActual));

// Obtenemos los gastos del mes actual
$conexion = conexion();

$datos = $conexion->query("SELECT * FROM Gastos WHERE Fecha BETWEEN '$primerDiaDelMes' AND '$ultimoDiaDelMes'");

$gastos = [];
while ($fila =$datos->fetchAll()) {
    $gastos[$fila['Fecha']][] = ['Descripcion' => $fila['Descripcion'], 'Monto' => $fila['Monto']];
}

// Crear la vista del calendario
echo '<div class="table-responsive"><table class="table table-bordered"><thead><tr>';

$diasSemana = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];

foreach ($diasSemana as $dia) {
    echo "<th>$dia</th>";
}

echo '</tr></thead><tbody><tr>';

$primerDiaSemana = date('w', strtotime($primerDiaDelMes));

// Rellenamos los días anteriores al primer día del mes
for ($i = 0; $i < $primerDiaSemana; $i++) {
    echo '<td></td>';
}

// Mostrar los días del mes
$numeroDeDia = 1;
while ($numeroDeDia <= date('t', strtotime($fechaActual))) {
    $fechaDia = date('Y-m-d', strtotime("$fechaActual-$numeroDeDia"));
    echo '<td>';
    echo "<strong>$numeroDeDia</strong><br>";
    
    if (isset($gastos[$fechaDia])) {
        foreach ($gastos[$fechaDia] as $gasto) {
            echo "{$gasto['Descripcion']} - {$gasto['Monto']}<br>";
        }
    }

    echo '</td>';

    if (($numeroDeDia + $primerDiaSemana) % 7 == 0) {
        echo '</tr><tr>';
    }

    $numeroDeDia++;
}

echo '</tr></tbody></table></div>';
?>