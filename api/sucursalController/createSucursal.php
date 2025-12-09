<?php
header('Content-Type: application/json');
session_name("INV");
session_start();
require_once('../../controllers/mainController.php');

$insert_query = "
        INSERT INTO Sucursales (
            nombre, direccion, telefono, email, gerente, estado, tipo, 
            horario_apertura, horario_cierre, notas, 
            fecha_creacion, fecha_actualizacion, creado_por
        ) VALUES (
            :nombre, :direccion, :telefono, :email, :gerente, :estado, :tipo,
            :horario_apertura, :horario_cierre, :notas,
            NOW(), NOW(), :creado_por
        )";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$nombre = trim($_POST['nombre'] ?? '');
$direccion = trim($_POST['direccion'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$email = trim($_POST['email'] ?? '');
$gerente = trim($_POST['gerente'] ?? '');
$estado = $_POST['estado'] ?? 'activa';
$tipo = $_POST['tipo'] ?? 'principal';
$horario_apertura = $_POST['horario_apertura'] ?? '';
$horario_cierre = $_POST['horario_cierre'] ?? '';
$notas = trim($_POST['notas'] ?? '');

// Validaciones
if (empty($nombre)) {
    echo json_encode(['success' => false, 'message' => 'El nombre de la sucursal es obligatorio']);
    exit;
}

if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Email no válido']);
    exit;
}

$estados_validos = ['activa', 'inactiva', 'mantenimiento'];
if (!in_array($estado, $estados_validos)) {
    echo json_encode(['success' => false, 'message' => 'Estado no válido']);
    exit;
}

$tipos_validos = ['principal', 'sucursal', 'almacen', 'punto_venta'];
if (!in_array($tipo, $tipos_validos)) {
    echo json_encode(['success' => false, 'message' => 'Tipo de sucursal no válido']);
    exit;
}

try {
    $pdo = conexion();
    
    $check_query = "SELECT SucursalID FROM Sucursales WHERE nombre = :nombre";
    $check_stmt = $pdo->prepare($check_query);
    $check_stmt->bindParam(':nombre', $nombre);
    $check_stmt->execute();
    
    if ($check_stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Ya existe una sucursal con este nombre']);
        exit;
    }
    
    $insert_query = "
        INSERT INTO Sucursales (
            nombre, direccion, telefono, email, gerente, estado, tipo, 
            horario_apertura, horario_cierre, notas, 
            fecha_creacion, fecha_actualizacion, creado_por
        ) VALUES (
            :nombre, :direccion, :telefono, :email, :gerente, :estado, :tipo,
            :horario_apertura, :horario_cierre, :notas,
            NOW(), NOW(), :creado_por
        )
    ";
    
    $creado_por = $_SESSION['id'] ?? null;
    
    $insert_stmt = $pdo->prepare($insert_query);
    $insert_stmt->bindParam(':nombre', $nombre);
    $insert_stmt->bindParam(':direccion', $direccion);
    $insert_stmt->bindParam(':telefono', $telefono);
    $insert_stmt->bindParam(':email', $email);
    $insert_stmt->bindParam(':gerente', $gerente);
    $insert_stmt->bindParam(':estado', $estado);
    $insert_stmt->bindParam(':tipo', $tipo);
    $insert_stmt->bindParam(':horario_apertura', $horario_apertura);
    $insert_stmt->bindParam(':horario_cierre', $horario_cierre);
    $insert_stmt->bindParam(':notas', $notas);
    $insert_stmt->bindParam(':creado_por', $creado_por, PDO::PARAM_INT);
    
    if ($insert_stmt->execute()) {
        $sucursal_id = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message' => 'Sucursal creada exitosamente',
            'sucursal_id' => $sucursal_id,
            'nombre' => $nombre
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al crear la sucursal']);
    }
    
} catch (PDOException $e) {
    error_log("Error creating sucursal: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error de base de datos']);
}
?>
