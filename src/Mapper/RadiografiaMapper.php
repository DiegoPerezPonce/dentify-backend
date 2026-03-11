<?php

namespace App\Mapper;

use App\Dto\RadiografiaResponseDTO;
use App\Entity\Radiografia;

class RadiografiaMapper
{
    public function toResponseDto(Radiografia $radiografia): RadiografiaResponseDTO
    {
        $dto = new RadiografiaResponseDTO();
        $dto->id = $radiografia->getId();
        $dto->nombre_archivo = $radiografia->getNombreArchivo();
        $dto->ruta_archivo = $radiografia->getRutaArchivo();
        $dto->tipo_mime = $radiografia->getTipoMime();
        $dto->fecha_subida = $radiografia->getFechaSubida()?->format(\DateTimeInterface::ATOM);

        return $dto;
    }
}
