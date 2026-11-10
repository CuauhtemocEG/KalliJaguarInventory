<?php

declare(strict_types=1);

namespace App\Services;

use PDO;

/** Reglas de negocio del carrito y de la confirmación de solicitudes de insumos. */
final class Solicitud
{
    private const MAX_DIAS_ENTREGA = 60;

    /**
     * Líneas del carrito con datos vigentes del producto (precio, stock, estado) y un
     * campo "Problema" cuando la línea ya no se puede surtir tal cual.
     *
     * @return list<array<string,mixed>>
     */
    public static function carrito(PDO $db, int $usuarioId, bool $bloquear = false): array
    {
        $stmt = $db->prepare(
            'SELECT c.ProductoID, c.Cantidad, p.PrecioUnitario, p.Nombre AS NombreProducto, p.Tipo,
                    p.image AS Imagen, p.Cantidad AS Stock, COALESCE(p.Activo, 1) AS Activo,
                    (p.ProductoID IS NOT NULL) AS Existe
             FROM CarritoSolicitudes c
             LEFT JOIN Productos p ON p.ProductoID = c.ProductoID
             WHERE c.UsuarioID = :u
             ORDER BY c.ProductoID' . ($bloquear ? ' FOR UPDATE' : '')
        );
        $stmt->execute([':u' => $usuarioId]);

        $lineas = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $l) {
            $l['Problema'] = self::problema($l);
            $lineas[] = $l;
        }

