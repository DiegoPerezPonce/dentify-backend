<?php

namespace App\Controller;

use App\Dto\BoxCreateDTO;
use App\Dto\BoxUpdateDTO;
use App\Entity\Box;
use App\Mapper\BoxMapper;
use App\Repository\BoxRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/boxes')]
#[IsGranted('ROLE_USER')]
class BoxController extends AbstractController
{
    public function __construct(
        private BoxRepository $boxRepository,
        private BoxMapper $boxMapper,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('', name: 'app_box_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $boxes = $this->boxRepository->findAll();

        $responseDtos = array_map(
            fn(Box $box) => $this->boxMapper->toResponseDto($box),
            $boxes
        );

        return $this->json($responseDtos);
    }

    #[Route('/{id}', name: 'app_box_get', methods: ['GET'])]
    public function get(Box $box): JsonResponse
    {
        return $this->json($this->boxMapper->toResponseDto($box));
    }

    #[Route('', name: 'app_box_create', methods: ['POST'])]
    public function create(#[MapRequestPayload] BoxCreateDTO $boxCreateDTO): JsonResponse
    {
        try {
            $box = $this->boxMapper->toEntity($boxCreateDTO);
        } catch (\InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        $this->entityManager->persist($box);
        $this->entityManager->flush();

        return $this->json(
            $this->boxMapper->toResponseDto($box),
            Response::HTTP_CREATED
        );
    }

    #[Route('/{id}', name: 'app_box_update', methods: ['PUT', 'PATCH'])]
    public function update(Box $box, #[MapRequestPayload] BoxUpdateDTO $boxUpdateDTO): JsonResponse
    {
        try {
            $box = $this->boxMapper->updateEntity($box, $boxUpdateDTO);
        } catch (\InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        $this->entityManager->flush();

        return $this->json($this->boxMapper->toResponseDto($box));
    }

    #[Route('/{id}', name: 'app_box_delete', methods: ['DELETE'])]
    public function delete(Box $box): JsonResponse
    {
        $this->entityManager->remove($box);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
