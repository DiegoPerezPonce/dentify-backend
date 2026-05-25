<?php

namespace App\Dto;

class TreatmentResponseDTO
{
    public ?int $id = null;
    public ?string $name = null;
    public ?int $categoryId = null;
    public ?string $categoryName = null;
    public ?int $dentistSpecialtyId = null;
    public ?string $dentistSpecialtyName = null;
    /** @deprecated Use dentistSpecialtyId */
    public ?int $specialtyId = null;
    /** @deprecated Use dentistSpecialtyName */
    public ?string $specialtyName = null;
    public int $defaultDurationMinutes = 30;
    public bool $allowsUrgency = true;
    public bool $forFirstVisit = false;
    public bool $isCustom = false;
    public bool $active = true;
}
