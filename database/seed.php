<?php

declare(strict_types=1);

/**
 * Carga los seeds (catálogos) y crea el administrador inicial a partir de .env
 * (ADMIN_NAME, ADMIN_USERNAME, ADMIN_EMAIL, ADMIN_PASSWORD). Es idempotente.
 *
 * Uso: php database/seed.php
 */

use App\Core\Database;

require dirname(__DIR__) . '/config/bootstrap.php';

$pdo = Database::make(config('database.name_new') ?: null);

$archivos = glob(__DIR__ . '/seeds/*.sql') ?: [];
sort($archivos);
foreach ($archivos as $archivo) {
    $pdo->exec((string) file_get_contents($archivo));
    echo 'Seed aplicado: ' . basename($archivo) . PHP_EOL;
}

$nombre = (string) env('ADMIN_NAME', 'Administrador');
$username = (string) env('ADMIN_USERNAME', 'admin');
$email = (string) env('ADMIN_EMAIL', '');
$password = (string) env('ADMIN_PASSWORD', '');

if ($email === '' || $password === '') {
    echo 'ADMIN_EMAIL/ADMIN_PASSWORD no definidos en .env: se omite la creación del administrador.' . PHP_EOL;
    exit(0);
}
if (strlen($password) < 10) {
    fwrite(STDERR, 'ADMIN_PASSWORD debe tener al menos 10 caracteres.' . PHP_EOL);
    exit(1);
}

$existe = $pdo->prepare('SELECT id FROM usuarios WHERE username = ? OR email = ?');
$existe->execute([$username, $email]);
if ($existe->fetchColumn()) {
    echo "El administrador '{$username}' ya existe; no se modifica." . PHP_EOL;
    exit(0);
}

$rol = $pdo->query("SELECT id FROM roles WHERE nombre = 'Administrador'")->fetchColumn();
$pdo->prepare(
    'INSERT INTO usuarios (nombre, username, email, password_hash, rol_id, estado, password_cambiada_at)
     VALUES (?, ?, ?, ?, ?, \'activo\', NOW())'
)->execute([$nombre, $username, $email, password_hash($password, PASSWORD_DEFAULT), $rol]);

echo "Administrador '{$username}' creado." . PHP_EOL;
