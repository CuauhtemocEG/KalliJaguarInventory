<?php
require_once "./controllers/mainController.php";

$comandaID = $_GET['ComandaID'];

$toDelivered = conexion();
$toDelivered = $toDelivered->prepare("UPDATE MovimientosInventario SET Status='Cerrado' WHERE ComandaID=:id");
$toDelivered->execute([":id" => $comandaID]);

echo "<script>window.setTimeout(function() { window.location = 'index.php?page=showAllRequest' }, 10);</script>";
?>