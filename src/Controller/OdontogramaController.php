<?php

namespace App\Controller;

use App\Dto\OdontogramaToothStatusDTO;
use App\Entity\Odontograma;
use App\Entity\Patologia;
use App\Mapper\OdontogramaMapper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/odontograma')]
#[IsGranted('ROLE_USER')]
class OdontogramaController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private OdontogramaMapper $odontogramaMapper
    ) {}

    #[Route('/{id}/tooth-status', name: 'app_odontograma_update_tooth_status', methods: ['PUT'])]
    public function updateToothStatus(
        Odontograma $odontograma,
        #[MapRequestPayload] OdontogramaToothStatusDTO $dto
    ): JsonResponse {
        // 1. Create a new Patologia entry
        $patologia = new Patologia();
        $patologia->setDiente($dto->diente);
        $patologia->setCara($dto->cara);
        $patologia->setEstado($dto->estado);
        $patologia->setColor($dto->color);
        $patologia->setTipoPatologia($dto->tipo);
        $patologia->setOdontograma($odontograma);
        
        $this->entityManager->persist($patologia);

        // 2. Update the aggregated "dientes" state in the Odontograma
        $dientes = $odontograma->getDientes();
        
        // We structure the JSON as [tooth_id => [face => [status, color, type]]]
        if (!isset($dientes[$dto->diente])) {
            $dientes[$dto->diente] = [];
        }
        
        $dientes[$dto->diente][$dto->cara] = [
            'estado' => $dto->estado,
            'color' => $dto->color,
            'tipo' => $dto->tipo,
            'fecha' => $patologia->getFecha()->format(\DateTimeInterface::ATOM)
        ];

        $odontograma->setDientes($dientes);
        
        $this->entityManager->flush();

        return $this->json($this->odontogramaMapper->toResponseDto($odontograma));
    }
}
