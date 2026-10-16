<?php

declare(strict_types=1);

/**
 * Migra los datos del esquema ANTERIOR (prefijo kallijag_inventory, tablas CamelCase)
 * al esquema NUEVO (database/migrations). Ambas bases deben estar en el mismo servidor MySQL
 * y ser accesibles con el mismo usuario (DB_USER/DB_PASS).
 *
 *   LEGACY_DB_NAME = base con el esquema anterior (solo lectura)
 *   DB_NAME_NEW    = base con el esquema nuevo (ya migrada con database/migrate.php; debe estar vacía)
 *
 * Uso: php database/migrate_legacy.php [--dry-run]
 *
 * Se ejecuta en una sola transacción: si algo falla no se escribe nada. Con --dry-run se hace ROLLBACK
 * al final y solo se muestra el reporte de conteos.
 */

use App\Core\Database;

require dirname(__DIR__) . '/config/bootstrap.php';

$legacy = (string) config('database.legacy_name');
$nueva = (string) (config('database.name_new') ?: config('database.name'));
$dryRun = in_array('--dry-run', $argv ?? [], true);

if (!preg_match('/^[A-Za-z0-9_]+$/', $legacy) || !preg_match('/^[A-Za-z0-9_]+$/', $nueva) || $legacy === $nueva) {
    fwrite(STDERR, "Defina LEGACY_DB_NAME (esquema anterior) y DB_NAME_NEW (esquema nuevo) distintos en .env\n");
    exit(1);
}

$pdo = Database::make($nueva);
$L = "`{$legacy}`";
$C = 'COLLATE utf8mb4_unicode_ci';

if ((int) $pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn() > 0) {
    fwrite(STDERR, "La base nueva ya contiene usuarios: la migración solo se ejecuta sobre una base vacía.\n");
    exit(1);
}

// Verificaciones previas: datos que violarían claves únicas del esquema nuevo
$duplicados = [
    'UPC duplicado en Productos' => "SELECT UPC FROM {$L}.Productos GROUP BY UPC HAVING COUNT(*) > 1",
    'Username duplicado en Usuarios' => "SELECT Username FROM {$L}.Usuarios GROUP BY Username HAVING COUNT(*) > 1",
    'Nombre duplicado en Sucursales' => "SELECT nombre FROM {$L}.Sucursales GROUP BY nombre HAVING COUNT(*) > 1",
    'Nombre duplicado en Tags' => "SELECT Nombre FROM {$L}.Tags GROUP BY Nombre HAVING COUNT(*) > 1",
];
$hayError = false;
foreach ($duplicados as $mensaje => $sql) {
    $valores = $pdo->query($sql)->fetchAll(PDO::FETCH_COLUMN);
    if ($valores) {
        $hayError = true;
        fwrite(STDERR, "{$mensaje}: " . implode(', ', $valores) . "\n");
    }
}
if ($hayError) {
    fwrite(STDERR, "Corrija los duplicados en la base anterior y reintente.\n");
    exit(1);
}

