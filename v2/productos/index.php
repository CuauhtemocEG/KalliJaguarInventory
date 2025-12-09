<?php
/**
 * API REST para Gestión de Productos
 * Endpoint: /api/productos/index.php
 * Métodos: GET, POST, PUT, DELETE
 * Características: Múltiples tags, imágenes, código de barras, filtros avanzados
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
    $productoId = isset($_GET['id']) ? intval($_GET['id']) : null;
    $categoriaId = isset($_GET['categoria']) ? intval($_GET['categoria']) : null;
    $tagId = isset($_GET['tag']) ? intval($_GET['tag']) : null;
    $tipo = isset($_GET['tipo']) ? $_GET['tipo'] : null;
    $search = isset($_GET['search']) ? trim($_GET['search']) : null;
    $stockBajo = isset($_GET['stockBajo']) && $_GET['stockBajo'] === 'true';
    $includeInactive = isset($_GET['includeInactive']) && $_GET['includeInactive'] === 'true';
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 1000;
    $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
    
    try {
        if ($productoId) {
            // Obtener producto específico con tags
            $sql = "
                SELECT 
                    p.*,
                    c.Nombre as CategoriaNombre,
                    u.nombre as UsuarioNombre
                FROM Productos p
                LEFT JOIN Categorias c ON p.CategoriaID = c.CategoriaID
                LEFT JOIN Usuarios u ON p.UsuarioID = u.UsuarioID
                WHERE p.ProductoID = :productoId
            ";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['productoId' => $productoId]);
            $producto = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$producto) {
                sendError('Producto no encontrado', 404);
            }
            
            // Obtener tags del producto
            $sqlTags = "
                SELECT 
                    t.TagID,
                    t.Nombre,
                    t.Color,
                    t.Icono
                FROM ProductoTags pt
                INNER JOIN Tags t ON pt.TagID = t.TagID
                WHERE pt.ProductoID = :productoId AND t.Activo = 1
                ORDER BY t.Nombre
            ";
            
            $stmtTags = $pdo->prepare($sqlTags);
            $stmtTags->execute(['productoId' => $productoId]);
            $producto['tags'] = $stmtTags->fetchAll(PDO::FETCH_ASSOC);
            
            // Calcular estado de stock
            $producto['stockBajo'] = $producto['Cantidad'] <= $producto['StockMinimo'];
            $producto['stockStatus'] = $producto['Cantidad'] == 0 ? 'sin_stock' : 
                                       ($producto['stockBajo'] ? 'bajo' : 'normal');
            
            sendResponse(true, 'Producto obtenido correctamente', $producto);
            
        } else {
            // Construir query con filtros
            $sql = "
                SELECT DISTINCT
                    p.*,
                    c.Nombre as CategoriaNombre
                FROM Productos p
                LEFT JOIN Categorias c ON p.CategoriaID = c.CategoriaID
                LEFT JOIN ProductoTags pt ON p.ProductoID = pt.ProductoID
                LEFT JOIN Tags t ON pt.TagID = t.TagID
                WHERE 1=1
            ";
            
            $params = [];
            
            if (!$includeInactive) {
                $sql .= " AND p.Activo = 1";
            }
            
            if ($categoriaId) {
                $sql .= " AND p.CategoriaID = :categoriaId";
                $params['categoriaId'] = $categoriaId;
            }
            
            if ($tagId) {
                $sql .= " AND pt.TagID = :tagId";
                $params['tagId'] = $tagId;
            }
            
            if ($tipo) {
                $sql .= " AND p.Tipo = :tipo";
                $params['tipo'] = $tipo;
            }
            
            if ($search) {
                $sql .= " AND (p.Nombre LIKE :search OR p.UPC LIKE :search OR p.SKU LIKE :search OR p.Descripcion LIKE :search)";
                $params['search'] = "%{$search}%";
            }
            
            if ($stockBajo) {
                $sql .= " AND p.Cantidad <= p.StockMinimo";
            }
            
            $sql .= " ORDER BY p.Nombre ASC LIMIT :limit OFFSET :offset";
            
            $stmt = $pdo->prepare($sql);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue(":{$key}", $value);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            
            $stmt->execute();
            $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Obtener tags para cada producto
            foreach ($productos as &$producto) {
                $stmtTags = $pdo->prepare("
                    SELECT 
                        t.TagID,
                        t.Nombre,
                        t.Color,
                        t.Icono
                    FROM ProductoTags pt
                    INNER JOIN Tags t ON pt.TagID = t.TagID
                    WHERE pt.ProductoID = :productoId AND t.Activo = 1
                ");
                $stmtTags->execute(['productoId' => $producto['ProductoID']]);
                $producto['tags'] = $stmtTags->fetchAll(PDO::FETCH_ASSOC);
                
                $producto['stockBajo'] = $producto['Cantidad'] <= $producto['StockMinimo'];
                $producto['stockStatus'] = $producto['Cantidad'] == 0 ? 'sin_stock' : 
                                           ($producto['stockBajo'] ? 'bajo' : 'normal');
            }
            
            // Contar total
            $sqlCount = "SELECT COUNT(DISTINCT p.ProductoID) as total FROM Productos p";
            if ($tagId || $categoriaId || $tipo || $search || $stockBajo || !$includeInactive) {
                $sqlCount .= " LEFT JOIN ProductoTags pt ON p.ProductoID = pt.ProductoID WHERE 1=1";
                if (!$includeInactive) $sqlCount .= " AND p.Activo = 1";
                if ($categoriaId) $sqlCount .= " AND p.CategoriaID = :categoriaId";
                if ($tagId) $sqlCount .= " AND pt.TagID = :tagId";
                if ($tipo) $sqlCount .= " AND p.Tipo = :tipo";
                if ($stockBajo) $sqlCount .= " AND p.Cantidad <= p.StockMinimo";
                if ($search) $sqlCount .= " AND (p.Nombre LIKE :search OR p.UPC LIKE :search OR p.SKU LIKE :search)";
            }
            
            $stmtCount = $pdo->prepare($sqlCount);
            foreach ($params as $key => $value) {
                if ($key !== 'limit' && $key !== 'offset') {
                    $stmtCount->bindValue(":{$key}", $value);
                }
            }
            $stmtCount->execute();
            $total = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Calcular estadísticas de stock
            $sqlStats = "
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN Cantidad > StockMinimo THEN 1 ELSE 0 END) as normal,
                    SUM(CASE WHEN Cantidad > 0 AND Cantidad <= StockMinimo THEN 1 ELSE 0 END) as bajo,
                    SUM(CASE WHEN Cantidad = 0 THEN 1 ELSE 0 END) as sin_stock
                FROM Productos
                WHERE Activo = 1
            ";
            $stmtStats = $pdo->query($sqlStats);
            $stats = $stmtStats->fetch(PDO::FETCH_ASSOC);
            
            sendResponse(true, 'Productos obtenidos correctamente', [
                'productos' => $productos,
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset,
                'stats' => $stats
            ]);
        }
        
    } catch (PDOException $e) {
        sendError('Error al obtener productos: ' . $e->getMessage(), 500);
    }
}

function handlePost($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validaciones
    if (empty($input['Nombre'])) {
        sendError('El nombre del producto es requerido');
    }
    if (empty($input['Tipo']) || !in_array($input['Tipo'], ['Unidad', 'Pesable'])) {
        sendError('El tipo debe ser Unidad o Pesable');
    }
    if (!isset($input['PrecioUnitario']) || $input['PrecioUnitario'] < 0) {
        sendError('El precio unitario es requerido y debe ser mayor a 0');
    }
    
    try {
        $pdo->beginTransaction();
        
        // Insertar producto
        $sql = "
            INSERT INTO Productos (
                UPC, Nombre, Descripcion, Cantidad, Tipo, PrecioUnitario,
                CategoriaID, UsuarioID, image, StockMinimo, SKU, Proveedor, Activo
            ) VALUES (
                :upc, :nombre, :descripcion, :cantidad, :tipo, :precio,
                :categoria, :usuario, :image, :stockMin, :sku, :proveedor, 1
            )
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'upc' => $input['UPC'] ?? null,
            'nombre' => trim($input['Nombre']),
            'descripcion' => trim($input['Descripcion'] ?? ''),
            'cantidad' => $input['Cantidad'] ?? 0,
            'tipo' => $input['Tipo'],
            'precio' => $input['PrecioUnitario'],
            'categoria' => $input['CategoriaID'] ?? null,
            'usuario' => $input['UsuarioID'] ?? 1,
            'image' => $input['image'] ?? null,
            'stockMin' => $input['StockMinimo'] ?? 5,
            'sku' => $input['SKU'] ?? null,
            'proveedor' => $input['Proveedor'] ?? null
        ]);
        
        $productoId = $pdo->lastInsertId();
        
        // Asignar tags si existen
        if (isset($input['tags']) && is_array($input['tags'])) {
            foreach ($input['tags'] as $tagId) {
                $stmt = $pdo->prepare("INSERT INTO ProductoTags (ProductoID, TagID, AsignadoPor) VALUES (:productoId, :tagId, :usuario)");
                $stmt->execute([
                    'productoId' => $productoId,
                    'tagId' => $tagId,
                    'usuario' => $input['UsuarioID'] ?? 1
                ]);
            }
        }
        
        $pdo->commit();
        
        // Obtener producto creado
        $stmt = $pdo->prepare("SELECT * FROM Productos WHERE ProductoID = :productoId");
        $stmt->execute(['productoId' => $productoId]);
        $newProducto = $stmt->fetch(PDO::FETCH_ASSOC);
        
        sendResponse(true, 'Producto creado exitosamente', $newProducto, 201);
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        sendError('Error al crear producto: ' . $e->getMessage(), 500);
    }
}

function handlePut($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['ProductoID'])) {
        sendError('ID del producto es requerido');
    }
    
    $productoId = intval($input['ProductoID']);
    
    try {
        $pdo->beginTransaction();
        
        // Verificar que existe
        $stmt = $pdo->prepare("SELECT ProductoID FROM Productos WHERE ProductoID = :productoId");
        $stmt->execute(['productoId' => $productoId]);
        
        if (!$stmt->fetch()) {
            $pdo->rollBack();
            sendError('Producto no encontrado', 404);
        }
        
        // Construir query dinámica
        $updates = [];
        $params = ['productoId' => $productoId];
        
        $campos = ['UPC', 'Nombre', 'Descripcion', 'Cantidad', 'Tipo', 'PrecioUnitario', 
                   'CategoriaID', 'image', 'StockMinimo', 'SKU', 'Proveedor', 'Activo'];
        
        foreach ($campos as $campo) {
            if (isset($input[$campo])) {
                $updates[] = "{$campo} = :{$campo}";
                $params[$campo] = $input[$campo];
            }
        }
        
        if (!empty($updates)) {
            $sql = "UPDATE Productos SET " . implode(', ', $updates) . " WHERE ProductoID = :productoId";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        }
        
        // Actualizar tags si se proporcionaron
        if (isset($input['tags']) && is_array($input['tags'])) {
            // Eliminar tags actuales
            $stmt = $pdo->prepare("DELETE FROM ProductoTags WHERE ProductoID = :productoId");
            $stmt->execute(['productoId' => $productoId]);
            
            // Insertar nuevos tags
            foreach ($input['tags'] as $tagId) {
                $stmt = $pdo->prepare("INSERT INTO ProductoTags (ProductoID, TagID, AsignadoPor) VALUES (:productoId, :tagId, :usuario)");
                $stmt->execute([
                    'productoId' => $productoId,
                    'tagId' => $tagId,
                    'usuario' => $input['UsuarioID'] ?? 1
                ]);
            }
        }
        
        $pdo->commit();
        
        // Obtener producto actualizado
        $stmt = $pdo->prepare("SELECT * FROM Productos WHERE ProductoID = :productoId");
        $stmt->execute(['productoId' => $productoId]);
        $updatedProducto = $stmt->fetch(PDO::FETCH_ASSOC);
        
        sendResponse(true, 'Producto actualizado exitosamente', $updatedProducto);
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        sendError('Error al actualizar producto: ' . $e->getMessage(), 500);
    }
}

function handleDelete($pdo) {
    $productoId = isset($_GET['id']) ? intval($_GET['id']) : null;
    $forceDelete = isset($_GET['force']) && $_GET['force'] === 'true';
    
    if (!$productoId) {
        sendError('ID del producto es requerido');
    }
    
    try {
        $stmt = $pdo->prepare("SELECT ProductoID FROM Productos WHERE ProductoID = :productoId");
        $stmt->execute(['productoId' => $productoId]);
        
        if (!$stmt->fetch()) {
            sendError('Producto no encontrado', 404);
        }
        
        if ($forceDelete) {
            // Eliminación física
            $pdo->beginTransaction();
            
            // Eliminar relaciones con tags
            $stmt = $pdo->prepare("DELETE FROM ProductoTags WHERE ProductoID = :productoId");
            $stmt->execute(['productoId' => $productoId]);
            
            // Eliminar producto
            $stmt = $pdo->prepare("DELETE FROM Productos WHERE ProductoID = :productoId");
            $stmt->execute(['productoId' => $productoId]);
            
            $pdo->commit();
            
            sendResponse(true, 'Producto eliminado completamente');
        } else {
            // Eliminación lógica
            $stmt = $pdo->prepare("UPDATE Productos SET Activo = 0 WHERE ProductoID = :productoId");
            $stmt->execute(['productoId' => $productoId]);
            
            sendResponse(true, 'Producto desactivado (eliminación lógica)');
        }
        
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        sendError('Error al eliminar producto: ' . $e->getMessage(), 500);
    }
}
?>
