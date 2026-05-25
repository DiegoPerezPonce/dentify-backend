<?php

namespace App\Mapper;

use App\Dto\ClinicScheduleSettingsDTO;
use App\Dto\DentistWeeklyScheduleResponseDTO;
use App\Dto\WeeklyScheduleDayDTO;
use App\Entity\ClinicScheduleSettings;
use App\Entity\Dentist;
use App\Entity\DentistWeeklySchedule;
use App\Service\ScheduleTimeResolver;

class ScheduleMapper
{
    public function clinicToDto(ClinicScheduleSettings $settings): ClinicScheduleSettingsDTO
    {
        $dto = new ClinicScheduleSettingsDTO();
        $dto->openTime = $settings->getOpenTime();
        $dto->closeTime = $settings->getCloseTime();
        $dto->morningEndTime = $settings->getMorningEndTime();
        $dto->afternoonStartTime = $settings->getAfternoonStartTime();

        return $dto;
    }

    /**
     * @param DentistWeeklySchedule[] $rows
     */
    public function toWeeklyResponse(
        Dentist $dentist,
        ClinicScheduleSettings $settings,
        array $rows
    ): DentistWeeklyScheduleResponseDTO {
        $dto = new DentistWeeklyScheduleResponseDTO();
        $dto->dentistId = $dentist->getId();
        $dto->dentistName = trim($dentist->getNombre() . ' ' . $dentist->getApellidos());
        $dto->clinic = $this->clinicToDto($settings);

        $byWeekday = [];
        foreach ($rows as $row) {
            $byWeekday[$row->getWeekday()] = $row;
        }

        for ($weekday = 1; $weekday <= 6; ++$weekday) {
            $dayDto = new WeeklyScheduleDayDTO();
            $dayDto->weekday = $weekday;
            $row = $byWeekday[$weekday] ?? null;
            $shift = $row?->getShift() ?? 'off';
            $dayDto->shift = $shift;
            $dayDto->shiftLabel = ScheduleTimeResolver::labelForShift($shift);
            $range = ScheduleTimeResolver::rangeForShift($shift, $settings);
            if ($range !== null) {
                $dayDto->startTime = $range['start'];
                $dayDto->endTime = $range['end'];
            }
            $dto->days[] = $dayDto;
        }

        return $dto;
    }
}
