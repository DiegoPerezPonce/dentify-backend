<?php

namespace App\Domain\Odontograma;

final class OdontogramaPuentes
{
    public const META_KEY = '_puentes';

    public const ESTADO_REALIZADO = 'PUENTE_REALIZADO';
    public const ESTADO_FUTURO = 'PUENTE_FUTURO';

    public const COLOR_REALIZADO = '#3182ce';
    public const COLOR_FUTURO = '#e53e3e';

    public static function colorForEstado(string $estado): string
    {
        return match ($estado) {
            self::ESTADO_REALIZADO => self::COLOR_REALIZADO,
            self::ESTADO_FUTURO => self::COLOR_FUTURO,
            default => self::COLOR_FUTURO,
        };
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function extract(array $dientes): array
    {
        $puentes = $dientes[self::META_KEY] ?? [];

        return is_array($puentes) ? array_values($puentes) : [];
    }

    /**
     * @param list<array<string, mixed>> $puentes
     */
    public static function merge(array $dientes, array $puentes): array
    {
        if ($puentes !== []) {
            $dientes[self::META_KEY] = $puentes;
        } elseif (isset($dientes[self::META_KEY])) {
            unset($dientes[self::META_KEY]);
        }

        return $dientes;
    }

    /**
     * @return array{0: string, 1: string}
     */
    public static function normalizePair(string $a, string $b): array
    {
        return (int) $a <= (int) $b ? [$a, $b] : [$b, $a];
    }
}
