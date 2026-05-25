<?php

namespace App\Domain\Schedule;

final class DentistWorkShift
{
    public const MORNING = 'morning';
    public const AFTERNOON = 'afternoon';
    public const FULL_DAY = 'full_day';
    public const OFF = 'off';

    /** @return list<string> */
    public static function all(): array
    {
        return [
            self::MORNING,
            self::AFTERNOON,
            self::FULL_DAY,
            self::OFF,
        ];
    }

    public static function isValid(?string $value): bool
    {
        return $value !== null && in_array($value, self::all(), true);
    }
}
