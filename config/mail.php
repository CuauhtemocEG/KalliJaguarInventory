<?php

$lista = static fn (string $valor): array => array_values(array_filter(array_map('trim', explode(',', $valor))));

return [
    'host' => env('MAIL_HOST', ''),
    'port' => (int) env('MAIL_PORT', 465),
    'encryption' => env('MAIL_ENCRYPTION', 'ssl'),
    'username' => env('MAIL_USERNAME', ''),
    'password' => (string) env('MAIL_PASSWORD', ''),
    'from_address' => env('MAIL_FROM_ADDRESS', ''),
    'from_name' => env('MAIL_FROM_NAME', ''),
    'to' => env('MAIL_TO', ''),
    'cc' => $lista((string) env('MAIL_CC', '')),
];
