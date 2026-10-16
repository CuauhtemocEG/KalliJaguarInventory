<?php

return [
    'host' => env('DB_HOST', 'localhost'),
    'port' => (int) env('DB_PORT', 3306),
    'name' => env('DB_NAME', ''),
    'user' => env('DB_USER', ''),
    'pass' => (string) env('DB_PASS', ''),
    'name_new' => env('DB_NAME_NEW', ''),
    'legacy_name' => env('LEGACY_DB_NAME', ''),
    'legacy_group_by' => (bool) env('DB_LEGACY_GROUP_BY', true),
    'charset' => env('DB_CHARSET', 'utf8mb4'),
];