$pasos = [
    'categorias' => "INSERT INTO categorias (id, nombre)
        SELECT MIN(CategoriaID), MIN(TRIM(Nombre)) FROM {$L}.Categorias GROUP BY TRIM(Nombre) {$C}",

    'proveedores' => "INSERT IGNORE INTO proveedores (nombre)
        SELECT MIN(TRIM(Proveedor)) FROM {$L}.Productos
        WHERE Proveedor IS NOT NULL AND TRIM(Proveedor) <> '' GROUP BY TRIM(Proveedor) {$C}",

    'usuarios' => "INSERT INTO usuarios (id, nombre, username, email, password_hash, rol_id, estado, telefono, avatar,
            notas_admin, intentos_fallidos, ultimo_acceso_at, ultimo_acceso_ip, password_cambiada_at, created_at, updated_at)
        SELECT u.UsuarioID, u.Nombre, u.Username,
            COALESCE(NULLIF(TRIM(u.email), ''), CONCAT(u.Username, '@sin-correo.invalid')),
            u.Password, r.id, COALESCE(u.estado, 'activo'), u.telefono, u.avatar, u.notas_admin,
            LEAST(COALESCE(u.intentos_fallidos, 0), 255), u.ultimo_acceso, u.ip_ultimo_acceso,
            u.fecha_ultimo_cambio_password, COALESCE(u.fecha_creacion, NOW()),
            COALESCE(u.fecha_ultima_modificacion, u.fecha_creacion, NOW())
        FROM {$L}.Usuarios u JOIN roles r ON r.nombre {$C} = u.Rol {$C}",

    'usuarios.auditoria' => "UPDATE usuarios n
        JOIN {$L}.Usuarios u ON u.UsuarioID = n.id
        LEFT JOIN usuarios c ON c.id = u.creado_por
        LEFT JOIN usuarios m ON m.id = u.modificado_por
        SET n.created_by = c.id, n.updated_by = m.id",

    'password_resets' => "INSERT INTO password_resets (usuario_id, token_hash, expires_at)
        SELECT UsuarioID, SHA2(token_reset_password, 256), COALESCE(token_reset_expira, NOW())
        FROM {$L}.Usuarios WHERE token_reset_password IS NOT NULL AND token_reset_password <> ''",

    'tags' => "INSERT INTO tags (id, nombre, color, descripcion, icono, activo, created_at, created_by)
        SELECT t.TagID, t.Nombre, t.Color, t.Descripcion, t.Icono, COALESCE(t.Activo, 1),
               COALESCE(t.FechaCreacion, NOW()), u.id
        FROM {$L}.Tags t LEFT JOIN usuarios u ON u.id = t.CreadoPor",

    'sucursales' => "INSERT INTO sucursales (id, nombre, direccion, telefono, email, gerente, estado, tipo,
            horario_apertura, horario_cierre, notas, created_at, updated_at, created_by)
        SELECT s.SucursalID, s.nombre, s.direccion, s.telefono, s.email, s.gerente, COALESCE(s.estado, 'activa'), s.tipo,
            s.horario_apertura, s.horario_cierre, s.notas, COALESCE(s.fecha_creacion, NOW()),
            COALESCE(s.fecha_actualizacion, s.fecha_creacion, NOW()), u.id
        FROM {$L}.Sucursales s LEFT JOIN usuarios u ON u.id = s.creado_por",

    'productos' => "INSERT INTO productos (id, upc, sku, nombre, descripcion, tipo, precio_unitario, cantidad, stock_minimo,
            categoria_id, proveedor_id, imagen, activo, created_at, updated_at, created_by)
        SELECT p.ProductoID, p.UPC, NULLIF(TRIM(p.SKU), ''), p.Nombre, p.Descripcion, LOWER(p.Tipo),
            GREATEST(COALESCE(p.PrecioUnitario, 0), 0), GREATEST(COALESCE(p.Cantidad, 0), 0),
            GREATEST(COALESCE(p.StockMinimo, 0), 0), cn.id, pv.id, NULLIF(TRIM(p.image), ''), COALESCE(p.Activo, 1),
            COALESCE(p.FechaCreacion, NOW()), COALESCE(p.FechaModificacion, p.FechaCreacion, NOW()), u.id
        FROM {$L}.Productos p
        LEFT JOIN {$L}.Categorias co ON co.CategoriaID = p.CategoriaID
        LEFT JOIN categorias cn ON cn.nombre {$C} = TRIM(co.Nombre) {$C}
        LEFT JOIN proveedores pv ON pv.nombre {$C} = TRIM(p.Proveedor) {$C}
        LEFT JOIN usuarios u ON u.id = p.UsuarioID",

    'producto_tags' => "INSERT IGNORE INTO producto_tags (producto_id, tag_id, asignado_por, created_at)
        SELECT pt.ProductoID, pt.TagID, u.id, COALESCE(pt.FechaAsignacion, NOW())
        FROM {$L}.ProductoTags pt
        JOIN productos p ON p.id = pt.ProductoID JOIN tags t ON t.id = pt.TagID
        LEFT JOIN usuarios u ON u.id = pt.AsignadoPor",

    'comandas' => "INSERT INTO comandas (folio, sucursal_id, solicitante_id, estado_id, fecha_entrega, created_at)
        SELECT m.ComandaID, MIN(m.SucursalID), MIN(u.id),
            COALESCE((SELECT e.id FROM estados_comanda e WHERE e.codigo = CASE
                SUBSTRING_INDEX(GROUP_CONCAT(m.Status ORDER BY m.MovimientoID DESC), ',', 1)
                    WHEN 'En transito' THEN 'en_transito' WHEN 'Cerrado' THEN 'cerrado'
                    WHEN 'Cancelado' THEN 'cancelado' ELSE 'abierto' END),
                (SELECT id FROM estados_comanda WHERE codigo = 'abierto')),
            MAX(m.FechaDelivery), MIN(m.FechaMovimiento)
        FROM {$L}.MovimientosInventario m
        JOIN sucursales s ON s.id = m.SucursalID
        LEFT JOIN usuarios u ON u.id = m.UsuarioID
        WHERE m.TipoMovimiento = 'Salida' AND m.ComandaID IS NOT NULL AND m.ComandaID <> ''
        GROUP BY m.ComandaID",

    'comanda_detalles' => "INSERT INTO comanda_detalles (comanda_id, producto_id, cantidad, precio_unitario, precio_final)
        SELECT c.id, m.ProductoID, SUM(m.Cantidad), p.precio_unitario, COALESCE(SUM(m.PrecioFinal), 0)
        FROM {$L}.MovimientosInventario m
        JOIN comandas c ON c.folio {$C} = m.ComandaID {$C}
        JOIN productos p ON p.id = m.ProductoID
        WHERE m.TipoMovimiento = 'Salida' AND m.Cantidad > 0
        GROUP BY c.id, m.ProductoID, p.precio_unitario",

    'movimientos_inventario' => "INSERT INTO movimientos_inventario (id, producto_id, tipo, cantidad, precio_final, comanda_id,
            sucursal_id, usuario_id, created_at)
        SELECT m.MovimientoID, m.ProductoID, LOWER(m.TipoMovimiento), m.Cantidad, m.PrecioFinal, c.id, s.id, u.id,
            COALESCE(m.FechaMovimiento, NOW())
        FROM {$L}.MovimientosInventario m
        JOIN productos p ON p.id = m.ProductoID
        LEFT JOIN comandas c ON c.folio {$C} = m.ComandaID {$C}
        LEFT JOIN sucursales s ON s.id = m.SucursalID
        LEFT JOIN usuarios u ON u.id = m.UsuarioID
        WHERE m.Cantidad > 0",

    'comanda_estado_historial' => "INSERT INTO comanda_estado_historial (comanda_id, estado_anterior_id, estado_nuevo_id,
            usuario_id, observaciones, created_at)
        SELECT c.id,
            (SELECT e.id FROM estados_comanda e WHERE e.codigo = CASE
                WHEN h.EstadoAnterior IS NULL THEN NULL WHEN h.EstadoAnterior LIKE 'En Tr%' THEN 'en_transito'
                WHEN h.EstadoAnterior = 'Cerrado' THEN 'cerrado' WHEN h.EstadoAnterior = 'Cancelado' THEN 'cancelado'
                ELSE 'abierto' END),
            (SELECT e.id FROM estados_comanda e WHERE e.codigo = CASE
                WHEN h.EstadoNuevo LIKE 'En Tr%' THEN 'en_transito' WHEN h.EstadoNuevo = 'Cerrado' THEN 'cerrado'
                WHEN h.EstadoNuevo = 'Cancelado' THEN 'cancelado' ELSE 'abierto' END),
            u.id, h.Observaciones, COALESCE(h.FechaCambio, NOW())
        FROM {$L}.EstadosComanda h
        JOIN comandas c ON c.folio {$C} = h.ComandaID {$C}
        LEFT JOIN usuarios u ON u.id = h.UsuarioID",

    'picking_logs' => "INSERT INTO picking_logs (comanda_id, producto_id, cantidad_solicitada, cantidad_pickeada, usuario_id, created_at)
        SELECT c.id, p.id, GREATEST(COALESCE(l.CantidadSolicitada, 0), 0), GREATEST(COALESCE(l.CantidadPickeada, 0), 0), u.id,
            COALESCE(l.FechaPicking, NOW())
        FROM {$L}.PickingLog l
        JOIN comandas c ON c.folio {$C} = l.ComandaID {$C}
        JOIN productos p ON p.id = l.ProductoID
        LEFT JOIN usuarios u ON u.id = l.UsuarioID",

    'carrito_items' => "INSERT INTO carrito_items (usuario_id, producto_id, cantidad)
        SELECT k.UsuarioID, k.ProductoID, k.Cantidad
        FROM {$L}.CarritoSolicitudes k
        JOIN usuarios u ON u.id = k.UsuarioID JOIN productos p ON p.id = k.ProductoID
        WHERE k.Cantidad > 0",

    'stock_logs' => "INSERT INTO stock_logs (producto_id, stock_antes, stock_despues, usuario_id, created_at)
        SELECT p.id, l.StockBefore, l.StockAfter, u.id, COALESCE(l.Fecha, NOW())
        FROM {$L}.Logs_stock l
        JOIN productos p ON p.upc {$C} = l.UPC {$C}
        LEFT JOIN usuarios u ON u.id = l.UsuarioID",

    'configuracion_mantenimiento' => "INSERT INTO configuracion_mantenimiento (clave, valor, tipo, descripcion, activo, updated_at)
        SELECT ConfigKey, ConfigValue, COALESCE(ConfigType, 'string'), Descripcion, COALESCE(Activo, 1), COALESCE(FechaModificacion, NOW())
        FROM {$L}.MaintenanceConfig
        ON DUPLICATE KEY UPDATE valor = VALUES(valor), tipo = VALUES(tipo), descripcion = VALUES(descripcion), activo = VALUES(activo)",

    'configuracion_mantenimiento_logs' => "INSERT INTO configuracion_mantenimiento_logs (clave, valor_anterior, valor_nuevo,
            usuario_id, ip, user_agent, created_at)
        SELECT l.ConfigKey, l.ValorAnterior, l.ValorNuevo, u.id, l.IPAddress, l.UserAgent, COALESCE(l.Fecha, NOW())
        FROM {$L}.MaintenanceConfigLog l LEFT JOIN usuarios u ON u.id = l.UsuarioID",
];

