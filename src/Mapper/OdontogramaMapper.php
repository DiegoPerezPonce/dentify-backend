<?php

namespace App\Mapper;

use App\Dto\OdontogramaResponseDTO;
use App\Entity\Odontograma;

class OdontogramaMapper
{
    public function toResponseDto(Odontograma $odontograma): OdontogramaResponseDTO
    {
        $dto = new OdontogramaResponseDTO();
        $dto->id = $odontograma->getId();
        $dto->dientes = $odontograma->getDientes();
        $dto->fecha_creacion = $odontograma->getFechaCreacion()?->format(\DateTimeInterface::ATOM);
        $dto->fecha_modificacion = $odontograma->getFechaModificacion()?->format(\DateTimeInterface::ATOM);

        return $dto;
    }
}
