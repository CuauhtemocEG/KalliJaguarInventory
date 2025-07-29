<?php
require_once "../../controllers/mainController.php";

if (!isset($_GET['ComandaID'])) {
    echo 'No se recibió una Comanda para eliminar';
}

$comandaID = $_GET['ComandaID'];
$idUser = $_SESSION['id'];

$conexion = conexion();
$datos = $conexion->query("SELECT Cantidad, ProductoID FROM MovimientosInventario WHERE ComandaID='$comandaID'");
$datos = $datos->fetchAll();

foreach ($datos as $item) {

    $product = $item['ProductoID'];

    $consultStock = conexion();
    $consultStock = $consultStock->query("SELECT Cantidad as Quantity FROM Productos WHERE ProductoID='$product'");
    $stockBefore = $consultStock->fetchColumn();

    $newStock = $stockBefore + $item['Cantidad'];

    try {
        $updateProducts = conexion();
        $updateProducts = $updateProducts->prepare("UPDATE Productos SET Cantidad=:stock WHERE ProductoID=:productoID");

        $updateProducts->execute([
            ':stock' => $newStock,
            ':productoID' => $item['ProductoID']
        ]);
    } catch (Exception $e) {
        echo "Error al actualizar stock de los productos cancelados: " . $e->getMessage();
    }
}

$deleteComanda = conexion();
$deleteComanda = $deleteComanda->prepare("UPDATE MovimientosInventario SET Status='Cancelado' WHERE ComandaID=:id");
$deleteComanda->execute([":id" => $comandaID]);

$emailUser = conexion();
$emailUser = $emailUser->query("SELECT Email FROM Usuarios WHERE UsuarioID = '$idUser'");
$Usermail = $emailUser->fetchColumn();

require './PHPMailer/src/PHPMailer.php';
require './PHPMailer/src/SMTP.php';
require './PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Debugoutput = 'html';
    $mail->Host = 'smtp.titan.email';
    $mail->SMTPAuth = true;
    $mail->Username = 'info@stagging.kallijaguar-inventory.com';
    $mail->Password = 'KalliJaguar2025@';
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;

    $mail->setFrom('info@stagging.kallijaguar-inventory.com', 'Informacion Kalli Jaguar');
    $mail->addAddress('cencarnacion@stagging.kallijaguar-inventory.com');
    //$mail->addAddress('mauricio.dominguez@kallijaguar-inventory.com');
    //$mail->addCC('julieta.ramirez@kallijaguar-inventory.com');
    //$mail->addCC('miguel.loaeza@kallijaguar-inventory.com');
    //$mail->addCC('andrea.sanchez@kallijaguar-inventory.com');
    //$mail->addCC('may.sanchez@kallijaguar-inventory.com');
    //$mail->addCC('cencarnacion@kallijaguar-inventory.com');

    $mail->isHTML(true);
    $mail->Subject = 'Comanda Cancelada: ' . $comandaID;
    $mail->Body = "<p>Se ha generado una cancelacion de la siguiente comanda: <strong>{$comandaID}</strong></p>
    <p>La solicitud realizada sera eliminada del Sistema y el stock reservado pasara a ser disponible nuevamente.</p>
    <p>Saludos.</p>";
    $mail->send();
    echo 'El mensaje ha sido enviado con éxito.';
} catch (Exception $e) {
    echo "El mensaje no pudo ser enviado: {$mail->ErrorInfo}";
}

echo "<script>window.setTimeout(function() { window.location = 'index.php?page=showRequest' }, 10);</script>";
exit();
