<?php

namespace App\Controller;

use App\Domain\Appointment\AppointmentKind;
use App\Domain\Appointment\AppointmentKindCatalogRules;
use App\Dto\TreatmentCreateDTO;
use App\Mapper\TreatmentMapper;
use App\Repository\TreatmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/treatments')]
#[IsGranted('ROLE_USER')]
class TreatmentController extends AbstractController
{
    public function __construct(
        private TreatmentRepository $treatmentRepository,
        private TreatmentMapper $treatmentMapper,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('', name: 'app_treatment_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $categoryId = $this->optionalPositiveInt($request->query->get('categoryId'));

        $appointmentKindRaw = $request->query->get('appointmentKind');
        $appointmentKind = is_string($appointmentKindRaw) && $appointmentKindRaw !== ''
            ? trim($appointmentKindRaw)
            : null;

        if ($appointmentKind !== null && !AppointmentKind::isValid($appointmentKind)) {
            throw new BadRequestHttpException('Invalid appointmentKind.');
        }

        $filter = AppointmentKindCatalogRules::filterFor($appointmentKind);

        $items = array_map(
            fn ($t) => $this->treatmentMapper->toResponseDto($t),
            $this->treatmentRepository->findForAppointmentFilters($filter, $categoryId)
        );

        return $this->json($items);
    }

    #[Route('', name: 'app_treatment_create', methods: ['POST'])]
    public function create(#[MapRequestPayload] TreatmentCreateDTO $dto): JsonResponse
    {
        try {
            $treatment = $this->treatmentMapper->toEntity($dto, true);
        } catch (\InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        $this->entityManager->persist($treatment);
        $this->entityManager->flush();

        return $this->json(
            $this->treatmentMapper->toResponseDto($treatment),
            Response::HTTP_CREATED
        );
    }

    private function optionalPositiveInt(mixed $raw): ?int
    {
        if ($raw === null || $raw === '') {
            return null;
        }
        if (!ctype_digit((string) $raw)) {
            throw new BadRequestHttpException('Invalid categoryId.');
        }

        return (int) $raw;
    }
}
