<?php

namespace App\Dto;

class DentistWeeklyScheduleResponseDTO
{
    public ?int $dentistId = null;
    public ?string $dentistName = null;
    public ?ClinicScheduleSettingsDTO $clinic = null;

    /** @var WeeklyScheduleDayDTO[] */
    public array $days = [];
}
