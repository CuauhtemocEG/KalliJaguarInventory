<?php
require_once BASE_PATH . '/src/Helpers/legacy.php';

if (!isset($_GET['ComandaID'])) {
    echo 'No se recibió una Comanda para eliminar';
}

$comandaID = $_GET['ComandaID'];
$idUser = $_SESSION['id'];

$conexion = conexion();
$datos = consulta($conexion, "SELECT Cantidad, ProductoID FROM MovimientosInventario WHERE ComandaID=:p1", [':p1' => $comandaID]);
$datos = $datos->fetchAll();

foreach ($datos as $item) {

    $product = $item['ProductoID'];

    $consultStock = conexion();
    $consultStock = consulta($consultStock, "SELECT Cantidad as Quantity FROM Productos WHERE ProductoID=:p1", [':p1' => $product]);
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
$emailUser = consulta($emailUser, "SELECT Email FROM Usuarios WHERE UsuarioID = :p1", [':p1' => $idUser]);
$Usermail = $emailUser->fetchColumn();


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    \App\Services\Mailer::configure($mail);

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
