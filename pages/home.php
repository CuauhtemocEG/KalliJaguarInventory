<?php 
require_once "./controllers/mainController.php";

$checkInventory = conexion();
$checkInventory = $checkInventory->query("SELECT COUNT(*) FROM Productos WHERE Cantidad < 5");
$datos = (int) $checkInventory->fetchColumn();
?>
<div class="container-fluid" style="padding-top:15px; padding-bottom:15px">
	<h1 class="title">En desarrollos...</h1><br>
	<h2 class="subtitle">¡Bienvenido <?php echo $_SESSION['nombre']?>!</h2>
	<h3> Ingresaste con un Rol de: <?php echo $_SESSION['rol'] ?></h3>
	<p>No de registros en BD con inventario < 5 <?php echo $totalCount ?></p>
</div>