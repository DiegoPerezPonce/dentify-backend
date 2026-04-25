<?php

namespace App\Mapper;

use App\Dto\ProtocoloTratamientoCreateDTO;
use App\Dto\ProtocoloTratamientoResponseDTO;
use App\Dto\ProtocoloTratamientoUpdateDTO;
use App\Entity\ProtocoloTratamiento;
use App\Repository\StockMaterialRepository;

class ProtocoloTratamientoMapper
{
    public function __construct(
        private StockMaterialRepository $stockMaterialRepository,
        private StockMaterialMapper $stockMaterialMapper
    ) {}

    public function toEntity(ProtocoloTratamientoCreateDTO $dto): ProtocoloTratamiento
    {
        $material = $this->stockMaterialRepository->find($dto->material_id);
        if (!$material) {
            throw new \InvalidArgumentException('Material not found with ID: '.$dto->material_id);
        }

        $entity = new ProtocoloTratamiento();
        $entity->setNombreTratamiento($dto->nombre_tratamiento);
        $entity->setMaterial($material);
        $entity->setCantidadNecesaria($dto->cantidad_necesaria);
        $entity->setNotasPasoAPaso($dto->notas_paso_a_paso);

        return $entity;
    }

    public function updateEntity(ProtocoloTratamiento $entity, ProtocoloTratamientoUpdateDTO $dto): ProtocoloTratamiento
    {
        if ($dto->nombre_tratamiento !== null) {
            $entity->setNombreTratamiento($dto->nombre_tratamiento);
        }
        if ($dto->material_id !== null) {
            $material = $this->stockMaterialRepository->find($dto->material_id);
            if (!$material) {
                throw new \InvalidArgumentException('Material not found with ID: '.$dto->material_id);
            }
            $entity->setMaterial($material);
        }
        if ($dto->cantidad_necesaria !== null) {
            $entity->setCantidadNecesaria($dto->cantidad_necesaria);
        }
        if ($dto->notas_paso_a_paso !== null) {
            $entity->setNotasPasoAPaso($dto->notas_paso_a_paso === '' ? null : $dto->notas_paso_a_paso);
        }

        return $entity;
    }

    public function toResponseDto(ProtocoloTratamiento $entity): ProtocoloTratamientoResponseDTO
    {
        $dto = new ProtocoloTratamientoResponseDTO();
        $dto->id = $entity->getId();
        $dto->nombre_tratamiento = $entity->getNombreTratamiento();
        $dto->material = $this->stockMaterialMapper->toResponseDto($entity->getMaterial());
        $dto->cantidad_necesaria = $entity->getCantidadNecesaria();
        $dto->notas_paso_a_paso = $entity->getNotasPasoAPaso();

        return $dto;
    }
}
