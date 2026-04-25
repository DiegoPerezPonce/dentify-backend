<?php

namespace App\Controller;

use App\Dto\ProtocoloTratamientoCreateDTO;
use App\Dto\ProtocoloTratamientoUpdateDTO;
use App\Entity\ProtocoloTratamiento;
use App\Mapper\ProtocoloTratamientoMapper;
use App\Repository\ProtocoloTratamientoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/protocolos')]
#[IsGranted('ROLE_USER')]
class ProtocoloTratamientoController extends AbstractController
{
    public function __construct(
        private ProtocoloTratamientoRepository $protocoloTratamientoRepository,
        private ProtocoloTratamientoMapper $protocoloTratamientoMapper,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('', name: 'app_protocolo_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $items = $this->protocoloTratamientoRepository->findBy([], ['id' => 'ASC']);

        $responseDtos = array_map(
            fn (ProtocoloTratamiento $p) => $this->protocoloTratamientoMapper->toResponseDto($p),
            $items
        );

        return $this->json($responseDtos);
    }

    #[Route('/{id}', name: 'app_protocolo_get', methods: ['GET'])]
    public function get(ProtocoloTratamiento $protocolo): JsonResponse
    {
        return $this->json($this->protocoloTratamientoMapper->toResponseDto($protocolo));
    }

    #[Route('', name: 'app_protocolo_create', methods: ['POST'])]
    public function create(#[MapRequestPayload] ProtocoloTratamientoCreateDTO $dto): JsonResponse
    {
        try {
            $entity = $this->protocoloTratamientoMapper->toEntity($dto);
        } catch (\InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return $this->json(
            $this->protocoloTratamientoMapper->toResponseDto($entity),
            Response::HTTP_CREATED
        );
    }

    #[Route('/{id}', name: 'app_protocolo_update', methods: ['PUT', 'PATCH'])]
    public function update(ProtocoloTratamiento $protocolo, #[MapRequestPayload] ProtocoloTratamientoUpdateDTO $dto): JsonResponse
    {
        try {
            $protocolo = $this->protocoloTratamientoMapper->updateEntity($protocolo, $dto);
        } catch (\InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        $this->entityManager->flush();

        return $this->json($this->protocoloTratamientoMapper->toResponseDto($protocolo));
    }

    #[Route('/{id}', name: 'app_protocolo_delete', methods: ['DELETE'])]
    public function delete(ProtocoloTratamiento $protocolo): JsonResponse
    {
        $this->entityManager->remove($protocolo);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
