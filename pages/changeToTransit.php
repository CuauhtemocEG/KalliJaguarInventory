<?php
require_once "./controllers/mainController.php";

$comandaID = $_GET['ComandaID'];

$toTransit = conexion();
$toTransit = $toTransit->prepare("UPDATE MovimientosInventario SET Status='En transito' WHERE ComandaID=:id");
$toTransit->execute([":id" => $comandaID]);

echo "<script>window.setTimeout(function() { window.location = 'index.php?page=showAllRequest' }, 10);</script>";
?>