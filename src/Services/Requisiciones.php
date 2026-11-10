<?php

declare(strict_types=1);

namespace App\Services;

use PDO;

/**
 * Requisiciones de compra: el Supervisor envía su lista, Logística la aprueba y el almacén
 * captura lo que realmente llega (peso/cantidad), lo que da entrada al inventario.
 */
final class Requisiciones
{
    public const MAX_LINEAS = 100;

    private const EPS = 0.0005;

    /** Unidades escritas por el usuario -> [tipo, factor a la unidad base]. */
    private const UNIDADES = [
        'kg' => ['Pesable', 1.0], 'kgs' => ['Pesable', 1.0], 'kilo' => ['Pesable', 1.0], 'kilos' => ['Pesable', 1.0],
        'kilogramo' => ['Pesable', 1.0], 'kilogramos' => ['Pesable', 1.0],
        'g' => ['Pesable', 0.001], 'gr' => ['Pesable', 0.001], 'grs' => ['Pesable', 0.001], 'gramo' => ['Pesable', 0.001], 'gramos' => ['Pesable', 0.001],
        'l' => ['Litro', 1.0], 'lt' => ['Litro', 1.0], 'lts' => ['Litro', 1.0], 'litro' => ['Litro', 1.0], 'litros' => ['Litro', 1.0],
        'ml' => ['Litro', 0.001],
        'manojo' => ['Manojo', 1.0], 'manojos' => ['Manojo', 1.0],
        'pza' => ['Unidad', 1.0], 'pzas' => ['Unidad', 1.0], 'pz' => ['Unidad', 1.0], 'pieza' => ['Unidad', 1.0], 'piezas' => ['Unidad', 1.0],
        'unidad' => ['Unidad', 1.0], 'unidades' => ['Unidad', 1.0], 'u' => ['Unidad', 1.0],
    ];

    // ───────────────────────── Interpretación de texto libre ─────────────────────────

