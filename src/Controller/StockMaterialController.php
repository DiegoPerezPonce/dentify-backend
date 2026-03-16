<?php

namespace App\Controller;

use App\Dto\StockMaterialCreateDTO;
use App\Dto\StockMaterialUpdateDTO;
use App\Dto\StockMaterialResponseDTO;
use App\Dto\UpdateStockQuantityDTO;
use App\Entity\StockMaterial;
use App\Mapper\StockMaterialMapper;
use App\Repository\StockMaterialRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/stock-materials')]
#[IsGranted('ROLE_USER')]
class StockMaterialController extends AbstractController
{
    public function __construct(
        private StockMaterialRepository $stockMaterialRepository,
        private StockMaterialMapper $stockMaterialMapper,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('', name: 'app_stock_material_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $materials = $this->stockMaterialRepository->findAll();

        $responseDtos = array_map(
            fn(StockMaterial $material) => $this->stockMaterialMapper->toResponseDto($material),
            $materials
        );

        return $this->json($responseDtos);
    }

    #[Route('/{id}', name: 'app_stock_material_get', methods: ['GET'])]
    public function get(StockMaterial $material): JsonResponse
    {
        return $this->json($this->stockMaterialMapper->toResponseDto($material));
    }

    #[Route('', name: 'app_stock_material_create', methods: ['POST'])]
    public function create(#[MapRequestPayload] StockMaterialCreateDTO $createDTO): JsonResponse
    {
        $material = $this->stockMaterialMapper->toEntity($createDTO);
        
        $this->entityManager->persist($material);
        $this->entityManager->flush();

        return $this->json(
            $this->stockMaterialMapper->toResponseDto($material),
            Response::HTTP_CREATED
        );
    }

    #[Route('/{id}/add', name: 'app_stock_material_add', methods: ['PUT'])]
    public function add(StockMaterial $material, #[MapRequestPayload] UpdateStockQuantityDTO $updateDTO): JsonResponse
    {
        $material->setCantidadActual($material->getCantidadActual() + $updateDTO->cantidad);
        $material->setFechaUltimaReposicion(new \DateTime());
        
        $this->entityManager->flush();

        return $this->json($this->stockMaterialMapper->toResponseDto($material));
    }

    #[Route('/{id}/reduce', name: 'app_stock_material_reduce', methods: ['PUT'])]
    public function reduce(StockMaterial $material, #[MapRequestPayload] UpdateStockQuantityDTO $updateDTO): JsonResponse
    {
        if ($material->getCantidadActual() < $updateDTO->cantidad) {
            return $this->json([
                'error' => 'Conflict',
                'message' => 'Insufficient stock. Current: ' . $material->getCantidadActual() . ', Required: ' . $updateDTO->cantidad
            ], Response::HTTP_CONFLICT);
        }

        $material->setCantidadActual($material->getCantidadActual() - $updateDTO->cantidad);
        
        $this->entityManager->flush();

        return $this->json($this->stockMaterialMapper->toResponseDto($material));
    }

    #[Route('/{id}', name: 'app_stock_material_delete', methods: ['DELETE'])]
    public function delete(StockMaterial $material): JsonResponse
    {
        $this->entityManager->remove($material);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
