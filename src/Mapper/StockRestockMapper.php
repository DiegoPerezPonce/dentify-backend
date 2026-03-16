<?php

namespace App\Mapper;

use App\Dto\StockRestockCreateDTO;
use App\Dto\StockRestockResponseDTO;
use App\Entity\StockRestock;
use App\Repository\StockMaterialRepository;

class StockRestockMapper
{
    public function __construct(
        private StockMaterialRepository $stockMaterialRepository,
        private StockMaterialMapper $stockMaterialMapper
    ) {}

    public function toEntity(StockRestockCreateDTO $dto): StockRestock
    {
        $entity = new StockRestock();
        
        $material = $this->stockMaterialRepository->find($dto->material_id);
        if ($material) {
            $entity->setMaterial($material);
        }
        
        $entity->setCantidadRecibida($dto->cantidad_recibida);
        $entity->setProveedor($dto->proveedor);
        $entity->setFecha(new \DateTime($dto->fecha));

        return $entity;
    }

    public function toResponseDto(StockRestock $entity): StockRestockResponseDTO
    {
        $dto = new StockRestockResponseDTO();
        $dto->id = $entity->getId();
        $dto->material = $this->stockMaterialMapper->toResponseDto($entity->getMaterial());
        $dto->cantidad_recibida = $entity->getCantidadRecibida();
        $dto->proveedor = $entity->getProveedor();
        $dto->fecha = $entity->getFecha()?->format(\DateTime::ATOM);

        return $dto;
    }
}
