<?php

namespace App\Controller;

use App\Dto\AppointmentCreateDTO;
use App\Dto\AppointmentUpdateDTO;
use App\Entity\Appointment;
use App\Mapper\AppointmentMapper;
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
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('', name: 'app_appointment_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $patientId = $request->query->get('patientId');
        $dentistId = $request->query->get('dentistId');
        $fecha = $request->query->get('fecha');

        $criteria = [];
        if ($patientId) $criteria['paciente'] = $patientId;
        if ($dentistId) $criteria['odontologo'] = $dentistId;
        if ($fecha) $criteria['fecha'] = new \DateTime($fecha);

        $appointments = $this->appointmentRepository->findBy($criteria);

        $responseDtos = array_map(
            fn(Appointment $appointment) => $this->appointmentMapper->toResponseDto($appointment),
            $appointments
        );

        return $this->json($responseDtos);
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
            null,
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
            $appointment->getDuracion()
        );

        if (!empty($boxOverlaps)) {
            return $this->json([
                'error' => 'Conflict',
                'message' => 'The box is already occupied during this time range.'
            ], Response::HTTP_CONFLICT);
        }

        $this->entityManager->persist($appointment);
        $this->entityManager->flush();

        return $this->json(
            $this->appointmentMapper->toResponseDto($appointment),
            Response::HTTP_CREATED
        );
    }

    #[Route('/{id}', name: 'app_appointment_update', methods: ['PUT', 'PATCH'])]
    public function update(Appointment $appointment, #[MapRequestPayload] AppointmentUpdateDTO $appointmentUpdateDTO): JsonResponse
    {
        $updatedAppointment = $this->appointmentMapper->updateEntity(clone $appointment, $appointmentUpdateDTO);
        
        // 1. Check Box status if changed
        if ($updatedAppointment->getBox() !== $appointment->getBox() && $updatedAppointment->getBox()->getEstado() !== 'disponible') {
            return $this->json([
                'error' => 'Conflict',
                'message' => 'The selected box is currently ' . $updatedAppointment->getBox()->getEstado() . ' and cannot be used.'
            ], Response::HTTP_CONFLICT);
        }

        // 2. Check for overlaps for Dentist (with 5-minute gap)
        $dentistOverlaps = $this->appointmentRepository->findOverlaps(
            $updatedAppointment->getOdontologo(),
            null,
            $updatedAppointment->getFecha(),
            $updatedAppointment->getHoraInicio(),
            $updatedAppointment->getDuracion(),
            $appointment->getId(),
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
            $updatedAppointment->getBox(),
            $updatedAppointment->getFecha(),
            $updatedAppointment->getHoraInicio(),
            $updatedAppointment->getDuracion(),
            $appointment->getId()
        );

        if (!empty($boxOverlaps)) {
            return $this->json([
                'error' => 'Conflict',
                'message' => 'The box is already occupied during this time range.'
            ], Response::HTTP_CONFLICT);
        }

        $this->appointmentMapper->updateEntity($appointment, $appointmentUpdateDTO);
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
}