// Tablas opcionales del sistema anterior (se crearon "a demanda" en el código; pueden no existir)
$existeLegacy = static function (string $tabla) use ($pdo, $legacy): bool {
    $st = $pdo->prepare('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?');
    $st->execute([$legacy, $tabla]);
    return (int) $st->fetchColumn() > 0;
};
if ($existeLegacy('Logs_precio')) {
    $pasos['precio_logs'] = "INSERT INTO precio_logs (producto_id, precio_antes, precio_despues, usuario_id, created_at)
        SELECT p.id, l.PrecioBefore, l.PrecioAfter, u.id, COALESCE(l.FechaCambio, NOW())
        FROM {$L}.Logs_precio l JOIN productos p ON p.upc {$C} = l.UPC {$C}
        LEFT JOIN usuarios u ON u.id = l.UsuarioID";
}
if ($existeLegacy('user_notifications')) {
    $pasos['notificaciones'] = "INSERT INTO notificaciones (usuario_id, titulo, mensaje, tipo, enviado_por, leida_at, expira_at, created_at)
        SELECT n.usuario_id, n.titulo, n.mensaje, n.tipo, e.id, n.fecha_leido, n.fecha_expira, COALESCE(n.fecha_creacion, NOW())
        FROM {$L}.user_notifications n JOIN usuarios u ON u.id = n.usuario_id LEFT JOIN usuarios e ON e.id = n.enviado_por";
}
if ($existeLegacy('MaintenanceAccessLog')) {
    $pasos['mantenimiento_accesos_logs'] = "INSERT INTO mantenimiento_accesos_logs (usuario_id, pagina, razon, detalles_razon, ip, user_agent)
        SELECT u.id, l.Pagina, l.Razon, l.DetallesRazon, l.IPAddress, l.UserAgent
        FROM {$L}.MaintenanceAccessLog l LEFT JOIN usuarios u ON u.id = l.UsuarioID";
}

