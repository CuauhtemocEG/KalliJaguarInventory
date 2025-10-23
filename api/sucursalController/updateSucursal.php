<?php
header('Content-Type: application/json');
session_start();

require_once('../../controllers/mainController.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$sucursal_id = (int)($_POST['sucursal_id'] ?? 0);
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

if (!$sucursal_id) {
    echo json_encode(['success' => false, 'message' => 'ID de sucursal requerido']);
    exit;
}

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
    
    $check_query = "SELECT SucursalID, nombre FROM Sucursales WHERE SucursalID = :sucursal_id";
    $check_stmt = $pdo->prepare($check_query);
    $check_stmt->bindParam(':sucursal_id', $sucursal_id, PDO::PARAM_INT);
    $check_stmt->execute();
    $sucursal_actual = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$sucursal_actual) {
        echo json_encode(['success' => false, 'message' => 'Sucursal no encontrada']);
        exit;
    }
    
    $check_name_query = "SELECT SucursalID FROM Sucursales WHERE nombre = :nombre AND SucursalID != :sucursal_id";
    $check_name_stmt = $pdo->prepare($check_name_query);
    $check_name_stmt->bindParam(':nombre', $nombre);
    $check_name_stmt->bindParam(':sucursal_id', $sucursal_id, PDO::PARAM_INT);
    $check_name_stmt->execute();
    
    if ($check_name_stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Ya existe otra sucursal con este nombre']);
        exit;
    }
    
    $update_query = "
        UPDATE Sucursales SET 
            nombre = :nombre,
            direccion = :direccion,
            telefono = :telefono,
            email = :email,
            gerente = :gerente,
            estado = :estado,
            tipo = :tipo,
            horario_apertura = :horario_apertura,
            horario_cierre = :horario_cierre,
            notas = :notas,
            fecha_actualizacion = NOW(),
            actualizado_por = :actualizado_por
        WHERE SucursalID = :sucursal_id
    ";
    
    $actualizado_por = $_SESSION['id'] ?? null;
    
    $update_stmt = $pdo->prepare($update_query);
    $update_stmt->bindParam(':nombre', $nombre);
    $update_stmt->bindParam(':direccion', $direccion);
    $update_stmt->bindParam(':telefono', $telefono);
    $update_stmt->bindParam(':email', $email);
    $update_stmt->bindParam(':gerente', $gerente);
    $update_stmt->bindParam(':estado', $estado);
    $update_stmt->bindParam(':tipo', $tipo);
    $update_stmt->bindParam(':horario_apertura', $horario_apertura);
    $update_stmt->bindParam(':horario_cierre', $horario_cierre);
    $update_stmt->bindParam(':notas', $notas);
    $update_stmt->bindParam(':actualizado_por', $actualizado_por, PDO::PARAM_INT);
    $update_stmt->bindParam(':sucursal_id', $sucursal_id, PDO::PARAM_INT);
    
    if ($update_stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Sucursal actualizada exitosamente',
            'sucursal_id' => $sucursal_id,
            'nombre' => $nombre,
            'nombre_anterior' => $sucursal_actual['nombre']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar la sucursal']);
    }
    
} catch (PDOException $e) {
    error_log("Error updating sucursal: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error de base de datos']);
}
?>
