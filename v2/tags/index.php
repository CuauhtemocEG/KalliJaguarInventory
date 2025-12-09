<?php
/**
 * API REST para Gestión de Tags
 * Endpoint: /api/tags/index.php
 * Métodos: GET, POST, PUT, DELETE
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Manejar preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../../config.php';
require_once '../../controllers/mainController.php';

// Funciones auxiliares
function sendResponse($success, $message, $data = null, $code = 200) {
    http_response_code($code);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

function sendError($message, $code = 400) {
    sendResponse(false, $message, null, $code);
}

try {
    $pdo = conexion();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    sendError('Error de conexión a la base de datos', 500);
}

// Enrutamiento según método HTTP
switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        handleGet($pdo);
        break;
    
    case 'POST':
        handlePost($pdo);
        break;
    
    case 'PUT':
        handlePut($pdo);
        break;
    
    case 'DELETE':
        handleDelete($pdo);
        break;
    
    default:
        sendError('Método no permitido', 405);
}

/**
 * GET - Obtener tags
 */
function handleGet($pdo) {
    $tagId = isset($_GET['id']) ? intval($_GET['id']) : null;
    $includeInactive = isset($_GET['includeInactive']) && $_GET['includeInactive'] === 'true';
    $withProductCount = isset($_GET['withProductCount']) && $_GET['withProductCount'] === 'true';
    
    try {
        if ($tagId) {
            // Obtener un tag específico
            $sql = "
                SELECT 
                    t.*,
                    u.nombre as CreadoPorNombre,
                    COUNT(DISTINCT pt.ProductoID) as ProductosAsociados
                FROM Tags t
                LEFT JOIN Usuarios u ON t.CreadoPor = u.UsuarioID
                LEFT JOIN ProductoTags pt ON t.TagID = pt.TagID
                WHERE t.TagID = :tagId
                GROUP BY t.TagID
            ";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['tagId' => $tagId]);
            $tag = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$tag) {
                sendError('Tag no encontrado', 404);
            }
            
            // Obtener productos asociados
            $sqlProductos = "
                SELECT 
                    p.ProductoID,
                    p.Nombre,
                    p.UPC,
                    p.Tipo,
                    p.image
                FROM ProductoTags pt
                INNER JOIN Productos p ON pt.ProductoID = p.ProductoID
                WHERE pt.TagID = :tagId AND p.Activo = 1
                ORDER BY p.Nombre
            ";
            
            $stmtProductos = $pdo->prepare($sqlProductos);
            $stmtProductos->execute(['tagId' => $tagId]);
            $tag['productos'] = $stmtProductos->fetchAll(PDO::FETCH_ASSOC);
            
            sendResponse(true, 'Tag obtenido correctamente', $tag);
            
        } else {
            // Obtener todos los tags
            $sql = "
                SELECT 
                    t.*,
                    u.nombre as CreadoPorNombre
            ";
            
            if ($withProductCount) {
                $sql .= ", COUNT(DISTINCT pt.ProductoID) as ProductosAsociados";
            }
            
            $sql .= "
                FROM Tags t
                LEFT JOIN Usuarios u ON t.CreadoPor = u.UsuarioID
            ";
            
            if ($withProductCount) {
                $sql .= " LEFT JOIN ProductoTags pt ON t.TagID = pt.TagID";
            }
            
            if (!$includeInactive) {
                $sql .= " WHERE t.Activo = 1";
            }
            
            if ($withProductCount) {
                $sql .= " GROUP BY t.TagID";
            }
            
            $sql .= " ORDER BY t.Nombre ASC";
            
            $stmt = $pdo->query($sql);
            $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            sendResponse(true, 'Tags obtenidos correctamente', [
                'tags' => $tags,
                'total' => count($tags)
            ]);
        }
        
    } catch (PDOException $e) {
        sendError('Error al obtener tags: ' . $e->getMessage(), 500);
    }
}

/**
 * POST - Crear nuevo tag
 */
