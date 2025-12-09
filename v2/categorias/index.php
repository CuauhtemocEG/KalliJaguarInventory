<?php
/**
 * API REST para Gestión de Categorías
 * Endpoint: /api/categorias/index.php
 * Métodos: GET, POST, PUT, DELETE
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../../config.php';
require_once '../../controllers/mainController.php';

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

function handleGet($pdo) {
    $categoriaId = isset($_GET['id']) ? intval($_GET['id']) : null;
    $withProductCount = isset($_GET['withProductCount']) && $_GET['withProductCount'] === 'true';
    
    try {
        if ($categoriaId) {
            $sql = "
                SELECT 
                    c.*,
                    COUNT(DISTINCT p.ProductoID) as ProductosAsociados,
                    SUM(CASE WHEN p.Activo = 1 THEN 1 ELSE 0 END) as ProductosActivos
                FROM Categorias c
                LEFT JOIN Productos p ON c.CategoriaID = p.CategoriaID
                WHERE c.CategoriaID = :categoriaId
                GROUP BY c.CategoriaID
            ";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['categoriaId' => $categoriaId]);
            $categoria = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$categoria) {
                sendError('Categoría no encontrada', 404);
            }
            
            // Obtener productos de la categoría
            $sqlProductos = "
                SELECT 
                    p.ProductoID,
                    p.Nombre,
                    p.UPC,
                    p.Tipo,
                    p.Cantidad,
                    p.PrecioUnitario,
                    p.image,
                    p.Activo
                FROM Productos p
                WHERE p.CategoriaID = :categoriaId
                ORDER BY p.Nombre
            ";
            
            $stmtProductos = $pdo->prepare($sqlProductos);
            $stmtProductos->execute(['categoriaId' => $categoriaId]);
            $categoria['productos'] = $stmtProductos->fetchAll(PDO::FETCH_ASSOC);
            
            sendResponse(true, 'Categoría obtenida correctamente', $categoria);
            
        } else {
            $sql = "SELECT c.CategoriaID, c.Nombre";
            
            if ($withProductCount) {
                $sql .= ", COUNT(DISTINCT p.ProductoID) as ProductosAsociados";
            }
            
            $sql .= " FROM Categorias c";
            
            if ($withProductCount) {
                $sql .= " LEFT JOIN Productos p ON c.CategoriaID = p.CategoriaID";
            }
            
            if ($withProductCount) {
                $sql .= " GROUP BY c.CategoriaID";
            }
            
            $sql .= " ORDER BY c.Nombre ASC";
            
            $stmt = $pdo->query($sql);
            $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            sendResponse(true, 'Categorías obtenidas correctamente', [
                'categorias' => $categorias,
                'total' => count($categorias)
            ]);
        }
        
    } catch (PDOException $e) {
        sendError('Error al obtener categorías: ' . $e->getMessage(), 500);
    }
}

function handlePost($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['Nombre'])) {
        sendError('El nombre de la categoría es requerido');
    }
    
    $nombre = trim($input['Nombre']);
    
    try {
        // Verificar que no exista
        $stmt = $pdo->prepare("SELECT CategoriaID FROM Categorias WHERE Nombre = :nombre");
        $stmt->execute(['nombre' => $nombre]);
        
        if ($stmt->fetch()) {
            sendError('Ya existe una categoría con ese nombre', 409);
        }
        
        // Insertar
        $stmt = $pdo->prepare("INSERT INTO Categorias (Nombre) VALUES (:nombre)");
        $stmt->execute(['nombre' => $nombre]);
        
        $categoriaId = $pdo->lastInsertId();
        
        $stmt = $pdo->prepare("SELECT * FROM Categorias WHERE CategoriaID = :categoriaId");
        $stmt->execute(['categoriaId' => $categoriaId]);
        $newCategoria = $stmt->fetch(PDO::FETCH_ASSOC);
        
        sendResponse(true, 'Categoría creada exitosamente', $newCategoria, 201);
        
    } catch (PDOException $e) {
        sendError('Error al crear categoría: ' . $e->getMessage(), 500);
    }
}

function handlePut($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['CategoriaID'])) {
        sendError('ID de la categoría es requerido');
    }
    
    if (empty($input['Nombre'])) {
        sendError('El nombre de la categoría es requerido');
    }
    
    $categoriaId = intval($input['CategoriaID']);
    $nombre = trim($input['Nombre']);
    
    try {
        // Verificar que existe
        $stmt = $pdo->prepare("SELECT CategoriaID FROM Categorias WHERE CategoriaID = :categoriaId");
        $stmt->execute(['categoriaId' => $categoriaId]);
        
        if (!$stmt->fetch()) {
            sendError('Categoría no encontrada', 404);
        }
        
        // Verificar nombre duplicado (excepto la misma categoría)
        $stmt = $pdo->prepare("SELECT CategoriaID FROM Categorias WHERE Nombre = :nombre AND CategoriaID != :categoriaId");
        $stmt->execute(['nombre' => $nombre, 'categoriaId' => $categoriaId]);
        
        if ($stmt->fetch()) {
            sendError('Ya existe otra categoría con ese nombre', 409);
        }
        
        // Actualizar
        $stmt = $pdo->prepare("UPDATE Categorias SET Nombre = :nombre WHERE CategoriaID = :categoriaId");
        $stmt->execute(['nombre' => $nombre, 'categoriaId' => $categoriaId]);
        
        $stmt = $pdo->prepare("SELECT * FROM Categorias WHERE CategoriaID = :categoriaId");
        $stmt->execute(['categoriaId' => $categoriaId]);
        $updatedCategoria = $stmt->fetch(PDO::FETCH_ASSOC);
        
        sendResponse(true, 'Categoría actualizada exitosamente', $updatedCategoria);
        
    } catch (PDOException $e) {
        sendError('Error al actualizar categoría: ' . $e->getMessage(), 500);
    }
}

function handleDelete($pdo) {
    $categoriaId = isset($_GET['id']) ? intval($_GET['id']) : null;
    $forceDelete = isset($_GET['force']) && $_GET['force'] === 'true';
    
    if (!$categoriaId) {
        sendError('ID de la categoría es requerido');
    }
    
    try {
        $stmt = $pdo->prepare("SELECT CategoriaID FROM Categorias WHERE CategoriaID = :categoriaId");
        $stmt->execute(['categoriaId' => $categoriaId]);
        
        if (!$stmt->fetch()) {
            sendError('Categoría no encontrada', 404);
        }
        
        // Verificar productos asociados
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM Productos WHERE CategoriaID = :categoriaId");
        $stmt->execute(['categoriaId' => $categoriaId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $productosAsociados = $result['total'];
        
        if ($productosAsociados > 0 && !$forceDelete) {
            sendError("No se puede eliminar la categoría porque tiene {$productosAsociados} productos asociados. Use force=true para eliminar de todas formas.", 409);
        }
        
        if ($forceDelete && $productosAsociados > 0) {
            // Desvincular productos
            $stmt = $pdo->prepare("UPDATE Productos SET CategoriaID = NULL WHERE CategoriaID = :categoriaId");
            $stmt->execute(['categoriaId' => $categoriaId]);
        }
        
        // Eliminar categoría
        $stmt = $pdo->prepare("DELETE FROM Categorias WHERE CategoriaID = :categoriaId");
        $stmt->execute(['categoriaId' => $categoriaId]);
        
        $mensaje = $forceDelete && $productosAsociados > 0 
            ? "Categoría eliminada ({$productosAsociados} productos desvinculados)"
            : "Categoría eliminada correctamente";
            
        sendResponse(true, $mensaje);
        
    } catch (PDOException $e) {
        sendError('Error al eliminar categoría: ' . $e->getMessage(), 500);
    }
}
?>