$pdo->beginTransaction();
try {
    foreach (glob(__DIR__ . '/seeds/*.sql') ?: [] as $seed) {
        $pdo->exec((string) file_get_contents($seed));
    }

    foreach ($pasos as $nombre => $sql) {
        $filas = $pdo->exec($sql);
        printf("%-36s %6d filas\n", $nombre, (int) $filas);
    }

    // Contraseñas que no sean hash válido (texto plano heredado) se re-hashean
    $planas = $pdo->query("SELECT id, password_hash FROM usuarios WHERE password_hash NOT LIKE '$%'")->fetchAll(PDO::FETCH_ASSOC);
    $upd = $pdo->prepare('UPDATE usuarios SET password_hash = ?, password_cambiada_at = NOW() WHERE id = ?');
    foreach ($planas as $u) {
        $upd->execute([password_hash((string) $u['password_hash'], PASSWORD_DEFAULT), $u['id']]);
    }
    printf("%-36s %6d filas\n", 'usuarios (password re-hasheada)', count($planas));

    // Tags escritos como texto libre en Productos.Tag ("a, b; c") -> tags + producto_tags
    $buscarTag = $pdo->prepare('SELECT id FROM tags WHERE nombre = ?');
    $crearTag = $pdo->prepare('INSERT INTO tags (nombre) VALUES (?)');
    $asignar = $pdo->prepare('INSERT IGNORE INTO producto_tags (producto_id, tag_id) VALUES (?, ?)');
    $nuevos = 0;
    $filas = $pdo->query("SELECT ProductoID, Tag FROM {$L}.Productos WHERE Tag IS NOT NULL AND TRIM(Tag) <> ''")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($filas as $fila) {
        foreach (preg_split('/[,;|]+/', (string) $fila['Tag']) ?: [] as $nombreTag) {
            $nombreTag = trim($nombreTag);
            if ($nombreTag === '') {
                continue;
            }
            $buscarTag->execute([$nombreTag]);
            $tagId = $buscarTag->fetchColumn();
            if (!$tagId) {
                $crearTag->execute([mb_substr($nombreTag, 0, 80)]);
                $tagId = (int) $pdo->lastInsertId();
                $nuevos++;
            }
            $asignar->execute([$fila['ProductoID'], $tagId]);
        }
    }
    printf("%-36s %6d tags nuevos\n", 'productos.Tag (texto) -> tags', $nuevos);

    // Reporte de integridad: filas de origen que no pudieron migrarse (huérfanas)
    echo PHP_EOL . "Reporte de conteos (origen -> destino):" . PHP_EOL;
    $pares = [
        ['Usuarios', 'usuarios'], ['Categorias', 'categorias'], ['Sucursales', 'sucursales'], ['Tags', 'tags'],
        ['Productos', 'productos'], ['ProductoTags', 'producto_tags'], ['MovimientosInventario', 'movimientos_inventario'],
        ['CarritoSolicitudes', 'carrito_items'], ['Logs_stock', 'stock_logs'], ['PickingLog', 'picking_logs'],
        ['EstadosComanda', 'comanda_estado_historial'],
    ];
    foreach ($pares as [$origen, $destino]) {
        $a = (int) $pdo->query("SELECT COUNT(*) FROM {$L}.`{$origen}`")->fetchColumn();
        $b = (int) $pdo->query("SELECT COUNT(*) FROM `{$destino}`")->fetchColumn();
        printf("  %-24s %6d -> %-26s %6d %s\n", $origen, $a, $destino, $b, $a === $b ? '' : '(revisar)');
    }
    echo '  (categorias duplicadas por nombre se fusionan; filas huérfanas o con cantidad <= 0 se omiten)' . PHP_EOL;

    if ($dryRun) {
        $pdo->rollBack();
        echo PHP_EOL . 'DRY-RUN: no se escribió ningún dato.' . PHP_EOL;
    } else {
        $pdo->commit();
        echo PHP_EOL . 'Migración completada.' . PHP_EOL;
    }
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, PHP_EOL . 'ERROR (se revirtió todo): ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
