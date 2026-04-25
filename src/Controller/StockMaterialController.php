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
use Symfony\Component\HttpFoundation\Request;
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

    /**
     * Query: search (nombre), lowStockOnly (bool), page, itemsPerPage.
     * Header X-Total-Count: total rows matching filters (for FE list + notification hub).
     */
    #[Route('', name: 'app_stock_material_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $filters = [];

        $search = $request->query->get('search');
        if (is_string($search) && trim($search) !== '') {
            $filters['search'] = trim($search);
        }

        $lowStockRaw = $request->query->get('lowStockOnly');
        if ($lowStockRaw !== null && $lowStockRaw !== '') {
            $parsed = filter_var($lowStockRaw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($parsed === null) {
                return $this->json(
                    ['error' => 'Bad Request', 'message' => 'Invalid lowStockOnly. Use true or false.'],
                    Response::HTTP_BAD_REQUEST
                );
            }
            if ($parsed) {
                $filters['lowStockOnly'] = true;
            }
        }

        $pagination = $this->resolveListPagination($request);
        if ($pagination instanceof JsonResponse) {
            return $pagination;
        }
        [$offset, $limit] = $pagination;

        [$materials, $total] = $this->stockMaterialRepository->findList($filters, $offset, $limit);

        $responseDtos = array_map(
            fn (StockMaterial $material) => $this->stockMaterialMapper->toResponseDto($material),
            $materials
        );

        $response = $this->json($responseDtos);
        $response->headers->set('X-Total-Count', (string) $total);

        return $response;
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

    #[Route('/{id}', name: 'app_stock_material_update', methods: ['PUT', 'PATCH'])]
    public function update(StockMaterial $material, #[MapRequestPayload] StockMaterialUpdateDTO $updateDTO): JsonResponse
    {
        $this->stockMaterialMapper->updateEntity($material, $updateDTO);
        $this->entityManager->flush();

        return $this->json($this->stockMaterialMapper->toResponseDto($material));
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
                'message' => 'Insufficient stock. Current: ' . $material->getCantidadActual() . ', Required: ' . $updateDTO->cantidad,
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

    /**
     * @return JsonResponse|array{0: int|null, 1: int|null}
     */
    private function resolveListPagination(Request $request): JsonResponse|array
    {
        $pageRaw = $request->query->get('page');
        $itemsRaw = $request->query->get('itemsPerPage');

        if (
            ($pageRaw === null || $pageRaw === '')
            && ($itemsRaw === null || $itemsRaw === '')
        ) {
            return [null, null];
        }

        if ($pageRaw !== null && $pageRaw !== '' && (!ctype_digit((string) $pageRaw) || (int) $pageRaw < 1)) {
            return $this->json(
                ['error' => 'Bad Request', 'message' => 'Invalid page.'],
                Response::HTTP_BAD_REQUEST
            );
        }

        if ($itemsRaw !== null && $itemsRaw !== '' && !ctype_digit((string) $itemsRaw)) {
            return $this->json(
                ['error' => 'Bad Request', 'message' => 'Invalid itemsPerPage.'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $page = ($pageRaw === null || $pageRaw === '') ? 1 : (int) $pageRaw;
        $itemsPerPage = ($itemsRaw === null || $itemsRaw === '') ? 30 : max(1, min(100, (int) $itemsRaw));

        return [($page - 1) * $itemsPerPage, $itemsPerPage];
    }
}
