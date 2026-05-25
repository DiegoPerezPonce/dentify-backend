<?php

namespace App\Controller;

use App\Mapper\SpecialtyMapper;
use App\Repository\SpecialtyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/specialties')]
#[IsGranted('ROLE_USER')]
class SpecialtyController extends AbstractController
{
    public function __construct(
        private SpecialtyRepository $specialtyRepository,
        private SpecialtyMapper $specialtyMapper
    ) {}

    #[Route('', name: 'app_specialty_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $items = array_map(
            fn ($s) => $this->specialtyMapper->toResponseDto($s),
            $this->specialtyRepository->findAllActive()
        );

        return $this->json($items);
    }
}
