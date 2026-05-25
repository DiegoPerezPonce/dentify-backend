<?php

namespace App\Controller;

use App\Domain\Odontograma\OdontogramaPuentes;
use App\Dto\OdontogramaBridgeDTO;
use App\Dto\OdontogramaToothStatusDTO;
use App\Entity\Odontograma;
use App\Entity\Patologia;
use App\Mapper\OdontogramaMapper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
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

    #[Route('/{id}/tooth-status', name: 'app_odontograma_clear_tooth_status', methods: ['DELETE'])]
    public function clearToothStatus(Odontograma $odontograma, Request $request): JsonResponse
    {
        $diente = $request->query->getString('diente');
        $cara = $request->query->getString('cara');
        if ($diente === '' || $cara === '') {
            throw new BadRequestHttpException('Query parameters diente and cara are required.');
        }

        $dientes = $odontograma->getDientes();
        if (isset($dientes[$diente][$cara])) {
            unset($dientes[$diente][$cara]);
            if ($dientes[$diente] === []) {
                unset($dientes[$diente]);
            }
            $odontograma->setDientes($dientes);
            $this->entityManager->flush();
        }

        return $this->json($this->odontogramaMapper->toResponseDto($odontograma));
    }

    #[Route('/{id}/bridge', name: 'app_odontograma_add_bridge', methods: ['POST'])]
    public function addBridge(
        Odontograma $odontograma,
        #[MapRequestPayload] OdontogramaBridgeDTO $dto
    ): JsonResponse {
        if ($dto->diente_inicio === $dto->diente_fin) {
            throw new BadRequestHttpException('Los pilares del puente deben ser dientes distintos.');
        }

        [$inicio, $fin] = OdontogramaPuentes::normalizePair($dto->diente_inicio, $dto->diente_fin);

        $dientes = $odontograma->getDientes();
        $puentes = OdontogramaPuentes::extract($dientes);

        foreach ($puentes as $puente) {
            if (
                ($puente['diente_inicio'] ?? '') === $inicio
                && ($puente['diente_fin'] ?? '') === $fin
            ) {
                throw new BadRequestHttpException('Ya existe un puente entre esos dientes pilastro.');
            }
        }

        $puentes[] = [
            'id' => uniqid('br_', true),
            'diente_inicio' => $inicio,
            'diente_fin' => $fin,
            'estado' => $dto->estado,
            'color' => OdontogramaPuentes::colorForEstado($dto->estado),
            'fecha' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
        ];

        $odontograma->setDientes(OdontogramaPuentes::merge($dientes, $puentes));
        $this->entityManager->flush();

        return $this->json($this->odontogramaMapper->toResponseDto($odontograma));
    }

    #[Route('/{id}/bridge', name: 'app_odontograma_delete_bridge', methods: ['DELETE'])]
    public function deleteBridge(Odontograma $odontograma, Request $request): JsonResponse
    {
        $bridgeId = $request->query->getString('id');
        if ($bridgeId === '') {
            throw new BadRequestHttpException('Query parameter id is required.');
        }

        $dientes = $odontograma->getDientes();
        $puentes = OdontogramaPuentes::extract($dientes);
        $filtered = array_values(array_filter(
            $puentes,
            static fn (array $puente): bool => ($puente['id'] ?? '') !== $bridgeId
        ));

        if (count($filtered) === count($puentes)) {
            throw new BadRequestHttpException('Puente no encontrado.');
        }

        $odontograma->setDientes(OdontogramaPuentes::merge($dientes, $filtered));
        $this->entityManager->flush();

        return $this->json($this->odontogramaMapper->toResponseDto($odontograma));
    }
}
