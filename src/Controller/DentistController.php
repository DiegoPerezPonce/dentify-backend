<?php

namespace App\Controller;

use App\Dto\DentistCreateDTO;
use App\Dto\DentistUpdateDTO;
use App\Entity\Dentist;
use App\Mapper\DentistMapper;
use App\Repository\DentistRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/dentists')]
#[IsGranted('ROLE_USER')]
class DentistController extends AbstractController
{
    public function __construct(
        private DentistRepository $dentistRepository,
        private DentistMapper $dentistMapper,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('', name: 'app_dentist_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $dentists = $this->dentistRepository->findAll();

        $responseDtos = array_map(
            fn(Dentist $dentist) => $this->dentistMapper->toResponseDto($dentist),
            $dentists
        );

        return $this->json($responseDtos);
    }

    #[Route('/{id}', name: 'app_dentist_get', methods: ['GET'])]
    public function get(Dentist $dentist): JsonResponse
    {
        return $this->json($this->dentistMapper->toResponseDto($dentist));
    }

    #[Route('', name: 'app_dentist_create', methods: ['POST'])]
    public function create(#[MapRequestPayload] DentistCreateDTO $dentistCreateDTO): JsonResponse
    {
        $dentist = $this->dentistMapper->toEntity($dentistCreateDTO);
        
        $this->entityManager->persist($dentist);
        $this->entityManager->flush();

        return $this->json(
            $this->dentistMapper->toResponseDto($dentist),
            Response::HTTP_CREATED
        );
    }

    #[Route('/{id}', name: 'app_dentist_update', methods: ['PUT', 'PATCH'])]
    public function update(Dentist $dentist, #[MapRequestPayload] DentistUpdateDTO $dentistUpdateDTO): JsonResponse
    {
        $dentist = $this->dentistMapper->updateEntity($dentist, $dentistUpdateDTO);
        
        $this->entityManager->flush();

        return $this->json($this->dentistMapper->toResponseDto($dentist));
    }

    #[Route('/{id}', name: 'app_dentist_delete', methods: ['DELETE'])]
    public function delete(Dentist $dentist): JsonResponse
    {
        $this->entityManager->remove($dentist);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
