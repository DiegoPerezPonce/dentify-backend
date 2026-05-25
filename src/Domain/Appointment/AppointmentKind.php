<?php

namespace App\Domain\Appointment;

final class AppointmentKind
{
    public const PRIMERA_VISITA = 'primera_visita';
    public const URGENCIA = 'urgencia';
    public const TRATAMIENTO = 'tratamiento';
    public const REVISION = 'revision';

  /** @return list<string> */
    public static function all(): array
    {
        return [
            self::PRIMERA_VISITA,
            self::URGENCIA,
            self::TRATAMIENTO,
            self::REVISION,
        ];
    }

    public static function isValid(?string $value): bool
    {
        return $value !== null && in_array($value, self::all(), true);
    }
}
