<?php

declare(strict_types=1);

/**
 * Ejecuta las migraciones SQL pendientes de database/migrations (orden numérico).
 *
 * Uso: php database/migrate.php [--status]
 */

use App\Core\Database;

require dirname(__DIR__) . '/config/bootstrap.php';

$pdo = Database::make(config('database.name_new') ?: null);
$pdo->exec(
    'CREATE TABLE IF NOT EXISTS schema_migrations (
        version VARCHAR(100) NOT NULL PRIMARY KEY,
        applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
);

$aplicadas = $pdo->query('SELECT version FROM schema_migrations')->fetchAll(PDO::FETCH_COLUMN);
$archivos = glob(__DIR__ . '/migrations/*.sql') ?: [];
sort($archivos);

$soloEstado = in_array('--status', $argv ?? [], true);
$pendientes = 0;

foreach ($archivos as $archivo) {
    $version = basename($archivo, '.sql');
    $hecha = in_array($version, $aplicadas, true);
    if ($soloEstado) {
        echo ($hecha ? '[aplicada]  ' : '[pendiente] ') . $version . PHP_EOL;
        continue;
    }
    if ($hecha) {
        continue;
    }
    $pendientes++;
    echo "Aplicando {$version}... ";
    try {
        $pdo->exec((string) file_get_contents($archivo));
        $pdo->prepare('INSERT INTO schema_migrations (version) VALUES (?)')->execute([$version]);
        echo "OK" . PHP_EOL;
    } catch (Throwable $e) {
        fwrite(STDERR, PHP_EOL . 'ERROR en ' . $version . ': ' . $e->getMessage() . PHP_EOL);
        exit(1);
    }
}

if (!$soloEstado) {
    echo $pendientes === 0 ? 'No hay migraciones pendientes.' . PHP_EOL : "Migraciones aplicadas: {$pendientes}" . PHP_EOL;
}
