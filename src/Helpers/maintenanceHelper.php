<?php
/** Configuración de mantenimiento leída directo de la BD (misma forma que devuelve maintenance-config/index.php). */
function getMaintenanceConfigFromAPI() {
    static $cache = null;
    static $cachedAt = 0;
    if ($cache !== null && (time() - $cachedAt) < 2) {
        return $cache;
    }

    try {
        require_once BASE_PATH . '/src/Helpers/legacy.php';
        $rows = conexion()->query('SELECT ConfigKey, ConfigValue, ConfigType FROM MaintenanceConfig WHERE Activo = 1')->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        error_log('Error al leer configuración de mantenimiento: ' . $e->getMessage());
        return null;
    }

    $configs = [];
    foreach ($rows as $row) {
        $value = $row['ConfigValue'];
        $value = match ($row['ConfigType']) {
            'boolean' => $value === 'true' || $value === '1',
            'array' => json_decode((string) $value, true) ?? [],
            'integer' => (int) $value,
            default => $value,
        };
        $configs[] = ['key' => $row['ConfigKey'], 'value' => $value];
    }

    $cachedAt = time();

    return $cache = $configs;
}

function getApiUrl() {
    $baseUrl = config('app.url');

    if ($baseUrl) {
        return $baseUrl . '/api/';
    }

    $protocol = \App\Core\Session::isHttps() ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'] . '/api/';
}

/** Convierte rutas heredadas ("../img/x.png", "./img/x.png") en URLs de public/assets. */
function normalizeAssetPath($path) {
    if (preg_match('#^(?:https?:)?//#', $path)) {
        return $path;
    }
    return asset(preg_replace('#^(?:\.{1,2}/)*(?:assets/)?#', '', $path));
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
    $now = new DateTime('now', new DateTimeZone(date_default_timezone_get()));
    
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
        'logo' => normalizeAssetPath($parsed['MAINTENANCE_LOGO_PATH'] ?? 'img/logo.png'),
        'redirect_url' => $parsed['MAINTENANCE_REDIRECT_URL'] ?? 'home.php',
        'show_reasons' => $parsed['MAINTENANCE_SHOW_REASONS'] ?? true,
        'reason' => $reason,
        'reason_details' => getReasonDetails($reason, $parsed)
    ];
}

function determineBlockReason($config) {
    $now = new DateTime('now', new DateTimeZone(date_default_timezone_get()));
    
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
    $now = new DateTime('now', new DateTimeZone(date_default_timezone_get()));
    
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
        require_once BASE_PATH . '/src/Helpers/legacy.php';
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