function handlePost($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validaciones
    if (empty($input['Nombre'])) {
        sendError('El nombre del tag es requerido');
    }
    
    $nombre = trim($input['Nombre']);
    $color = isset($input['Color']) ? $input['Color'] : '#6366f1';
    $descripcion = isset($input['Descripcion']) ? trim($input['Descripcion']) : null;
    $icono = isset($input['Icono']) ? $input['Icono'] : 'fa-tag';
    $creadoPor = isset($input['CreadoPor']) ? intval($input['CreadoPor']) : 1;
    
    // Validar que no exista ya
    $checkStmt = $pdo->prepare("SELECT TagID FROM Tags WHERE LOWER(Nombre) = LOWER(:nombre)");
    $checkStmt->execute(['nombre' => $nombre]);
    
    if ($checkStmt->fetch()) {
        sendError('Ya existe un tag con ese nombre');
    }
    
    try {
        $sql = "
            INSERT INTO Tags (Nombre, Color, Descripcion, Icono, CreadoPor, FechaCreacion, FechaModificacion, Activo)
            VALUES (:nombre, :color, :descripcion, :icono, :creadoPor, NOW(), NOW(), 1)
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'nombre' => $nombre,
            'color' => $color,
            'descripcion' => $descripcion,
            'icono' => $icono,
            'creadoPor' => $creadoPor
        ]);
        
        $tagId = $pdo->lastInsertId();
        
        sendResponse(true, 'Tag creado exitosamente', [
            'TagID' => $tagId,
            'Nombre' => $nombre
        ], 201);
        
    } catch (PDOException $e) {
        sendError('Error al crear tag: ' . $e->getMessage(), 500);
    }
}

/**
 * PUT - Actualizar tag
 */
function handlePut($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['TagID'])) {
        sendError('El ID del tag es requerido');
    }
    
    if (empty($input['Nombre'])) {
        sendError('El nombre del tag es requerido');
    }
    
    $tagId = intval($input['TagID']);
    $nombre = trim($input['Nombre']);
    $color = isset($input['Color']) ? $input['Color'] : '#6366f1';
    $descripcion = isset($input['Descripcion']) ? trim($input['Descripcion']) : null;
    $icono = isset($input['Icono']) ? $input['Icono'] : 'fa-tag';
    
    // Verificar que el tag existe
    $checkStmt = $pdo->prepare("SELECT TagID FROM Tags WHERE TagID = :tagId");
    $checkStmt->execute(['tagId' => $tagId]);
    
    if (!$checkStmt->fetch()) {
        sendError('Tag no encontrado', 404);
    }
    
    // Verificar que no exista otro tag con el mismo nombre
    $checkNameStmt = $pdo->prepare("SELECT TagID FROM Tags WHERE LOWER(Nombre) = LOWER(:nombre) AND TagID != :tagId");
    $checkNameStmt->execute(['nombre' => $nombre, 'tagId' => $tagId]);
    
    if ($checkNameStmt->fetch()) {
        sendError('Ya existe otro tag con ese nombre');
    }
    
    try {
        $sql = "
            UPDATE Tags 
            SET Nombre = :nombre,
                Color = :color,
                Descripcion = :descripcion,
                Icono = :icono,
                FechaModificacion = NOW()
            WHERE TagID = :tagId
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'nombre' => $nombre,
            'color' => $color,
            'descripcion' => $descripcion,
            'icono' => $icono,
            'tagId' => $tagId
        ]);
        
        sendResponse(true, 'Tag actualizado exitosamente', [
            'TagID' => $tagId,
            'Nombre' => $nombre
        ]);
        
    } catch (PDOException $e) {
        sendError('Error al actualizar tag: ' . $e->getMessage(), 500);
    }
}

/**
 * DELETE - Eliminar (desactivar) tag
 */
function handleDelete($pdo) {
    $tagId = isset($_GET['id']) ? intval($_GET['id']) : null;
    
    if (!$tagId) {
        sendError('El ID del tag es requerido');
    }
    
    // Verificar que el tag existe
    $checkStmt = $pdo->prepare("SELECT Nombre, (SELECT COUNT(*) FROM ProductoTags WHERE TagID = :tagId) as ProductosAsociados FROM Tags WHERE TagID = :tagId");
    $checkStmt->execute(['tagId' => $tagId]);
    $tag = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$tag) {
        sendError('Tag no encontrado', 404);
    }
    
    try {
        // Solo desactivar, no eliminar (soft delete)
        $sql = "UPDATE Tags SET Activo = 0, FechaModificacion = NOW() WHERE TagID = :tagId";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['tagId' => $tagId]);
        
        $mensaje = $tag['ProductosAsociados'] > 0 
            ? "Tag '{$tag['Nombre']}' desactivado. Las relaciones con {$tag['ProductosAsociados']} producto(s) se mantienen."
            : "Tag '{$tag['Nombre']}' eliminado exitosamente.";
        
        sendResponse(true, $mensaje, [
            'TagID' => $tagId,
            'ProductosAfectados' => $tag['ProductosAsociados']
        ]);
        
    } catch (PDOException $e) {
        sendError('Error al eliminar tag: ' . $e->getMessage(), 500);
    }
}
