<?php

declare(strict_types=1);

namespace App\Services;

/** Catálogo único de unidades de medida de Productos.Tipo. */
final class Unidades
{
    /** @var array<string, array{singular:string,plural:string,abbr:string,entero:bool,paso:float,nombre:string}> */
    private const TIPOS = [
        'Unidad'  => ['nombre' => 'Unidad (pieza)', 'singular' => 'pza',    'plural' => 'pzas',    'abbr' => 'pza', 'entero' => true,  'paso' => 1.0],
        'Pesable' => ['nombre' => 'Pesable (kg)',   'singular' => 'kg',     'plural' => 'kg',      'abbr' => 'kg',  'entero' => false, 'paso' => 0.5],
        'Litro'   => ['nombre' => 'Litro (L)',      'singular' => 'L',      'plural' => 'L',       'abbr' => 'L',   'entero' => false, 'paso' => 0.5],
        'Manojo'  => ['nombre' => 'Manojo',         'singular' => 'manojo', 'plural' => 'manojos', 'abbr' => 'mnj', 'entero' => true,  'paso' => 1.0],
    ];

    /** @return list<string> */
    public static function tipos(): array
    {
        return array_keys(self::TIPOS);
    }

    public static function esValido(?string $tipo): bool
    {
        return $tipo !== null && isset(self::TIPOS[$tipo]);
    }

    public static function esEntero(?string $tipo): bool
    {
        return (self::TIPOS[$tipo] ?? self::TIPOS['Unidad'])['entero'];
    }

    public static function etiqueta(?string $tipo, float $cantidad = 1.0): string
    {
        $u = self::TIPOS[$tipo] ?? self::TIPOS['Unidad'];

        return abs($cantidad - 1.0) < 0.0005 ? $u['singular'] : $u['plural'];
    }

    /** Cantidad con su unidad, p. ej. "2.5 kg" o "3 manojos". */
    public static function formato(?string $tipo, float $cantidad): string
    {
        $txt = self::esEntero($tipo)
            ? number_format($cantidad, 0)
            : rtrim(rtrim(number_format($cantidad, 3, '.', ''), '0'), '.');

        return $txt . ' ' . self::etiqueta($tipo, $cantidad);
    }

    /**
     * Valida y normaliza una cantidad solicitada. Devuelve [cantidad, error].
     *
     * @return array{0: float|null, 1: string|null}
     */
    public static function validar(?string $tipo, mixed $valor, float $maximo = 100000.0): array
    {
        if (!is_numeric($valor) || !is_finite((float) $valor)) {
            return [null, 'La cantidad no es válida'];
        }
        $cantidad = round((float) $valor, 3);
        if ($cantidad <= 0) {
            return [null, 'La cantidad debe ser mayor a cero'];
        }
        if ($cantidad > $maximo) {
            return [null, 'La cantidad excede el máximo permitido'];
        }
        if (self::esEntero($tipo) && abs($cantidad - round($cantidad)) > 0.0001) {
            return [null, 'Este producto solo se pide en cantidades enteras (' . self::etiqueta($tipo, 2) . ')'];
        }
        if (!self::esEntero($tipo) && $cantidad < 0.001) {
            return [null, 'La cantidad mínima es 0.001'];
        }

        return [self::esEntero($tipo) ? (float) round($cantidad) : $cantidad, null];
    }

    /** Definición para el front (U.unidades en JS). */
    public static function paraJs(): array
    {
        $out = [];
        foreach (self::TIPOS as $tipo => $u) {
            $out[$tipo] = $u + ['tipo' => $tipo];
        }

        return $out;
    }
}
