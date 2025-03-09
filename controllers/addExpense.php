<?php
include './mainController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $descripcion = $_POST['descripcion'];
    $monto = $_POST['monto'];
    $fecha = $_POST['fecha'];

    $sql = "INSERT INTO Gastos (Descripcion,Monto,Fecha) VALUES ('$descripcion', '$monto', '$fecha')";

    if ($conexion->query($sql) === TRUE) {
        header('Location: index.php?page=expensesWeekly');
    } else {
        echo "Error: " . $conexion->error;
    }
}
?>
<div class="container mt-5">
    <h1 class="text-center">Agregar Gasto</h1>

    <form action="addExpense.php" method="POST">
        <div class="mb-3">
            <label for="descripcion" class="form-label">Descripción</label>
            <input type="text" class="form-control" id="descripcion" name="descripcion" required>
        </div>
        <div class="mb-3">
            <label for="monto" class="form-label">Monto</label>
            <input type="number" class="form-control" id="monto" name="monto" required>
        </div>
        <div class="mb-3">
            <label for="fecha" class="form-label">Fecha</label>
            <input type="date" class="form-control" id="fecha" name="fecha" required>
        </div>
        <button type="submit" class="btn btn-primary">Guardar Gasto</button>
    </form>
</div>