<?php

namespace App\Controller;

use App\Domain\Appointment\AppointmentKind;
use App\Domain\Appointment\AppointmentKindCatalogRules;
use App\Mapper\TreatmentCategoryMapper;
use App\Repository\TreatmentCategoryRepository;
use App\Repository\TreatmentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/treatment-categories')]
#[IsGranted('ROLE_USER')]
class TreatmentCategoryController extends AbstractController
{
    public function __construct(
        private TreatmentCategoryRepository $categoryRepository,
        private TreatmentRepository $treatmentRepository,
        private TreatmentCategoryMapper $categoryMapper
    ) {}

    #[Route('', name: 'app_treatment_category_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $appointmentKindRaw = $request->query->get('appointmentKind');
        $appointmentKind = is_string($appointmentKindRaw) && $appointmentKindRaw !== ''
            ? trim($appointmentKindRaw)
            : null;

        if ($appointmentKind !== null && !AppointmentKind::isValid($appointmentKind)) {
            throw new BadRequestHttpException('Invalid appointmentKind.');
        }

        $filter = AppointmentKindCatalogRules::filterFor($appointmentKind);

        $categories = $this->categoryRepository->findAllActiveOrdered();
        $treatments = $this->treatmentRepository->findForAppointmentFilters($filter);

        return $this->json(
            $this->categoryMapper->toGroupedDtos($categories, $treatments)
        );
    }
}