        return $lineas;
    }

    /** @param array<string,mixed> $l */
    private static function problema(array $l): ?string
    {
        if (!(int) $l['Existe'] || !(int) $l['Activo']) {
            return 'Producto no disponible';
        }
        $stock = (float) $l['Stock'];
        $cant = (float) $l['Cantidad'];
        $tipo = (string) $l['Tipo'];
        if ($stock <= 0) {
            return 'Sin stock';
        }
        if ($cant > $stock + 0.0001) {
            return 'Solo hay ' . Unidades::formato($tipo, $stock) . ' disponibles';
        }
        [, $error] = Unidades::validar($tipo, $cant);

        return $error;
    }

    /**
     * Fija la cantidad de un producto en el carrito. Lanza InvalidArgumentException con
     * un mensaje apto para el usuario si no se puede.
     */
    public static function fijarCantidad(PDO $db, int $usuarioId, int $productoId, mixed $cantidad): void
    {
        $stmt = $db->prepare('SELECT Nombre, Tipo, Cantidad, PrecioUnitario, image, COALESCE(Activo, 1) AS Activo FROM Productos WHERE ProductoID = :id');
        $stmt->execute([':id' => $productoId]);
        $p = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$p || !(int) $p['Activo']) {
            throw new \InvalidArgumentException('El producto ya no está disponible');
        }
        $stock = (float) $p['Cantidad'];
        if ($stock <= 0) {
            throw new \InvalidArgumentException('"' . $p['Nombre'] . '" está agotado');
        }
        [$cant, $error] = Unidades::validar($p['Tipo'], $cantidad);
        if ($error !== null) {
            throw new \InvalidArgumentException($error);
        }
        if ($cant > $stock + 0.0001) {
            throw new \InvalidArgumentException('Solo hay ' . Unidades::formato($p['Tipo'], $stock) . ' disponibles de "' . $p['Nombre'] . '"');
        }

        $db->prepare(
            'REPLACE INTO CarritoSolicitudes (UsuarioID, ProductoID, Cantidad, PrecioUnitario, NombreProducto, Tipo, Imagen)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        )->execute([$usuarioId, $productoId, $cant, $p['PrecioUnitario'], $p['Nombre'], $p['Tipo'], $p['image']]);
    }

    /**
     * Crea la solicitud a partir del carrito en una sola transacción con bloqueo de
     * productos. Devuelve el folio o lanza InvalidArgumentException.
     */
    public static function confirmar(PDO $db, int $usuarioId, int $sucursalId, string $fechaEntrega, float $multiplicadorIva): string
    {
        $fecha = \DateTime::createFromFormat('Y-m-d', $fechaEntrega);
        if (!$fecha || $fecha->format('Y-m-d') !== $fechaEntrega) {
            throw new \InvalidArgumentException('La fecha de entrega no es válida');
        }
        $hoy = new \DateTime('today');
        if ($fecha < $hoy) {
            throw new \InvalidArgumentException('La fecha de entrega no puede ser anterior a hoy');
        }
        if ($fecha > (clone $hoy)->modify('+' . self::MAX_DIAS_ENTREGA . ' days')) {
            throw new \InvalidArgumentException('La fecha de entrega no puede superar ' . self::MAX_DIAS_ENTREGA . ' días');
        }

        $db->beginTransaction();
        try {
            $u = $db->prepare('SELECT 1 FROM Usuarios WHERE UsuarioID = ?');
            $u->execute([$usuarioId]);
            if (!$u->fetchColumn()) {
                throw new \InvalidArgumentException('Tu usuario ya no existe');
            }

            $s = $db->prepare("SELECT estado FROM Sucursales WHERE SucursalID = ?");
            $s->execute([$sucursalId]);
            $estado = $s->fetchColumn();
            if ($estado === false) {
                throw new \InvalidArgumentException('La sucursal no existe');
            }
            if (in_array(strtolower((string) $estado), ['inactiva', 'inactivo'], true)) {
                throw new \InvalidArgumentException('La sucursal seleccionada está inactiva');
            }

            // Bloquear el carrito evita que un doble clic genere dos solicitudes
            $carrito = self::carrito($db, $usuarioId, true);
            if (!$carrito) {
                throw new \InvalidArgumentException('No hay productos en el carrito');
            }

            // Bloqueo de productos en orden estable para evitar interbloqueos
            $ids = array_column($carrito, 'ProductoID');
            $in = implode(',', array_fill(0, count($ids), '?'));
            $lock = $db->prepare("SELECT ProductoID, Nombre, Tipo, Cantidad, PrecioUnitario, COALESCE(Activo, 1) AS Activo FROM Productos WHERE ProductoID IN ($in) ORDER BY ProductoID FOR UPDATE");
            $lock->execute($ids);
            $productos = [];
            foreach ($lock->fetchAll(PDO::FETCH_ASSOC) as $p) {
                $productos[(int) $p['ProductoID']] = $p;
            }

            $problemas = [];
            foreach ($carrito as $l) {
                $p = $productos[(int) $l['ProductoID']] ?? null;
                $nombre = $p['Nombre'] ?? $l['NombreProducto'] ?? ('#' . $l['ProductoID']);
                if (!$p || !(int) $p['Activo']) {
                    $problemas[] = "\"$nombre\" ya no está disponible";
                    continue;
                }
                [$cant, $error] = Unidades::validar($p['Tipo'], $l['Cantidad']);
                if ($error !== null) {
                    $problemas[] = "\"$nombre\": $error";
                } elseif ($cant > (float) $p['Cantidad'] + 0.0001) {
                    $problemas[] = "\"$nombre\": solo hay " . Unidades::formato($p['Tipo'], (float) $p['Cantidad']) . ' y pediste ' . Unidades::formato($p['Tipo'], $cant);
                }
            }
            if ($problemas) {
                throw new \InvalidArgumentException('Revisa tu solicitud: ' . implode('; ', $problemas));
            }

            $comandaId = self::folio($db, $sucursalId);
            $ins = $db->prepare(
                "INSERT INTO MovimientosInventario (ComandaID, SucursalID, ProductoID, TipoMovimiento, Cantidad, FechaMovimiento, PrecioFinal, UsuarioID, Status, FechaDelivery)
                 VALUES (?, ?, ?, 'Salida', ?, NOW(), ?, ?, 'Abierto', ?)"
            );
            $upd = $db->prepare('UPDATE Productos SET Cantidad = Cantidad - ? WHERE ProductoID = ? AND Cantidad >= ?');
            foreach ($carrito as $l) {
                $p = $productos[(int) $l['ProductoID']];
                [$cant] = Unidades::validar($p['Tipo'], $l['Cantidad']);
                $ins->execute([
                    $comandaId, $sucursalId, $p['ProductoID'], $cant,
                    round((float) $p['PrecioUnitario'] * $multiplicadorIva * $cant, 2),
                    $usuarioId, $fechaEntrega,
                ]);
                $upd->execute([$cant, $p['ProductoID'], $cant]);
                if ($upd->rowCount() !== 1) {
                    throw new \InvalidArgumentException('El stock de "' . $p['Nombre'] . '" cambió; intenta de nuevo');
                }
            }

            $db->prepare('DELETE FROM CarritoSolicitudes WHERE UsuarioID = ?')->execute([$usuarioId]);
            $db->commit();

            return $comandaId;
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    private static function folio(PDO $db, int $sucursalId): string
    {
        $existe = $db->prepare('SELECT 1 FROM MovimientosInventario WHERE ComandaID = ? LIMIT 1');
        for ($i = 0; $i < 20; $i++) {
            $folio = 'COM-' . date('Ymd') . '-' . $sucursalId . '-' . random_int(100, 999);
            $existe->execute([$folio]);
            if (!$existe->fetchColumn()) {
                return $folio;
            }
        }
        throw new \RuntimeException('No se pudo generar un folio único');
    }
}
