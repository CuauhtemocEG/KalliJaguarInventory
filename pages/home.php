<?php 
require_once "./controllers/mainController.php";

$checkInventory = conexion();
$checkInventory = $checkInventory->query("SELECT Nombre, Cantidad FROM Productos WHERE Cantidad < 5");
?>
<div class="container-fluid" style="padding-top:15px; padding-bottom:15px">
	<h1 class="title">En desarrollo...</h1><br>
	<h2 class="subtitle">¡Bienvenido <?php echo $_SESSION['nombre']?>!</h2>
	<h3> Ingresaste con un Rol de: <?php echo $_SESSION['rol'] ?></h3>
</div>