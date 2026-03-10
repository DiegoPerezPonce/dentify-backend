<?php

namespace App\Controller;

use App\Dto\PrimeraVisitaCreateDTO;
use App\Mapper\PrimeraVisitaMapper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/primera-visita')]
#[IsGranted('ROLE_USER')]
class PrimeraVisitaController extends AbstractController
{
    public function __construct(
        private PrimeraVisitaMapper $primeraVisitaMapper,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('', name: 'app_primera_visita_create', methods: ['POST'])]
    public function create(#[MapRequestPayload] PrimeraVisitaCreateDTO $createDTO): JsonResponse
    {
        $visita = $this->primeraVisitaMapper->toEntity($createDTO);
        
        $this->entityManager->persist($visita);
        $this->entityManager->flush();

        return $this->json(
            $this->primeraVisitaMapper->toResponseDto($visita),
            Response::HTTP_CREATED
        );
    }
}