    public static function normalizar(string $s): string
    {
        $s = mb_strtolower(trim($s), 'UTF-8');
        $s = strtr($s, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n']);

        return trim((string) preg_replace('/[^a-z0-9]+/', ' ', $s));
    }

    /** Singular aproximado (lechugas -> lechuga, jitomates -> jitomate, limones -> limon). */
    public static function raiz(string $palabra): string
    {
        $n = strlen($palabra);
        if ($n > 4 && str_ends_with($palabra, 'es') && !preg_match('/[aeiou]es$/', $palabra)) {
            return substr($palabra, 0, -2);
        }
        if ($n > 3 && str_ends_with($palabra, 's')) {
            return substr($palabra, 0, -1);
        }

        return $palabra;
    }

    /** @return list<string> */
    public static function dividir(string $texto): array
    {
        $partes = preg_split('/\R|;|(?<!\d),|,(?!\d)/u', $texto) ?: [];
        $partes = array_map(static fn ($p) => trim((string) $p, " \t-•*"), $partes);

        return array_values(array_filter($partes, static fn ($p) => $p !== ''));
    }

    /** @return array{cantidad:float,tipo:?string,nombre:string}|null */
    public static function interpretarLinea(string $linea): ?array
    {
        $linea = trim($linea);
        $unidades = implode('|', array_keys(self::UNIDADES));
        $num = '(\d+\/\d+|\d+(?:[.,]\d+)?|medio|media)';
        $unidad = '(?:(' . $unidades . ')(?![\p{L}\d]))?';

        $m = null;
        if (preg_match('/^' . $num . '\s*(?:x\s*)?' . $unidad . '\.?\s*(?:de\s+)?(.+)$/iu', $linea, $m)) {
            [$cant, $uni, $nombre] = [$m[1], $m[2] ?? '', $m[3]];
        } elseif (preg_match('/^(.+?)\s*(?:x\s*)?' . $num . '\s*' . $unidad . '\.?$/iu', $linea, $m)) {
            [$nombre, $cant, $uni] = [$m[1], $m[2], $m[3] ?? ''];
        } else {
            return null;
        }

        $cant = mb_strtolower($cant, 'UTF-8');
        if ($cant === 'medio' || $cant === 'media') {
            $valor = 0.5;
        } elseif (str_contains($cant, '/')) {
            [$a, $b] = array_map('floatval', explode('/', $cant, 2));
            $valor = $b > 0 ? $a / $b : 0.0;
        } else {
            $valor = (float) str_replace(',', '.', $cant);
        }

        $tipo = null;
        if ($uni !== '') {
            [$tipo, $factor] = self::UNIDADES[mb_strtolower($uni, 'UTF-8')];
            $valor *= $factor;
        }
        $nombre = trim((string) preg_replace('/^(?:de|del)\s+/iu', '', trim($nombre)), " .");
        if ($valor <= 0 || $nombre === '') {
            return null;
        }

        return ['cantidad' => round($valor, 3), 'tipo' => $tipo, 'nombre' => $nombre];
    }

    /** Qué tan bien coincide un producto con lo escrito: 0 exacto, 1 palabras completas, 2 empieza con, 3 contiene, 9 no. */
    public static function puntuar(string $producto, string $consulta): int
    {
        $p = self::normalizar($producto);
        $c = self::normalizar($consulta);
        if ($c === '') {
            return 9;
        }
        $raicesP = array_map([self::class, 'raiz'], explode(' ', $p));
        $raicesC = array_map([self::class, 'raiz'], explode(' ', $c));
        if ($raicesP === $raicesC) {
            return 0;
        }
        if (!array_diff($raicesC, $raicesP)) {
            return 1;
        }
        if (str_starts_with($p, $c)) {
            return 2;
        }

        return str_contains($p, $c) || str_contains($p, implode(' ', $raicesC)) ? 3 : 9;
    }

    /**
     * Convierte el texto del supervisor en líneas propuestas con productos candidatos del catálogo.
     *
     * @return list<array<string,mixed>>
     */
    public static function parsear(PDO $db, string $texto): array
    {
        $texto = mb_substr($texto, 0, 5000);
        $salida = [];
        foreach (self::dividir($texto) as $original) {
            if (count($salida) >= self::MAX_LINEAS) {
                break;
            }
            $l = self::interpretarLinea($original);
            if ($l === null) {
                $salida[] = ['original' => $original, 'valida' => false, 'mensaje' => 'No se entendió la cantidad. Ejemplo: "5 kg de queso".'];
                continue;
            }

            $tokens = array_filter(explode(' ', self::normalizar($l['nombre'])), static fn ($t) => $t !== '');
            $cands = [];
            if ($tokens) {
                $sql = 'SELECT ProductoID, Nombre, Tipo, Cantidad FROM Productos WHERE Activo = 1';
                $params = [];
                foreach (array_values($tokens) as $i => $t) {
                    $sql .= " AND Nombre LIKE :t{$i}";
                    $params[":t{$i}"] = '%' . addcslashes(self::raiz($t), '%_\\') . '%';
                }
                $st = $db->prepare($sql . ' LIMIT 60');
                $st->execute($params);
                foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $p) {
                    $score = self::puntuar($p['Nombre'], $l['nombre']);
                    if ($score < 9) {
                        $cands[] = ['id' => (int) $p['ProductoID'], 'nombre' => $p['Nombre'], 'tipo' => $p['Tipo'], 'stock' => (float) $p['Cantidad'], 'score' => $score];
                    }
                }
                usort($cands, static fn ($a, $b) => [$a['score'], strlen($a['nombre'])] <=> [$b['score'], strlen($b['nombre'])]);
                $cands = array_slice($cands, 0, 6);
            }

            // Se preselecciona solo cuando la coincidencia es clara y no hay otra igual de buena.
            $sel = null;
            if ($cands && $cands[0]['score'] <= 1 && (count($cands) === 1 || $cands[1]['score'] > $cands[0]['score'])) {
                $sel = $cands[0]['id'];
            }
            $salida[] = [
                'original' => $original, 'valida' => true, 'descripcion' => mb_convert_case($l['nombre'], MB_CASE_TITLE, 'UTF-8'),
                'cantidad' => $l['cantidad'], 'tipo' => $l['tipo'] ?? 'Unidad', 'tipo_explicito' => $l['tipo'] !== null,
                'candidatos' => $cands, 'producto_id' => $sel,
            ];
        }

        return $salida;
    }

    // ───────────────────────── Operaciones ─────────────────────────

