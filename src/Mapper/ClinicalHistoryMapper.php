<?php

namespace App\Mapper;

use App\Dto\ClinicalHistoryResponseDTO;
use App\Entity\ClinicalHistory;

class ClinicalHistoryMapper
{
    public function toResponseDto(ClinicalHistory $history): ClinicalHistoryResponseDTO
    {
        $dto = new ClinicalHistoryResponseDTO();
        $dto->id = $history->getId();
        $dto->descripcion = $history->getDescripcion();
        $dto->fecha = $history->getFecha()?->format(\DateTimeInterface::ATOM);
        $dto->tipo = $history->getTipo();
        $dto->observaciones = $history->getObservaciones();

        return $dto;
    }
}
