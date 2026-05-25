<?php

namespace App\Dto;

class TreatmentCategoryGroupDTO
{
    public ?int $id = null;
    public ?string $name = null;
    public int $sortOrder = 0;

    /** @var TreatmentResponseDTO[] */
    public array $treatments = [];
}
