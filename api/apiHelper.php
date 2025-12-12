<?php
function setAPIHeaders() {
    header('Content-Type: application/json; charset=UTF-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
}

function sendSuccess($data = null, $message = 'Operación exitosa', $code = 200) {
    http_response_code($code);
    echo json_encode([
        'success' => true,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit();
}

function sendError($message = 'Error en la operación', $code = 400, $errors = null) {
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'message' => $message,
        'errors' => $errors,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit();
}

function validateMethod($allowedMethods = ['GET']) {
    if (!in_array($_SERVER['REQUEST_METHOD'], $allowedMethods)) {
        sendError('Método HTTP no permitido', 405);
    }
}

function getJSONInput() {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        sendError('JSON inválido: ' . json_last_error_msg(), 400);
    }
    
    return $data;
}

function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)));
}

function validateRequired($data, $requiredFields) {
    $missing = [];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            $missing[] = $field;
        }
    }
    
    if (!empty($missing)) {
        sendError('Campos requeridos faltantes', 400, [
            'missing_fields' => $missing
        ]);
    }
    return true;
}

function getPaginationParams($defaultLimit = 20, $maxLimit = 100) {
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? min(max(1, intval($_GET['limit'])), $maxLimit) : $defaultLimit;
    $offset = ($page - 1) * $limit;
    
    return [
        'page' => $page,
        'limit' => $limit,
        'offset' => $offset
    ];
}

function paginatedResponse($data, $total, $page, $limit) {
    $totalPages = ceil($total / $limit);
    
    return [
        'items' => $data,
        'pagination' => [
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => $totalPages,
            'has_next' => $page < $totalPages,
            'has_prev' => $page > 1
        ]
    ];
}

function logActivity($action, $details = null, $userId = null) {
    try {
        require_once __DIR__ . '/../../controllers/mainController.php';
        $pdo = conexion();
        
        $stmt = $pdo->prepare("
            INSERT INTO admin_logs (action, details, user_id, created_at) 
            VALUES (?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $action,
            json_encode($details, JSON_UNESCAPED_UNICODE),
            $userId
        ]);
    } catch (Exception $e) {
        error_log("Error logging activity: " . $e->getMessage());
    }
}
