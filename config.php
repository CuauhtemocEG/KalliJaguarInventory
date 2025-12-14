<?php
// ============================================
// INFORMACIÓN DE LA APLICACIÓN
// ============================================

define('APP_NAME', 'Kalli Inventory System');
define('APP_SHORT_NAME', 'Kalli Jaguar');
define('APP_VERSION', '2.1.8');
define('APP_BUILD', '20250415'); // Formato: YYYYMMDD
define('APP_RELEASE_DATE', '9 de diciembre de 2025');

// ============================================
// VERSIONES DE MÓDULOS
// ============================================

define('MODULE_PRODUCTS', '2.0');
define('MODULE_CATEGORIES', '2.0');
define('MODULE_TAGS', '2.0');
define('MODULE_ORDERS', '1.0');
define('MODULE_USERS', '2.0');

// ============================================
// INFORMACIÓN DE DESARROLLO
// ============================================

define('APP_DEVELOPER', 'Kalli Development Team');
define('APP_COMPANY', 'Kalli Jaguar');
define('APP_YEAR', '2025');

// ============================================
// CONFIGURACIÓN DE SISTEMA
// ============================================

define('APP_ENV', 'production'); // production, development, staging
define('APP_DEBUG', false); // true para mostrar errores detallados
define('APP_TIMEZONE', 'America/Mexico_City');

// ============================================
// CONFIGURACIÓN DE BASE DE DATOS
// ============================================

define('DB_HOST', 'localhost:3306');
define('DB_NAME', 'kallijag_inventory');
define('DB_USER', 'kallijag_admin');
define('DB_PASS', 'uNtiL.horSe@5');
define('DB_CHARSET', 'utf8mb4');

// ============================================
// CONFIGURACIÓN DE API
// ============================================

define('API_VERSION', 'v1');
define('API_TIMEOUT', 30); // segundos
define('API_MAX_REQUESTS', 100); // por minuto

// ============================================
// CONFIGURACIÓN DE PAGINACIÓN
// ============================================

define('PAGINATION_DEFAULT_LIMIT', 20);
define('PAGINATION_MAX_LIMIT', 100);

// ============================================
// RUTAS DEL SISTEMA
// ============================================

define('ROOT_PATH', dirname(__DIR__));
define('UPLOADS_PATH', ROOT_PATH . '/img/producto/');
define('LOGS_PATH', ROOT_PATH . '/logs/');

// ============================================
// CONFIGURACIÓN DE SESIÓN
// ============================================

define('SESSION_LIFETIME', 7200); // 2 horas en segundos
define('SESSION_NAME', 'KALLI_SESSION');

// ============================================
// CONFIGURACIÓN DE SEGURIDAD
// ============================================

define('PASSWORD_MIN_LENGTH', 8);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 900); // 15 minutos en segundos


// ============================================
// FUNCIONES AUXILIARES
// ============================================

/**
 * Obtener versión completa con build
 */
function getFullVersion() {
    return APP_VERSION . ' (Build ' . APP_BUILD . ')';
}

/**
 * Obtener información de copyright
 */
function getCopyright() {
    return '&copy; ' . APP_YEAR . ' ' . APP_COMPANY . '. Todos los derechos reservados.';
}

/**
 * Verificar si está en modo debug
 */
function isDebugMode() {
    return APP_DEBUG === true;
}

/**
 * Verificar si está en producción
 */
function isProduction() {
    return APP_ENV === 'production';
}

/**
 * Obtener información del sistema como array
 */
function getSystemInfo() {
    return [
        'name' => APP_NAME,
        'short_name' => APP_SHORT_NAME,
        'version' => APP_VERSION,
        'build' => APP_BUILD,
        'release_date' => APP_RELEASE_DATE,
        'full_version' => getFullVersion(),
        'environment' => APP_ENV,
        'developer' => APP_DEVELOPER,
        'company' => APP_COMPANY,
        'year' => APP_YEAR,
        'copyright' => getCopyright(),
        'modules' => [
            'products' => MODULE_PRODUCTS,
            'categories' => MODULE_CATEGORIES,
            'tags' => MODULE_TAGS,
            'orders' => MODULE_ORDERS,
            'users' => MODULE_USERS
        ]
    ];
}

// ============================================
// ZONA HORARIA
// ============================================

date_default_timezone_set(APP_TIMEZONE);

// ============================================
// MANEJO DE ERRORES (solo en desarrollo)
// ============================================

if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
