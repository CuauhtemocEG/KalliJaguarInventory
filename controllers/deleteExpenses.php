<?php
$host = 'localhost:3306';
$usuario = 'kallijag_stage'; // tu usuario de base de datos
$clave = 'uNtiL.horSe@5';       // tu contraseña de base de datos
$baseDeDatos = 'kallijag_inventory_stage';

$conexion = new mysqli($host, $usuario, $clave, $baseDeDatos);

if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $sql = "DELETE FROM Gastos WHERE ID = $id";

    if ($conexion->query($sql) === TRUE) {
        header('Location: index.php?page=expensesWeekly');
    } else {
        echo "Error: " . $conexion->error;
    }
}
?>