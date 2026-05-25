<?php

namespace App\Mapper;

use App\Domain\Odontograma\OdontogramaPuentes;
use App\Dto\OdontogramaResponseDTO;
use App\Entity\Odontograma;

class OdontogramaMapper
{
    public function toResponseDto(Odontograma $odontograma): OdontogramaResponseDTO
    {
        $raw = $odontograma->getDientes();
        $puentes = OdontogramaPuentes::extract($raw);
        unset($raw[OdontogramaPuentes::META_KEY]);

        $dto = new OdontogramaResponseDTO();
        $dto->id = $odontograma->getId();
        $dto->dientes = $raw;
        $dto->puentes = $puentes;
        $dto->fecha_creacion = $odontograma->getFechaCreacion()?->format(\DateTimeInterface::ATOM);
        $dto->fecha_modificacion = $odontograma->getFechaModificacion()?->format(\DateTimeInterface::ATOM);

        return $dto;
    }
}
