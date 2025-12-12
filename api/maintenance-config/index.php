<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../../config.php';
require_once '../apiHelper.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    sendError('Error de conexión a la base de datos', 500);
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        getConfigurations($pdo);
        break;
    
    case 'POST':
        updateConfiguration($pdo);
        break;
    
    case 'PUT':
        updateConfiguration($pdo);
        break;
    
    default:
        sendError('Método no permitido', 405);
}

function getConfigurations($pdo) {
    try {
        $sql = "SELECT 
                    ConfigID,
                    ConfigKey,
                    ConfigValue,
                    ConfigType,
                    Descripcion,
                    FechaModificacion,
                    Activo
                FROM MaintenanceConfig
                WHERE Activo = 1
                ORDER BY 
                    CASE ConfigKey
                        WHEN 'MAINTENANCE_MODE_ENABLED' THEN 1
                        WHEN 'BLOCKED_WEEKDAYS' THEN 2
                        WHEN 'BLOCKED_DATES' THEN 3
                        WHEN 'BLOCKED_TIME_START' THEN 4
                        WHEN 'BLOCKED_TIME_END' THEN 5
                        ELSE 6
                    END";
        
        $stmt = $pdo->query($sql);
        $configs = $stmt->fetchAll();
        
        $processedConfigs = [];
        foreach ($configs as $config) {
            $value = $config['ConfigValue'];
            
            switch ($config['ConfigType']) {
                case 'boolean':
                    $value = ($value === 'true' || $value === '1');
                    break;
                case 'array':
                    $value = json_decode($value, true) ?? [];
                    break;
                case 'integer':
                    $value = (int)$value;
                    break;
            }
            
            $processedConfigs[] = [
                'id' => $config['ConfigID'],
                'key' => $config['ConfigKey'],
                'value' => $value,
                'type' => $config['ConfigType'],
                'descripcion' => $config['Descripcion'],
                'fecha_modificacion' => $config['FechaModificacion']
            ];
        }
        
        sendSuccess([
            'configuraciones' => $processedConfigs,
            'total' => count($processedConfigs)
        ]);
        
    } catch (PDOException $e) {
        sendError('Error al obtener configuraciones: ' . $e->getMessage(), 500);
    }
}

function updateConfiguration($pdo) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            $input = $_POST;
        }
        
        if (!isset($input['key']) || !isset($input['value'])) {
            sendError('Campos requeridos: key, value', 400);
        }
        
        $key = $input['key'];
        $value = $input['value'];
        $usuarioID = $_SESSION['id'] ?? null;
        
        $stmt = $pdo->prepare("SELECT ConfigValue, ConfigType FROM MaintenanceConfig WHERE ConfigKey = ?");
        $stmt->execute([$key]);
        $currentConfig = $stmt->fetch();
        
        if (!$currentConfig) {
            sendError('Configuración no encontrada', 404);
        }
        
        $valorAnterior = $currentConfig['ConfigValue'];
        $configType = $currentConfig['ConfigType'];
        
        $valorNuevo = $value;
        switch ($configType) {
            case 'boolean':
                $valorNuevo = $value ? 'true' : 'false';
                break;
            case 'array':
                $valorNuevo = json_encode($value);
                break;
            case 'integer':
                $valorNuevo = (string)(int)$value;
                break;
        }
        
        $stmt = $pdo->prepare("
            UPDATE MaintenanceConfig 
            SET ConfigValue = ?, 
                ModificadoPor = ?,
                FechaModificacion = NOW()
            WHERE ConfigKey = ?
        ");
        
        $stmt->execute([$valorNuevo, $usuarioID, $key]);
        
        $stmt = $pdo->prepare("
            INSERT INTO MaintenanceConfigLog 
            (ConfigKey, ValorAnterior, ValorNuevo, UsuarioID, IPAddress, UserAgent)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $key,
            $valorAnterior,
            $valorNuevo,
            $usuarioID,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
        
        sendSuccess([
            'mensaje' => 'Configuración actualizada correctamente',
            'key' => $key,
            'valor_anterior' => $valorAnterior,
            'valor_nuevo' => $valorNuevo
        ]);
        
    } catch (PDOException $e) {
        sendError('Error al actualizar configuración: ' . $e->getMessage(), 500);
    }
}

function updateMultipleConfigurations($pdo) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['configuraciones']) || !is_array($input['configuraciones'])) {
            sendError('Se requiere un array de configuraciones', 400);
        }
        
        $pdo->beginTransaction();
        $updated = 0;
        
        foreach ($input['configuraciones'] as $config) {
            if (!isset($config['key']) || !isset($config['value'])) {
                continue;
            }
            
            $stmt = $pdo->prepare("
                UPDATE MaintenanceConfig 
                SET ConfigValue = ?, 
                    ModificadoPor = ?,
                    FechaModificacion = NOW()
                WHERE ConfigKey = ?
            ");
            
            $stmt->execute([
                is_array($config['value']) ? json_encode($config['value']) : $config['value'],
                $_SESSION['id'] ?? null,
                $config['key']
            ]);
            
            $updated++;
        }
        
        $pdo->commit();
        
        sendSuccess([
            'mensaje' => 'Configuraciones actualizadas correctamente',
            'actualizadas' => $updated
        ]);
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        sendError('Error al actualizar configuraciones: ' . $e->getMessage(), 500);
    }
}
