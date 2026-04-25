<?php

namespace App\Mapper;

use App\Dto\StockMaterialCreateDTO;
use App\Dto\StockMaterialUpdateDTO;
use App\Dto\StockMaterialResponseDTO;
use App\Entity\StockMaterial;

class StockMaterialMapper
{
    public function toEntity(StockMaterialCreateDTO $dto): StockMaterial
    {
        $entity = new StockMaterial();
        $this->mapDtoToEntity($dto, $entity);

        return $entity;
    }

    public function updateEntity(StockMaterial $entity, StockMaterialUpdateDTO $dto): StockMaterial
    {
        $this->mapDtoToEntity($dto, $entity);

        return $entity;
    }

    private function mapDtoToEntity($dto, StockMaterial $entity): void
    {
        if (property_exists($dto, 'nombre') && $dto->nombre !== null) {
            $entity->setNombre($dto->nombre);
        }
        if (property_exists($dto, 'cantidad_actual') && $dto->cantidad_actual !== null) {
            $entity->setCantidadActual($dto->cantidad_actual);
        }
        if (property_exists($dto, 'unidad') && $dto->unidad !== null) {
            $entity->setUnidad($dto->unidad);
        }
        if (property_exists($dto, 'umbral_minimo') && $dto->umbral_minimo !== null) {
            $entity->setUmbralMinimo($dto->umbral_minimo);
        }
        if (property_exists($dto, 'fecha_ultima_reposicion') && $dto->fecha_ultima_reposicion !== null) {
            $entity->setFechaUltimaReposicion(new \DateTime($dto->fecha_ultima_reposicion));
        }
    }

    public function toResponseDto(StockMaterial $entity): StockMaterialResponseDTO
    {
        $dto = new StockMaterialResponseDTO();
        $dto->id = $entity->getId();
        $dto->nombre = $entity->getNombre();
        $dto->cantidad_actual = $entity->getCantidadActual();
        $umbral = $entity->getUmbralMinimo() ?? 10.0;
        $dto->umbral_minimo = $umbral;
        $dto->is_low_stock = $entity->getCantidadActual() <= $umbral;
        $dto->unidad = $entity->getUnidad();
        $dto->fecha_ultima_reposicion = $entity->getFechaUltimaReposicion()?->format('Y-m-d');

        return $dto;
    }
}
