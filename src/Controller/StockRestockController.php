<?php

namespace App\Controller;

use App\Dto\StockRestockCreateDTO;
use App\Entity\StockRestock;
use App\Mapper\StockRestockMapper;
use App\Repository\StockRestockRepository;
use App\Repository\StockMaterialRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/stock-restocks')]
#[IsGranted('ROLE_USER')]
class StockRestockController extends AbstractController
{
    public function __construct(
        private StockRestockRepository $stockRestockRepository,
        private StockMaterialRepository $stockMaterialRepository,
        private StockRestockMapper $stockRestockMapper,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('', name: 'app_stock_restock_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $materialId = $request->query->get('materialId');
        $startDate = $request->query->get('startDate');
        $endDate = $request->query->get('endDate');

        $criteria = [];
        if ($materialId) {
            $material = $this->stockMaterialRepository->find($materialId);
            if ($material) {
                $criteria['material'] = $material;
            }
        }

        $restocks = $this->stockRestockRepository->findBy(
            $criteria,
            ['fecha' => 'DESC']
        );

        // Apply date filters if provided
        if ($startDate || $endDate) {
            $restocks = array_filter($restocks, function (StockRestock $restock) use ($startDate, $endDate) {
                $fecha = $restock->getFecha();
                if (!$fecha) return false;

                if ($startDate && $fecha < new \DateTime($startDate)) {
                    return false;
                }
                if ($endDate && $fecha > new \DateTime($endDate)) {
                    return false;
                }
                return true;
            });
        }

        $responseDtos = array_map(
            fn(StockRestock $restock) => $this->stockRestockMapper->toResponseDto($restock),
            $restocks
        );

        return $this->json(array_values($responseDtos));
    }

    #[Route('/{id}', name: 'app_stock_restock_get', methods: ['GET'])]
    public function get(StockRestock $restock): JsonResponse
    {
        return $this->json($this->stockRestockMapper->toResponseDto($restock));
    }

    #[Route('', name: 'app_stock_restock_create', methods: ['POST'])]
    public function create(#[MapRequestPayload] StockRestockCreateDTO $createDTO): JsonResponse
    {
        $restock = $this->stockRestockMapper->toEntity($createDTO);
        
        // Validate that material exists
        $material = $restock->getMaterial();
        if (!$material) {
            return $this->json([
                'error' => 'Bad Request',
                'message' => 'Material not found with ID: ' . $createDTO->material_id
            ], Response::HTTP_BAD_REQUEST);
        }

        // Update stock quantity automatically
        $material->setCantidadActual(
            $material->getCantidadActual() + $createDTO->cantidad_recibida
        );
        $material->setFechaUltimaReposicion(new \DateTime());

        // Persist restock record and updated material
        $this->entityManager->persist($restock);
        $this->entityManager->flush();

        return $this->json(
            $this->stockRestockMapper->toResponseDto($restock),
            Response::HTTP_CREATED
        );
    }

    #[Route('/{id}', name: 'app_stock_restock_delete', methods: ['DELETE'])]
    public function delete(StockRestock $restock): JsonResponse
    {
        // Optional: When deleting a restock, you might want to adjust the stock back
        // For now, we just delete the record without modifying stock
        $this->entityManager->remove($restock);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/material/{materialId}/history', name: 'app_stock_restock_by_material', methods: ['GET'])]
    public function getByMaterial(int $materialId): JsonResponse
    {
        $material = $this->stockMaterialRepository->find($materialId);
        
        if (!$material) {
            return $this->json([
                'error' => 'Not Found',
                'message' => 'Material not found with ID: ' . $materialId
            ], Response::HTTP_NOT_FOUND);
        }

        $restocks = $this->stockRestockRepository->findBy(
            ['material' => $material],
            ['fecha' => 'DESC']
        );

        $responseDtos = array_map(
            fn(StockRestock $restock) => $this->stockRestockMapper->toResponseDto($restock),
            $restocks
        );

        return $this->json($responseDtos);
    }
}
