<?php

namespace App\Controller;

use App\Dto\AppointmentCreateDTO;
use App\Dto\AppointmentUpdateDTO;
use App\Dto\AppointmentRescheduleDTO;
use App\Entity\Appointment;
use App\Mapper\AppointmentMapper;
use App\Service\EmailService;
use App\Repository\AppointmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/appointments')]
#[IsGranted('ROLE_USER')]
class AppointmentController extends AbstractController
{
    public function __construct(
        private AppointmentRepository $appointmentRepository,
        private AppointmentMapper $appointmentMapper,
        private EntityManagerInterface $entityManager,
        private EmailService $emailService
    ) {}

    #[Route('', name: 'app_appointment_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $filters = [];

        foreach (['patientId' => 'patientId', 'dentistId' => 'dentistId', 'boxId' => 'boxId'] as $queryKey => $filterKey) {
            $raw = $request->query->get($queryKey);
            if ($raw === null || $raw === '') {
                continue;
            }
            if (!ctype_digit((string) $raw)) {
                return $this->json(
                    ['error' => 'Bad Request', 'message' => sprintf('Invalid %s.', $queryKey)],
                    Response::HTTP_BAD_REQUEST
                );
            }
            $filters[$filterKey] = (int) $raw;
        }

        $startDateRaw = $request->query->get('startDate');
        $endDateRaw = $request->query->get('endDate');
        $fechaRaw = $request->query->get('fecha');

        if ($startDateRaw !== null && $startDateRaw !== '') {
            $start = $this->parseListDate($startDateRaw);
            if ($start === null) {
                return $this->json(
                    ['error' => 'Bad Request', 'message' => 'Invalid startDate.'],
                    Response::HTTP_BAD_REQUEST
                );
            }
            $filters['startDate'] = $start;
        }
        if ($endDateRaw !== null && $endDateRaw !== '') {
            $end = $this->parseListDate($endDateRaw);
            if ($end === null) {
                return $this->json(
                    ['error' => 'Bad Request', 'message' => 'Invalid endDate.'],
                    Response::HTTP_BAD_REQUEST
                );
            }
            $filters['endDate'] = $end;
        }
        if (
            ($startDateRaw === null || $startDateRaw === '')
            && ($endDateRaw === null || $endDateRaw === '')
            && $fechaRaw !== null && $fechaRaw !== ''
        ) {
            $day = $this->parseListDate($fechaRaw);
            if ($day === null) {
                return $this->json(
                    ['error' => 'Bad Request', 'message' => 'Invalid fecha.'],
                    Response::HTTP_BAD_REQUEST
                );
            }
            $filters['startDate'] = $day;
            $filters['endDate'] = $day;
        }

        $statusRaw = $request->query->get('status');
        if ($statusRaw !== null && $statusRaw !== '') {
            $estado = $this->normalizeStatusFilter((string) $statusRaw);
            if ($estado === null) {
                return $this->json(
                    ['error' => 'Bad Request', 'message' => 'Invalid status.'],
                    Response::HTTP_BAD_REQUEST
                );
            }
            $filters['estado'] = $estado;
        }

        $pagination = $this->resolveListPagination($request);
        if ($pagination instanceof JsonResponse) {
            return $pagination;
        }
        [$offset, $limit] = $pagination;

        [$appointments, $total] = $this->appointmentRepository->findList($filters, $offset, $limit);

        $responseDtos = array_map(
            fn (Appointment $appointment) => $this->appointmentMapper->toResponseDto($appointment),
            $appointments
        );

        $response = $this->json($responseDtos);
        $response->headers->set('X-Total-Count', (string) $total);

