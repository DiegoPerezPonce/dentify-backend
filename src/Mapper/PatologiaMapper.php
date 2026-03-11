<?php

namespace App\Mapper;

use App\Dto\PatologiaResponseDTO;
use App\Entity\Patologia;

class PatologiaMapper
{
    public function toResponseDto(Patologia $patologia): PatologiaResponseDTO
    {
        $dto = new PatologiaResponseDTO();
        $dto->id = $patologia->getId();
        $dto->diente = $patologia->getDiente();
        $dto->cara = $patologia->getCara();
        $dto->tipo_patologia = $patologia->getTipoPatologia();
        $dto->estado = $patologia->getEstado();
        $dto->color = $patologia->getColor();
        $dto->fecha = $patologia->getFecha()?->format(\DateTimeInterface::ATOM);

        return $dto;
    }
}