    /**
     * @param array<string,mixed> $in
     * @return array{id:int,folio:string}
     */
    public static function crear(PDO $db, int $usuarioId, array $in): array
    {
        $lineas = $in['lineas'] ?? null;
        if (!is_array($lineas) || !$lineas) {
            throw new RequisicionException('Agrega al menos un producto a la lista.');
        }
        if (count($lineas) > self::MAX_LINEAS) {
            throw new RequisicionException('Máximo ' . self::MAX_LINEAS . ' productos por requisición.');
        }

        $sucursal = (int) ($in['sucursal_id'] ?? 0) ?: null;
        if ($sucursal !== null) {
            $st = $db->prepare('SELECT 1 FROM Sucursales WHERE SucursalID = ?');
            $st->execute([$sucursal]);
            if (!$st->fetchColumn()) {
                throw new RequisicionException('Sucursal no encontrada.');
            }
        }
        $fecha = trim((string) ($in['fecha_requerida'] ?? '')) ?: null;
        if ($fecha !== null) {
            $d = \DateTime::createFromFormat('Y-m-d', $fecha);
            if (!$d || $d->format('Y-m-d') !== $fecha || $fecha < date('Y-m-d')) {
                throw new RequisicionException('La fecha requerida no es válida.');
            }
        }
        $notas = mb_substr(trim((string) ($in['notas'] ?? '')), 0, 500) ?: null;

        $prep = [];
        $vistos = [];
        foreach ($lineas as $i => $l) {
            $n = $i + 1;
            $pid = (int) ($l['producto_id'] ?? 0) ?: null;
            $tipo = (string) ($l['tipo'] ?? 'Unidad');
            $desc = trim((string) ($l['descripcion'] ?? ''));
            if ($pid !== null) {
                $st = $db->prepare('SELECT Nombre, Tipo FROM Productos WHERE ProductoID = ? AND Activo = 1');
                $st->execute([$pid]);
                $p = $st->fetch(PDO::FETCH_ASSOC);
                if (!$p) {
                    throw new RequisicionException("Línea {$n}: el producto ya no está disponible.");
                }
                [$tipo, $desc] = [$p['Tipo'], $p['Nombre']];
            } elseif ($desc === '' || mb_strlen($desc) > 150) {
                throw new RequisicionException("Línea {$n}: escribe el nombre del producto (máx. 150 caracteres).");
            } elseif (!Unidades::esValido($tipo)) {
                $tipo = 'Unidad';
            }
            [$cant, $err] = Unidades::validar($tipo, $l['cantidad'] ?? null);
            if ($cant === null) {
                throw new RequisicionException("Línea {$n} ({$desc}): {$err}");
            }
            if ($pid !== null && isset($vistos[$pid])) {
                $prep[$vistos[$pid]]['cantidad'] += $cant;
                continue;
            }
            $vistos[$pid ?? 'x' . $n] = count($prep);
            $prep[] = ['pid' => $pid, 'desc' => $desc, 'tipo' => $tipo, 'cantidad' => $cant];
        }

        $db->beginTransaction();
        try {
            $db->prepare('INSERT INTO Requisiciones (Folio, SolicitanteID, SucursalID, Notas, FechaRequerida) VALUES (?, ?, ?, ?, ?)')
                ->execute([bin2hex(random_bytes(8)), $usuarioId, $sucursal, $notas, $fecha]);
            $id = (int) $db->lastInsertId();
            $folio = sprintf('REQ-%s-%04d', date('ymd'), $id);
            $db->prepare('UPDATE Requisiciones SET Folio = ? WHERE RequisicionID = ?')->execute([$folio, $id]);
            $ins = $db->prepare('INSERT INTO RequisicionLineas (RequisicionID, ProductoID, Descripcion, Tipo, CantidadSolicitada) VALUES (?, ?, ?, ?, ?)');
            foreach ($prep as $l) {
                $ins->execute([$id, $l['pid'], $l['desc'], $l['tipo'], $l['cantidad']]);
            }
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }

        return ['id' => $id, 'folio' => $folio];
    }

