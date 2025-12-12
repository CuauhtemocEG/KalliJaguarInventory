<?php
function getMaintenanceConfigFromAPI() {
    $apiUrl = getApiUrl() . 'maintenance-config/index.php';
    
    try {
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($httpCode !== 200 || !$response) {
            error_log("Error al consultar API de mantenimiento: HTTP $httpCode" . ($curlError ? ", Error: $curlError" : ''));
            return null;
        }
        
        $data = json_decode($response, true);
        
        if (!$data || !isset($data['success']) || !$data['success']) {
            error_log("Respuesta inválida de API de mantenimiento");
            return null;
        }
        
        return $data['data']['configuraciones'];
        
    } catch (Exception $e) {
        error_log("Excepción al consultar API de mantenimiento: " . $e->getMessage());
        return null;
    }
}

function getApiUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $basePath = dirname(dirname($scriptName));
    
    if (basename(dirname($scriptName)) !== 'pages') {
        $basePath = dirname($scriptName);
    }
    
    $apiUrl = $protocol . '://' . $host . $basePath . '/api/';
    
    return $apiUrl;
}

function parseConfigurations($configs) {
    $parsed = [];
    
    foreach ($configs as $config) {
        $key = $config['key'];
        $value = $config['value'];
        
        $parsed[$key] = $value;
    }
    
    return $parsed;
}

function isMaintenanceModeActive() {
    $configs = getMaintenanceConfigFromAPI();
    
    if (!$configs) {
        return false;
    }
    
    $parsed = parseConfigurations($configs);
    
    if (!isset($parsed['MAINTENANCE_MODE_ENABLED']) || !$parsed['MAINTENANCE_MODE_ENABLED']) {
        return false;
    }
    
    return checkMaintenanceRules($parsed);
}

function checkMaintenanceRules($config) {
    $now = new DateTime('now', new DateTimeZone('America/Mexico_City'));
    
    $currentWeekday = (int)$now->format('w');
    $blockedWeekdays = $config['BLOCKED_WEEKDAYS'] ?? [];
    
    if (in_array($currentWeekday, $blockedWeekdays)) {
        return true;
    }
    
    $currentDate = $now->format('Y-m-d');
    $blockedDates = $config['BLOCKED_DATES'] ?? [];
    
    if (in_array($currentDate, $blockedDates)) {
        return true;
    }
    
    $timeStart = $config['BLOCKED_TIME_START'] ?? null;
    $timeEnd = $config['BLOCKED_TIME_END'] ?? null;
    
    if ($timeStart && $timeEnd) {
        $currentTime = $now->format('H:i');
        
        if ($timeStart < $timeEnd) {
            if ($currentTime >= $timeStart && $currentTime <= $timeEnd) {
                return true;
            }
        } else {
            if ($currentTime >= $timeStart || $currentTime <= $timeEnd) {
                return true;
            }
        }
    }
    
    return false;
}

function getMaintenanceInfo() {
    if (!isMaintenanceModeActive()) {
        return null;
    }
    
    $configs = getMaintenanceConfigFromAPI();
    
    if (!$configs) {
        return null;
    }
    
    $parsed = parseConfigurations($configs);
    
    $reason = determineBlockReason($parsed);
    
    return [
        'enabled' => true,
        'message' => $parsed['MAINTENANCE_MESSAGE'] ?? 'Sistema en mantenimiento',
        'submessage' => $parsed['MAINTENANCE_SUBMESSAGE'] ?? 'Por favor, intenta más tarde',
        'logo' => $parsed['MAINTENANCE_LOGO_PATH'] ?? '../img/logo.png',
        'redirect_url' => $parsed['MAINTENANCE_REDIRECT_URL'] ?? 'home.php',
        'show_reasons' => $parsed['MAINTENANCE_SHOW_REASONS'] ?? true,
        'reason' => $reason,
        'reason_details' => getReasonDetails($reason, $parsed)
    ];
}

function determineBlockReason($config) {
    $now = new DateTime('now', new DateTimeZone('America/Mexico_City'));
    
    $currentWeekday = (int)$now->format('w');
    $blockedWeekdays = $config['BLOCKED_WEEKDAYS'] ?? [];
    
    if (in_array($currentWeekday, $blockedWeekdays)) {
        return 'weekday';
    }
    
    $currentDate = $now->format('Y-m-d');
    $blockedDates = $config['BLOCKED_DATES'] ?? [];
    
    if (in_array($currentDate, $blockedDates)) {
        return 'date';
    }
    
    $timeStart = $config['BLOCKED_TIME_START'] ?? null;
    $timeEnd = $config['BLOCKED_TIME_END'] ?? null;
    
    if ($timeStart && $timeEnd) {
        $currentTime = $now->format('H:i');
        
        if ($timeStart < $timeEnd) {
            if ($currentTime >= $timeStart && $currentTime <= $timeEnd) {
                return 'time';
            }
        } else {
            if ($currentTime >= $timeStart || $currentTime <= $timeEnd) {
                return 'time';
            }
        }
    }
    
    return 'manual';
}

function getReasonDetails($reason, $config) {
    $now = new DateTime('now', new DateTimeZone('America/Mexico_City'));
    
    switch ($reason) {
        case 'weekday':
            $weekdays = [
                0 => 'Domingo',
                1 => 'Lunes',
                2 => 'Martes',
                3 => 'Miércoles',
                4 => 'Jueves',
                5 => 'Viernes',
                6 => 'Sábado'
            ];
            $currentWeekday = (int)$now->format('w');
            return 'Los pedidos no están disponibles los días ' . $weekdays[$currentWeekday];
            
        case 'date':
            $currentDate = $now->format('d/m/Y');
            return 'Fecha especial bloqueada: ' . $currentDate;
            
        case 'time':
            $timeStart = $config['BLOCKED_TIME_START'] ?? '';
            $timeEnd = $config['BLOCKED_TIME_END'] ?? '';
            return 'Horario de pedidos: fuera del rango permitido (' . $timeStart . ' - ' . $timeEnd . ')';
            
        default:
            return 'El sistema está temporalmente deshabilitado';
    }
}

function logBlockedAccess($usuarioID = null, $reason = '', $pagina = 'requestProducts') {
    try {
        require_once dirname(__DIR__) . '/controllers/mainController.php';
        $conn = conexion();
        
        if (!$conn) {
            error_log("Error: No se pudo conectar a la base de datos para registrar acceso bloqueado");
            return;
        }
        
        $configs = getMaintenanceConfigFromAPI();
        if ($configs) {
            $parsed = parseConfigurations($configs);
            $detallesRazon = getReasonDetails($reason, $parsed);
        } else {
            $detallesRazon = 'No se pudo obtener detalles';
        }
        
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        
        $sql = "INSERT INTO MaintenanceAccessLog 
                (UsuarioID, Pagina, Razon, DetallesRazon, IPAddress, UserAgent) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->execute([
                $usuarioID,
                $pagina,
                $reason,
                $detallesRazon,
                $ipAddress,
                $userAgent
            ]);
            
            error_log("Acceso bloqueado registrado - Usuario: " . ($usuarioID ?? 'NULL') . " - Razón: $reason");
        } else {
            error_log("Error al preparar statement para MaintenanceAccessLog");
        }
        
        $conn = null;
        
    } catch (Exception $e) {
        error_log("Excepción al registrar acceso bloqueado: " . $e->getMessage());
    }
}
