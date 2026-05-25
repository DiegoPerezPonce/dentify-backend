<?php

namespace App\Dto;

class ClinicScheduleSettingsDTO
{
    public string $openTime = '08:00';
    public string $closeTime = '21:00';
    public string $morningEndTime = '15:00';
    public string $afternoonStartTime = '14:00';
    public string $overlapNote = '14:00–15:00: solape entre turno mañana y tarde.';
}
