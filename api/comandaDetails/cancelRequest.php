<?php
require_once "../../controllers/mainController.php";
header('Content-Type: application/json');

// Leer input JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['ComandaID'])) {
    echo json_encode(['status' => 'error', 'message' => 'No se recibió una Comanda para eliminar']);
    exit;
}

$comandaID = $input['ComandaID'];
session_name("INV");
session_start();
$idUser = $_SESSION['id'] ?? null;

if (!$idUser) {
    echo json_encode(['status' => 'error', 'message' => 'Usuario no autenticado']);
    exit;
}

try {
    $conexion = conexion();
    $datosStmt = $conexion->prepare("SELECT Cantidad, ProductoID FROM MovimientosInventario WHERE ComandaID = :comandaID");
    $datosStmt->execute([':comandaID' => $comandaID]);
    $datos = $datosStmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($datos as $item) {
        $product = $item['ProductoID'];

        $stockStmt = $conexion->prepare("SELECT Cantidad FROM Productos WHERE ProductoID = :productID");
        $stockStmt->execute([':productID' => $product]);
        $stockBefore = $stockStmt->fetchColumn();

        $newStock = $stockBefore + $item['Cantidad'];

        $updateStmt = $conexion->prepare("UPDATE Productos SET Cantidad = :stock WHERE ProductoID = :productoID");
        $updateStmt->execute([
            ':stock' => $newStock,
            ':productoID' => $product
        ]);
    }

    $deleteComanda = $conexion->prepare("UPDATE MovimientosInventario SET Status = 'Cancelado' WHERE ComandaID = :id");
    $deleteComanda->execute([':id' => $comandaID]);

    $emailStmt = $conexion->prepare("SELECT Email FROM Usuarios WHERE UsuarioID = :idUser");
    $emailStmt->execute([':idUser' => $idUser]);
    $Usermail = $emailStmt->fetchColumn();

    require_once '../../PHPMailer/src/PHPMailer.php';
    require_once '../../PHPMailer/src/SMTP.php';
    require_once '../../PHPMailer/src/Exception.php';

    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

    $mail->isSMTP();
    $mail->Debugoutput = 'html';
    $mail->Host = 'smtp.titan.email';
    $mail->SMTPAuth = true;
    $mail->Username = 'info@kallijaguar-inventory.com';
    $mail->Password = '{&<eXA[x$?_q\<N';
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;

    $mail->setFrom('info@kallijaguar-inventory.com', 'Informacion Kalli Jaguar');
    $mail->addAddress('andrea.sanchez@kallijaguar-inventory.com');
    $mail->addCC('julieta.ramirez@kallijaguar-inventory.com');
    $mail->addCC('miguel.loaeza@kallijaguar-inventory.com');
    $mail->addCC('may.sanchez@kallijaguar-inventory.com');
    $mail->addCC('cencarnacion@kallijaguar-inventory.com');
    $mail->addCC('claudia.espinoza@kallijaguar-inventory.com');

    $mail->isHTML(true);
    $mail->Subject = 'Comanda Cancelada: ' . $comandaID;
    $mail->Body = "<p>Se ha generado una cancelacion de la siguiente comanda: <strong>{$comandaID}</strong></p>
    <p>La solicitud realizada sera eliminada del Sistema y el stock reservado pasara a ser disponible nuevamente.</p>
    <p>Saludos.</p>";
    $mail->send();

    echo json_encode(['status' => 'success', 'message' => 'Comanda cancelada correctamente']);
} catch (\Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error al cancelar: ' . $e->getMessage()]);
}
exit();