<?php

return [
    'name' => env('APP_NAME', 'Kalli Inventory System'),
    'short_name' => env('APP_SHORT_NAME', 'Kalli Jaguar'),
    'version' => env('APP_VERSION', '2.1.8'),
    'build' => env('APP_BUILD', '20250415'),
    'release_date' => env('APP_RELEASE_DATE', ''),
    'company' => env('APP_COMPANY', 'Kalli Jaguar'),
    'developer' => env('APP_DEVELOPER', ''),
    'year' => env('APP_YEAR', date('Y')),
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'timezone' => env('APP_TIMEZONE', 'America/Mexico_City'),
    'tax_rate' => (float) env('TAX_RATE', 0.16),
    'locale' => env('APP_LOCALE', 'es-MX'),
    'url' => rtrim((string) env('APP_URL', ''), '/'),

    'modules' => [
        'products' => env('MODULE_PRODUCTS', '2.0'),
        'categories' => env('MODULE_CATEGORIES', '2.0'),
        'tags' => env('MODULE_TAGS', '2.0'),
        'orders' => env('MODULE_ORDERS', '1.0'),
        'users' => env('MODULE_USERS', '2.0'),
    ],

    'api' => [
        'version' => env('API_VERSION', 'v1'),
        'timeout' => (int) env('API_TIMEOUT', 30),
        'max_requests' => (int) env('API_MAX_REQUESTS', 100),
    ],

    'pagination' => [
        'default_limit' => (int) env('PAGINATION_DEFAULT_LIMIT', 20),
        'max_limit' => (int) env('PAGINATION_MAX_LIMIT', 100),
    ],

    'security' => [
        'password_min_length' => (int) env('PASSWORD_MIN_LENGTH', 8),
        'max_login_attempts' => (int) env('MAX_LOGIN_ATTEMPTS', 5),
        'lockout_time' => (int) env('LOCKOUT_TIME', 900),
    ],
];