        return $response;
    }

    private function parseListDate(string $raw): ?\DateTimeImmutable
    {
        try {
            return new \DateTimeImmutable(trim($raw));
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Maps frontend AppointmentStatus values to DB estado; accepts Spanish estado as-is.
     */
    private function normalizeStatusFilter(string $status): ?string
    {
        $norm = strtolower(trim($status));
        $map = [
            'scheduled' => 'programada',
            'confirmed' => 'confirmada',
            'completed' => 'completada',
            'cancelled' => 'cancelada',
            'no_show' => 'no_show',
        ];
        if (isset($map[$norm])) {
            return $map[$norm];
        }

        if (preg_match('/^[a-záéíóúñ_]+$/u', $norm) === 1) {
            return $norm;
        }

        return null;
    }

    /**
     * @return JsonResponse|array{0: int|null, 1: int|null}
     */
    private function resolveListPagination(Request $request): JsonResponse|array
    {
        $pageRaw = $request->query->get('page');
        $itemsRaw = $request->query->get('itemsPerPage');

        if (
            ($pageRaw === null || $pageRaw === '')
            && ($itemsRaw === null || $itemsRaw === '')
        ) {
            return [null, null];
        }

        if ($pageRaw !== null && $pageRaw !== '' && (!ctype_digit((string) $pageRaw) || (int) $pageRaw < 1)) {
            return $this->json(
                ['error' => 'Bad Request', 'message' => 'Invalid page.'],
                Response::HTTP_BAD_REQUEST
            );
        }

        if ($itemsRaw !== null && $itemsRaw !== '' && !ctype_digit((string) $itemsRaw)) {
            return $this->json(
                ['error' => 'Bad Request', 'message' => 'Invalid itemsPerPage.'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $page = ($pageRaw === null || $pageRaw === '') ? 1 : (int) $pageRaw;
        $itemsPerPage = ($itemsRaw === null || $itemsRaw === '') ? 30 : max(1, min(100, (int) $itemsRaw));

        return [($page - 1) * $itemsPerPage, $itemsPerPage];
    }

    #[Route('/{id}', name: 'app_appointment_get', methods: ['GET'])]
    public function get(Appointment $appointment): JsonResponse
    {
        return $this->json($this->appointmentMapper->toResponseDto($appointment));
    }

    #[Route('', name: 'app_appointment_create', methods: ['POST'])]
    public function create(#[MapRequestPayload] AppointmentCreateDTO $appointmentCreateDTO): JsonResponse
    {
        $appointment = $this->appointmentMapper->toEntity($appointmentCreateDTO);
        
        $validationError = $this->validateAppointmentConstraints($appointment);
        if ($validationError) {
            return $validationError;
        }

        $this->entityManager->persist($appointment);
        $this->entityManager->flush();

        // Send confirmation email
        $this->emailService->sendAppointmentConfirmation($appointment);

        return $this->json(
            $this->appointmentMapper->toResponseDto($appointment),
            Response::HTTP_CREATED
        );
    }

    #[Route('/{id}', name: 'app_appointment_update', methods: ['PUT', 'PATCH'])]
    public function update(Appointment $appointment, #[MapRequestPayload] AppointmentUpdateDTO $appointmentUpdateDTO): JsonResponse
    {
        $updatedAppointment = $this->appointmentMapper->updateEntity(clone $appointment, $appointmentUpdateDTO);
        
        $validationError = $this->validateAppointmentConstraints($updatedAppointment, $appointment->getId());
        if ($validationError) {
            return $validationError;
        }

        $this->appointmentMapper->updateEntity($appointment, $appointmentUpdateDTO);
        $this->entityManager->flush();

        return $this->json($this->appointmentMapper->toResponseDto($appointment));
    }

    #[Route('/{id}/cancel', name: 'app_appointment_cancel', methods: ['PUT'])]
    public function cancel(Appointment $appointment): JsonResponse
    {
        $appointment->setEstado('cancelada');
        $this->entityManager->flush();

        // Send cancellation email
        $this->emailService->sendAppointmentCancellation($appointment);

        return $this->json($this->appointmentMapper->toResponseDto($appointment));
    }

    #[Route('/{id}/reschedule', name: 'app_appointment_reschedule', methods: ['PUT'])]
    public function reschedule(
        Appointment $appointment, 
        #[MapRequestPayload] AppointmentRescheduleDTO $rescheduleDTO
    ): JsonResponse {
        // Create a temporary clone to validate the new schedule
        $testAppointment = clone $appointment;
        $testAppointment->setFecha(new \DateTime($rescheduleDTO->fecha));
        $testAppointment->setHoraInicio(new \DateTime($rescheduleDTO->hora_inicio));
        $testAppointment->setDuracion($rescheduleDTO->duracion);

        $validationError = $this->validateAppointmentConstraints($testAppointment, $appointment->getId());
        if ($validationError) {
            return $validationError;
        }

        // Apply changes if valid
        $appointment->setFecha(new \DateTime($rescheduleDTO->fecha));
        $appointment->setHoraInicio(new \DateTime($rescheduleDTO->hora_inicio));
        $appointment->setDuracion($rescheduleDTO->duracion);
        $appointment->setEstado('programada'); // Reset state if it was cancelled or similar

        $this->entityManager->flush();

        return $this->json($this->appointmentMapper->toResponseDto($appointment));
    }

    #[Route('/{id}', name: 'app_appointment_delete', methods: ['DELETE'])]
    public function delete(Appointment $appointment): JsonResponse
    {
        $this->entityManager->remove($appointment);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Centralized validation for box availability, dentist overlaps (with gap), and box overlaps.
     */
    private function validateAppointmentConstraints(Appointment $appointment, ?int $excludeId = null): ?JsonResponse
    {
        // 1. Check Box status
        if ($appointment->getBox()->getEstado() !== 'disponible') {
            return $this->json([
                'error' => 'Conflict',
                'message' => 'The selected box is currently ' . $appointment->getBox()->getEstado() . ' and cannot be used.'
            ], Response::HTTP_CONFLICT);
        }

        // 2. Check for overlaps for Dentist (with 5-minute gap)
        $dentistOverlaps = $this->appointmentRepository->findOverlaps(
            $appointment->getOdontologo(),
            null,
            $appointment->getFecha(),
            $appointment->getHoraInicio(),
            $appointment->getDuracion(),
            $excludeId,
            5 // Enforce 5-minute gap
        );

        if (!empty($dentistOverlaps)) {
            return $this->json([
                'error' => 'Conflict',
                'message' => 'The dentist is already occupied or does not have the required 5-minute gap between appointments.'
            ], Response::HTTP_CONFLICT);
        }

        // 3. Check for overlaps for Box
        $boxOverlaps = $this->appointmentRepository->findOverlaps(
            null,
            $appointment->getBox(),
            $appointment->getFecha(),
            $appointment->getHoraInicio(),
            $appointment->getDuracion(),
            $excludeId
        );

        if (!empty($boxOverlaps)) {
            return $this->json([
                'error' => 'Conflict',
                'message' => 'The box is already occupied during this time range.'
            ], Response::HTTP_CONFLICT);
        }

        // 4. Check infectious patient rules: must be scheduled last of the day
        if ($appointment->getIsInfectious()) {
            $laterAppointments = $this->appointmentRepository->findLaterAppointmentsSameDay(
                $appointment->getOdontologo(),
                $appointment->getBox(),
                $appointment->getFecha(),
                $appointment->getHoraInicio(),
                $excludeId
            );

            if (!empty($laterAppointments)) {
                return $this->json([
                    'error' => 'Conflict',
                    'message' => 'Infectious patients must be scheduled as the last appointment of the day (Peticiones #5).'
                ], Response::HTTP_CONFLICT);
            }
        }

        return null;
    }
}
