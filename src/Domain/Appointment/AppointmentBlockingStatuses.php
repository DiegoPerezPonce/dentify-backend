<?php

namespace App\Domain\Appointment;

/** Estados que ocupan odontólogo / box en agenda. */
final class AppointmentBlockingStatuses
{
    public const CANCELLED = 'cancelada';

  /** @var list<string> */
    public const BLOCKING = [
        'programada',
        'confirmada',
        'completada',
        'no_show',
    ];

    public static function isBlocking(?string $estado): bool
    {
        if ($estado === null || trim($estado) === '') {
            return true;
        }

        $norm = strtolower(trim($estado));

        return $norm !== self::CANCELLED;
    }
}
