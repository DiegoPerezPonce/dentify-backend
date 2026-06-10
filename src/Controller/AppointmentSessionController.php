<?php

namespace App\Controller;

use App\Dto\AppointmentSessionMaterialInputDTO;
use App\Dto\AppointmentSessionMaterialsUpdateDTO;
use App\Service\AppointmentSessionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/appointments/{appointmentId}/session')]
#[IsGranted('ROLE_USER')]
class AppointmentSessionController extends AbstractController
{
    public function __construct(
        private AppointmentSessionService $sessionService,
    ) {}

    #[Route('', name: 'app_appointment_session_get', methods: ['GET'], requirements: ['appointmentId' => '\d+'])]
    public function get(int $appointmentId): JsonResponse
    {
        return $this->json($this->sessionService->getSession($appointmentId));
    }

    #[Route('/start', name: 'app_appointment_session_start', methods: ['POST'], requirements: ['appointmentId' => '\d+'])]
    public function start(int $appointmentId): JsonResponse
    {
        return $this->json(
            $this->sessionService->startSession($appointmentId),
            Response::HTTP_CREATED
        );
    }

    #[Route('/materials', name: 'app_appointment_session_add_material', methods: ['POST'], requirements: ['appointmentId' => '\d+'])]
    public function addMaterial(
        int $appointmentId,
        #[MapRequestPayload] AppointmentSessionMaterialInputDTO $input
    ): JsonResponse {
        return $this->json($this->sessionService->addMaterial($appointmentId, $input));
    }

    #[Route('/materials', name: 'app_appointment_session_sync_materials', methods: ['PUT'], requirements: ['appointmentId' => '\d+'])]
    public function syncMaterials(
        int $appointmentId,
        #[MapRequestPayload] AppointmentSessionMaterialsUpdateDTO $dto
    ): JsonResponse {
        return $this->json($this->sessionService->syncMaterials($appointmentId, $dto->materials));
    }

    #[Route('/materials/{lineId}', name: 'app_appointment_session_remove_material', methods: ['DELETE'], requirements: ['appointmentId' => '\d+', 'lineId' => '\d+'])]
    public function removeMaterial(int $appointmentId, int $lineId): JsonResponse
    {
        return $this->json($this->sessionService->removeMaterialLine($appointmentId, $lineId));
    }

    #[Route('/complete', name: 'app_appointment_session_complete', methods: ['POST'], requirements: ['appointmentId' => '\d+'])]
    public function complete(int $appointmentId, Request $request): JsonResponse
    {
        $notes = null;
        $body = json_decode($request->getContent(), true);
        if (\is_array($body) && isset($body['notes']) && \is_string($body['notes'])) {
            $notes = $body['notes'];
        }

        return $this->json($this->sessionService->completeSession($appointmentId, $notes));
    }
}
