<?php

namespace App\Enum;

final class BoxEstado
{
    public const DISPONIBLE = 'disponible';
    public const MANTENIMIENTO = 'mantenimiento';

    public const ALLOWED = [
        self::DISPONIBLE,
        self::MANTENIMIENTO,
    ];

    public static function normalize(?string $estado): string
    {
        $value = mb_strtolower(trim((string) $estado));

        if ($value === self::MANTENIMIENTO || str_contains($value, 'manten') || $value === 'fuera de servicio') {
            return self::MANTENIMIENTO;
        }

        if ($value === 'ocupado' || $value === '' || !in_array($value, self::ALLOWED, true)) {
            return self::DISPONIBLE;
        }

        return $value;
    }
}
