<?php
session_start();
header('Content-Type: application/json');
require('../../fpdf/fpdf.php');
require_once "../../controllers/mainController.php";
require '../../PHPMailer/src/PHPMailer.php';
require '../../PHPMailer/src/SMTP.php';
require '../../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Leer JSON del body
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['idSucursal'], $input['id'], $input['fecha'])) {
  echo json_encode(['status' => 'error', 'message' => 'Datos de sesión no válidos.']);
  exit();
}

$idUser = $input['id'];
$sucursal_id = $input['idSucursal'];
$fechaDelivery = $input['fecha'];
$fechaObj = DateTime::createFromFormat('d/m/Y', $fechaDelivery);
if (!$fechaObj) {
  echo json_encode(['status' => 'error', 'message' => 'Formato de fecha inválido.']);
  exit();
}
$fechaMysql = $fechaObj->format('Y-m-d');
$fecha = date('Ymd');
$random_number = rand(100, 999);
$comandaID = 'COM-' . $fecha . '-' . $sucursal_id . '-' . $random_number;

// Obtener productos del carrito de la base de datos
$conn = conexion();
$stmt = $conn->prepare("SELECT * FROM CarritoSolicitudes WHERE UsuarioID = ?");
$stmt->execute([$idUser]);
$carrito = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$carrito || count($carrito) === 0) {
  echo json_encode(['status' => 'error', 'message' => 'No hay productos en el carrito.']);
  exit();
}