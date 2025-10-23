<?php
header('Content-Type: application/json');
session_start();

require_once('../../controllers/mainController.php');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$page = (int)($_GET['page'] ?? 1);
$limit = (int)($_GET['limit'] ?? 10);
$search = trim($_GET['search'] ?? '');
$estado = $_GET['estado'] ?? '';
$tipo = $_GET['tipo'] ?? '';
$sort_by = $_GET['sort_by'] ?? 'nombre';
$sort_order = $_GET['sort_order'] ?? 'ASC';

$offset = ($page - 1) * $limit;

$valid_sort_fields = ['nombre', 'direccion', 'estado', 'tipo', 'fecha_creacion'];
if (!in_array($sort_by, $valid_sort_fields)) {
    $sort_by = 'nombre';
}

$sort_order = strtoupper($sort_order) === 'DESC' ? 'DESC' : 'ASC';

try {
    $pdo = conexion();
    
    $where_conditions = [];
    $params = [];
    
    if (!empty($search)) {
        $where_conditions[] = "(s.nombre LIKE :search OR s.direccion LIKE :search OR s.gerente LIKE :search)";
        $params[':search'] = "%{$search}%";
    }
    
    if (!empty($estado)) {
        $where_conditions[] = "s.estado = :estado";
        $params[':estado'] = $estado;
    }
    
    if (!empty($tipo)) {
        $where_conditions[] = "s.tipo = :tipo";
        $params[':tipo'] = $tipo;
    }
    
    $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    $query = "
        SELECT 
            s.*,
            u.Nombre as creado_por_nombre,
            DATE_FORMAT(s.fecha_creacion, '%d/%m/%Y %H:%i') as fecha_creacion_formatted,
            DATE_FORMAT(s.fecha_actualizacion, '%d/%m/%Y %H:%i') as fecha_actualizacion_formatted,
            CASE 
                WHEN s.estado = 'activa' THEN 'Activa'
                WHEN s.estado = 'inactiva' THEN 'Inactiva'
                WHEN s.estado = 'mantenimiento' THEN 'Mantenimiento'
                ELSE s.estado
            END as estado_texto,
            CASE 
                WHEN s.tipo = 'principal' THEN 'Principal'
                WHEN s.tipo = 'sucursal' THEN 'Sucursal'
                WHEN s.tipo = 'almacen' THEN 'Almacén'
                WHEN s.tipo = 'punto_venta' THEN 'Punto de Venta'
                ELSE s.tipo
            END as tipo_texto
        FROM Sucursales s
        LEFT JOIN Usuarios u ON s.creado_por = u.UsuarioID
        {$where_clause}
        ORDER BY s.{$sort_by} {$sort_order}
        LIMIT {$limit} OFFSET {$offset}
    ";
    
    $stmt = $pdo->prepare($query);
    foreach ($params as $param => $value) {
        $stmt->bindValue($param, $value);
    }
    $stmt->execute();
    $sucursales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $count_query = "
        SELECT COUNT(*) as total
        FROM Sucursales s
        {$where_clause}
    ";
    
    $count_stmt = $pdo->prepare($count_query);
    foreach ($params as $param => $value) {
        $count_stmt->bindValue($param, $value);
    }
    $count_stmt->execute();
    $total_records = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $total_pages = ceil($total_records / $limit);
    
    echo json_encode([
        'success' => true,
        'data' => $sucursales,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $total_pages,
            'total_records' => $total_records,
            'limit' => $limit,
            'has_next' => $page < $total_pages,
            'has_prev' => $page > 1
        ],
        'filters' => [
            'search' => $search,
            'estado' => $estado,
            'tipo' => $tipo,
            'sort_by' => $sort_by,
            'sort_order' => $sort_order
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Error fetching sucursales: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al obtener las sucursales']);
}
?>
