<?php

namespace App\Service;

use App\Domain\Schedule\DentistWorkShift;
use App\Entity\ClinicScheduleSettings;

final class ScheduleTimeResolver
{
    /**
     * @return array{start: string, end: string}|null
     */
    public static function rangeForShift(string $shift, ClinicScheduleSettings $settings): ?array
    {
        return match ($shift) {
            DentistWorkShift::MORNING => [
                'start' => $settings->getOpenTime(),
                'end' => $settings->getMorningEndTime(),
            ],
            DentistWorkShift::AFTERNOON => [
                'start' => $settings->getAfternoonStartTime(),
                'end' => $settings->getCloseTime(),
            ],
            DentistWorkShift::FULL_DAY => [
                'start' => $settings->getOpenTime(),
                'end' => $settings->getCloseTime(),
            ],
            DentistWorkShift::OFF => null,
            default => null,
        };
    }

    public static function labelForShift(string $shift): string
    {
        return match ($shift) {
            DentistWorkShift::MORNING => 'Mañana',
            DentistWorkShift::AFTERNOON => 'Tarde',
            DentistWorkShift::FULL_DAY => 'Todo el día',
            DentistWorkShift::OFF => 'Libre',
            default => $shift,
        };
    }
}
