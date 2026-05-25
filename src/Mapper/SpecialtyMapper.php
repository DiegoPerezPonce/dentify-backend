<?php

namespace App\Mapper;

use App\Dto\SpecialtyResponseDTO;
use App\Entity\Specialty;

class SpecialtyMapper
{
    public function toResponseDto(Specialty $specialty): SpecialtyResponseDTO
    {
        $dto = new SpecialtyResponseDTO();
        $dto->id = $specialty->getId();
        $dto->name = $specialty->getName();
        $dto->active = $specialty->isActive();

        return $dto;
    }
}
