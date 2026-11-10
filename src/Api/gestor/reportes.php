<?php
header('Content-Type: application/json; charset=utf-8');
require_once BASE_PATH . '/src/Helpers/legacy.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$fecha = static function (string $clave, string $porDefecto): string {
    $v = (string) ($_GET[$clave] ?? '');
    $d = DateTime::createFromFormat('Y-m-d', $v);
    return $d && $d->format('Y-m-d') === $v ? $v : $porDefecto;
};
$desde = $fecha('desde', date('Y-m-01'));
$hasta = $fecha('hasta', date('Y-m-d'));
if ($desde > $hasta) {
    [$desde, $hasta] = [$hasta, $desde];
}
if ((strtotime($hasta) - strtotime($desde)) / 86400 > 731) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'El rango máximo es de 2 años']);
    exit;
}

$db = conexion();
$p = [':d' => $desde . ' 00:00:00', ':h' => $hasta . ' 23:59:59'];
$base = "m.TipoMovimiento = 'Salida' AND m.ComandaID IS NOT NULL AND m.FechaMovimiento BETWEEN :d AND :h";
$validos = "$base AND m.Status <> 'Cancelado'";

$consulta = static function (string $sql) use ($db, $p): array {
    $st = $db->prepare($sql);
    $st->execute($p);
    return $st->fetchAll(PDO::FETCH_ASSOC);
};

$kpis = $consulta(
    "SELECT COUNT(DISTINCT m.ComandaID) AS pedidos, COALESCE(SUM(m.PrecioFinal), 0) AS monto,
            COUNT(*) AS partidas, COUNT(DISTINCT m.SucursalID) AS sucursales
     FROM MovimientosInventario m WHERE $validos"
)[0];
$kpis['ticket'] = $kpis['pedidos'] > 0 ? round($kpis['monto'] / $kpis['pedidos'], 2) : 0;
$canc = $consulta(
    "SELECT COUNT(DISTINCT m.ComandaID) AS pedidos, COALESCE(SUM(m.PrecioFinal), 0) AS monto
     FROM MovimientosInventario m WHERE $base AND m.Status = 'Cancelado'"
)[0];
$kpis['cancelados'] = (int) $canc['pedidos'];
$kpis['monto_cancelado'] = (float) $canc['monto'];

echo json_encode([
    'success' => true,
    'desde' => $desde,
    'hasta' => $hasta,
    'kpis' => $kpis,
    'dias' => $consulta(
        "SELECT DATE(m.FechaMovimiento) AS dia, COUNT(DISTINCT m.ComandaID) AS pedidos, SUM(m.PrecioFinal) AS monto
         FROM MovimientosInventario m WHERE $validos GROUP BY DATE(m.FechaMovimiento) ORDER BY dia"
    ),
    'sucursales' => $consulta(
        "SELECT COALESCE(s.nombre, 'Sin sucursal') AS nombre, COUNT(DISTINCT m.ComandaID) AS pedidos, SUM(m.PrecioFinal) AS monto
         FROM MovimientosInventario m LEFT JOIN Sucursales s ON s.SucursalID = m.SucursalID
         WHERE $validos GROUP BY s.SucursalID, s.nombre ORDER BY monto DESC"
    ),
    'solicitantes' => $consulta(
        "SELECT COALESCE(u.Nombre, 'Desconocido') AS nombre, COUNT(DISTINCT m.ComandaID) AS pedidos, SUM(m.PrecioFinal) AS monto
         FROM MovimientosInventario m LEFT JOIN Usuarios u ON u.UsuarioID = m.UsuarioID
         WHERE $validos GROUP BY m.UsuarioID, u.Nombre ORDER BY monto DESC LIMIT 15"
    ),
    'categorias' => $consulta(
        "SELECT COALESCE(c.Nombre, 'Sin categoría') AS nombre, SUM(m.PrecioFinal) AS monto
         FROM MovimientosInventario m INNER JOIN Productos pr ON pr.ProductoID = m.ProductoID
         LEFT JOIN Categorias c ON c.CategoriaID = pr.CategoriaID
         WHERE $validos GROUP BY c.CategoriaID, c.Nombre ORDER BY monto DESC"
    ),
    'productos' => $consulta(
        "SELECT pr.Nombre, pr.UPC, pr.Tipo, SUM(m.Cantidad) AS cantidad, SUM(m.PrecioFinal) AS monto, COUNT(DISTINCT m.ComandaID) AS pedidos
         FROM MovimientosInventario m INNER JOIN Productos pr ON pr.ProductoID = m.ProductoID
         WHERE $validos GROUP BY pr.ProductoID, pr.Nombre, pr.UPC, pr.Tipo ORDER BY monto DESC LIMIT 25"
    ),
], JSON_UNESCAPED_UNICODE);
