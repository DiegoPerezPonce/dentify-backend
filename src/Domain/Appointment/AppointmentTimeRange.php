<?php

namespace App\Domain\Appointment;

/** Utilidades para comparar rangos horarios de citas (fecha + hora_inicio + duración). */
final class AppointmentTimeRange
{
    public static function timeToMinutes(\DateTimeInterface $time): int
    {
        return (int) $time->format('H') * 60 + (int) $time->format('i');
    }

    /**
     * @param int $startMinutes inicio en minutos desde medianoche
     * @param int $endMinutes   fin en minutos desde medianoche
     */
    public static function rangesOverlap(
        int $startMinutes,
        int $endMinutes,
        int $otherStartMinutes,
        int $otherEndMinutes,
        int $bufferMinutes = 0
    ): bool {
        $otherStartBuffered = $otherStartMinutes - $bufferMinutes;
        $otherEndBuffered = $otherEndMinutes + $bufferMinutes;

        return $startMinutes < $otherEndBuffered && $endMinutes > $otherStartBuffered;
    }

    public static function rangeFromAppointmentStart(
        \DateTimeInterface $horaInicio,
        int $durationMinutes
    ): array {
        $start = self::timeToMinutes($horaInicio);

        return [$start, $start + $durationMinutes];
    }
}