    /** @return list<array<string,mixed>> */
    public static function listar(PDO $db, ?int $solicitanteId, string $estado = '', string $q = '', int $limite = 100): array
    {
        $where = ['1=1'];
        $p = [];
        if ($solicitanteId !== null) {
            $where[] = 'r.SolicitanteID = :u';
            $p[':u'] = $solicitanteId;
        }
        if ($estado !== '') {
            $where[] = 'r.Estado = :e';
            $p[':e'] = $estado;
        }
        if ($q !== '') {
            $where[] = '(r.Folio LIKE :q OR u.Nombre LIKE :q OR s.nombre LIKE :q)';
            $p[':q'] = '%' . addcslashes($q, '%_\\') . '%';
        }
        $st = $db->prepare(
            'SELECT r.RequisicionID, r.Folio, r.Estado, r.FechaCreacion, r.FechaRequerida, r.Proveedor,
                    COALESCE(u.Nombre, \'-\') AS solicitante, s.nombre AS sucursal,
                    (SELECT COUNT(*) FROM RequisicionLineas l WHERE l.RequisicionID = r.RequisicionID) AS lineas,
                    (SELECT COUNT(*) FROM RequisicionLineas l WHERE l.RequisicionID = r.RequisicionID AND l.CantidadRecibida > 0) AS lineas_recibidas
             FROM Requisiciones r
             LEFT JOIN Usuarios u ON u.UsuarioID = r.SolicitanteID
             LEFT JOIN Sucursales s ON s.SucursalID = r.SucursalID
             WHERE ' . implode(' AND ', $where) . '
             ORDER BY r.RequisicionID DESC LIMIT ' . max(1, min(300, $limite))
        );
        $st->execute($p);

        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @return array<string,mixed>|null */
    public static function detalle(PDO $db, int $id): ?array
    {
        $st = $db->prepare(
            'SELECT r.*, COALESCE(u.Nombre, \'-\') AS solicitante, s.nombre AS sucursal, a.Nombre AS aprobador
             FROM Requisiciones r
             LEFT JOIN Usuarios u ON u.UsuarioID = r.SolicitanteID
             LEFT JOIN Usuarios a ON a.UsuarioID = r.AprobadoPor
             LEFT JOIN Sucursales s ON s.SucursalID = r.SucursalID
             WHERE r.RequisicionID = ?'
        );
        $st->execute([$id]);
        $r = $st->fetch(PDO::FETCH_ASSOC);
        if (!$r) {
            return null;
        }
        $st = $db->prepare(
            'SELECT l.*, p.Cantidad AS stock, p.CostoUltimo, p.UPC
             FROM RequisicionLineas l LEFT JOIN Productos p ON p.ProductoID = l.ProductoID
             WHERE l.RequisicionID = ? ORDER BY l.LineaID'
        );
        $st->execute([$id]);
        $r['lineas'] = $st->fetchAll(PDO::FETCH_ASSOC);

        return $r;
    }

    /** @param list<array<string,mixed>> $lineas [{id, cantidad_aprobada}] (omitir = igual a lo solicitado) */
    public static function aprobar(PDO $db, int $id, int $usuarioId, ?string $proveedor, array $lineas): void
    {
        $db->beginTransaction();
        try {
            $r = self::bloquear($db, $id, ['Enviada'], 'Solo se pueden aprobar requisiciones enviadas.');
            $st = $db->prepare('SELECT LineaID, Descripcion, Tipo, CantidadSolicitada FROM RequisicionLineas WHERE RequisicionID = ?');
            $st->execute([$id]);
            $actuales = $st->fetchAll(PDO::FETCH_ASSOC);

            $ajustes = [];
            foreach ($lineas as $l) {
                $ajustes[(int) ($l['id'] ?? 0)] = $l['cantidad_aprobada'] ?? null;
            }
            $upd = $db->prepare('UPDATE RequisicionLineas SET CantidadAprobada = ? WHERE LineaID = ?');
            $total = 0.0;
            foreach ($actuales as $l) {
                $valor = array_key_exists((int) $l['LineaID'], $ajustes) ? $ajustes[(int) $l['LineaID']] : $l['CantidadSolicitada'];
                if ($valor === null || $valor === '' || (is_numeric($valor) && (float) $valor == 0.0)) {
                    $cant = 0.0;
                } else {
                    [$cant, $err] = Unidades::validar($l['Tipo'], $valor);
                    if ($cant === null) {
                        throw new RequisicionException("{$l['Descripcion']}: {$err}");
                    }
                }
                $total += $cant;
                $upd->execute([$cant, $l['LineaID']]);
            }
            if ($total <= 0) {
                throw new RequisicionException('Aprueba al menos un producto; si no procede, usa "Rechazar".');
            }
            $db->prepare("UPDATE Requisiciones SET Estado = 'Aprobada', Proveedor = ?, AprobadoPor = ?, FechaAprobacion = NOW() WHERE RequisicionID = ?")
                ->execute([mb_substr(trim((string) $proveedor), 0, 255) ?: null, $usuarioId, $r['RequisicionID']]);
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function rechazar(PDO $db, int $id, string $motivo): void
    {
        $motivo = mb_substr(trim($motivo), 0, 255);
        if ($motivo === '') {
            throw new RequisicionException('Escribe el motivo del rechazo.');
        }
        $db->beginTransaction();
        try {
            self::bloquear($db, $id, ['Enviada'], 'Solo se pueden rechazar requisiciones enviadas.');
            $db->prepare("UPDATE Requisiciones SET Estado = 'Rechazada', MotivoRechazo = ?, FechaCierre = NOW() WHERE RequisicionID = ?")->execute([$motivo, $id]);
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /** El supervisor cancela la suya mientras está Enviada; el Gestor también si está Aprobada sin recepciones. */
    public static function cancelar(PDO $db, int $id, ?int $soloDeUsuario): void
    {
        $db->beginTransaction();
        try {
            $r = self::bloquear($db, $id, ['Enviada', 'Aprobada'], 'Esta requisición ya no se puede cancelar.');
            if ($soloDeUsuario !== null && ((int) $r['SolicitanteID'] !== $soloDeUsuario || $r['Estado'] !== 'Enviada')) {
                throw new RequisicionException('Solo puedes cancelar tus requisiciones que aún no se aprueban.', 403);
            }
            $db->prepare("UPDATE Requisiciones SET Estado = 'Cancelada', FechaCierre = NOW() WHERE RequisicionID = ?")->execute([$id]);
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function vincular(PDO $db, int $lineaId, int $productoId): void
    {
        $db->beginTransaction();
        try {
            $st = $db->prepare('SELECT RequisicionID, CantidadSolicitada, CantidadAprobada, Descripcion FROM RequisicionLineas WHERE LineaID = ? FOR UPDATE');
            $st->execute([$lineaId]);
            $l = $st->fetch(PDO::FETCH_ASSOC);
            if (!$l) {
                throw new RequisicionException('Línea no encontrada.', 404);
            }
            self::bloquear($db, (int) $l['RequisicionID'], ['Enviada', 'Aprobada', 'Parcial'], 'La requisición ya está cerrada.');
            $st = $db->prepare('SELECT Tipo FROM Productos WHERE ProductoID = ? AND Activo = 1');
            $st->execute([$productoId]);
            $tipo = $st->fetchColumn();
            if (!$tipo) {
                throw new RequisicionException('Producto no encontrado.', 404);
            }
            if (Unidades::esEntero($tipo)) {
                foreach ([$l['CantidadSolicitada'], $l['CantidadAprobada']] as $c) {
                    if ($c !== null && floor((float) $c) != (float) $c) {
                        throw new RequisicionException("{$l['Descripcion']}: ese producto solo admite cantidades enteras.");
                    }
                }
            }
            $db->prepare('UPDATE RequisicionLineas SET ProductoID = ?, Tipo = ? WHERE LineaID = ?')->execute([$productoId, $tipo, $lineaId]);
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Registra lo que realmente llegó: da entrada al inventario, guarda el costo y deja bitácora.
     *
     * @param list<array<string,mixed>> $lineas [{id, cantidad, costo?}]
     */
    public static function recibir(PDO $db, int $id, int $usuarioId, array $lineas): string
    {
        $db->beginTransaction();
        try {
            self::bloquear($db, $id, ['Aprobada', 'Parcial'], 'Solo se recibe mercancía de requisiciones aprobadas.');
            $capturadas = 0;
            foreach ($lineas as $in) {
                if (!isset($in['cantidad']) || $in['cantidad'] === '' || (is_numeric($in['cantidad']) && (float) $in['cantidad'] == 0.0)) {
                    continue;
                }
                $st = $db->prepare('SELECT * FROM RequisicionLineas WHERE LineaID = ? AND RequisicionID = ? FOR UPDATE');
                $st->execute([(int) ($in['id'] ?? 0), $id]);
                $l = $st->fetch(PDO::FETCH_ASSOC);
                if (!$l) {
                    throw new RequisicionException('Línea no encontrada.', 404);
                }
                if ($l['CantidadAprobada'] === null || (float) $l['CantidadAprobada'] <= 0) {
                    throw new RequisicionException("{$l['Descripcion']}: no fue aprobado en esta requisición.");
                }
                if (!$l['ProductoID']) {
                    throw new RequisicionException("{$l['Descripcion']}: vincula primero un producto del catálogo.");
                }
                [$cant, $err] = Unidades::validar($l['Tipo'], $in['cantidad'], 1000000.0);
                if ($cant === null) {
                    throw new RequisicionException("{$l['Descripcion']}: {$err}");
                }
                $costo = $in['costo'] ?? null;
                if ($costo === null || $costo === '') {
                    $costo = null;
                } elseif (!is_numeric($costo) || (float) $costo < 0 || (float) $costo > 100000000) {
                    throw new RequisicionException("{$l['Descripcion']}: costo inválido.");
                } else {
                    $costo = round((float) $costo, 2);
                }

                $st = $db->prepare('SELECT UPC, Cantidad FROM Productos WHERE ProductoID = ? FOR UPDATE');
                $st->execute([$l['ProductoID']]);
                $p = $st->fetch(PDO::FETCH_ASSOC);
                if (!$p) {
                    throw new RequisicionException("{$l['Descripcion']}: el producto ya no existe.", 404);
                }
                $antes = (float) $p['Cantidad'];
                $db->prepare('UPDATE Productos SET Cantidad = ?, CostoUltimo = COALESCE(?, CostoUltimo), FechaModificacion = NOW() WHERE ProductoID = ?')
                    ->execute([$antes + $cant, $costo, $l['ProductoID']]);
                $db->prepare("INSERT INTO MovimientosInventario (ComandaID, ProductoID, TipoMovimiento, Cantidad, FechaMovimiento, UsuarioID, Status) VALUES (0, ?, 'Entrada', ?, NOW(), ?, 'Cerrado')")
                    ->execute([$l['ProductoID'], $cant, $usuarioId]);
                $movId = (int) $db->lastInsertId();
                $db->prepare('INSERT INTO Logs_stock (UPC, StockBefore, StockAfter, UsuarioID, Fecha) VALUES (?, ?, ?, ?, NOW())')
                    ->execute([$p['UPC'], $antes, $antes + $cant, $usuarioId]);
                $db->prepare('INSERT INTO RequisicionRecepciones (LineaID, ProductoID, Cantidad, CostoUnitario, UsuarioID, MovimientoID) VALUES (?, ?, ?, ?, ?, ?)')
                    ->execute([$l['LineaID'], $l['ProductoID'], $cant, $costo, $usuarioId, $movId]);
                $db->prepare('UPDATE RequisicionLineas SET CantidadRecibida = CantidadRecibida + ?, CostoUnitario = COALESCE(?, CostoUnitario) WHERE LineaID = ?')
                    ->execute([$cant, $costo, $l['LineaID']]);
                $capturadas++;
            }
            if ($capturadas === 0) {
                throw new RequisicionException('Captura la cantidad recibida de al menos un producto.');
            }

            $st = $db->prepare('SELECT COUNT(*) FROM RequisicionLineas WHERE RequisicionID = ? AND CantidadAprobada > 0 AND CantidadRecibida + ? < CantidadAprobada');
            $st->execute([$id, self::EPS]);
            $pendientes = (int) $st->fetchColumn();
            $estado = $pendientes === 0 ? 'Recibida' : 'Parcial';
            $db->prepare('UPDATE Requisiciones SET Estado = ?, FechaCierre = ' . ($pendientes === 0 ? 'NOW()' : 'NULL') . ' WHERE RequisicionID = ?')
                ->execute([$estado, $id]);
            $db->commit();

            return $estado;
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /** Cierra una requisición parcial dando por completos los faltantes. */
    public static function cerrar(PDO $db, int $id): void
    {
        $db->beginTransaction();
        try {
            self::bloquear($db, $id, ['Parcial'], 'Solo se cierran requisiciones con recepción parcial.');
            $db->prepare("UPDATE Requisiciones SET Estado = 'Recibida', FechaCierre = NOW() WHERE RequisicionID = ?")->execute([$id]);
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * @param list<string> $estados permitidos
     * @return array<string,mixed>
     */
    private static function bloquear(PDO $db, int $id, array $estados, string $mensaje): array
    {
        $st = $db->prepare('SELECT * FROM Requisiciones WHERE RequisicionID = ? FOR UPDATE');
        $st->execute([$id]);
        $r = $st->fetch(PDO::FETCH_ASSOC);
        if (!$r) {
            throw new RequisicionException('Requisición no encontrada.', 404);
        }
        if (!in_array($r['Estado'], $estados, true)) {
            throw new RequisicionException($mensaje, 409);
        }

        return $r;
    }
}
