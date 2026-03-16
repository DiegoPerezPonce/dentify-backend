<?php

namespace App\Mapper;

use App\Dto\AppointmentCreateDTO;
use App\Dto\AppointmentResponseDTO;
use App\Dto\AppointmentUpdateDTO;
use App\Entity\Appointment;
use App\Repository\PatientRepository;
use App\Repository\DentistRepository;
use App\Repository\BoxRepository;

class AppointmentMapper
{
    public function __construct(
        private PatientRepository $patientRepository,
        private DentistRepository $dentistRepository,
        private BoxRepository $boxRepository
    ) {}

    public function toEntity(AppointmentCreateDTO $dto): Appointment
    {
        $appointment = new Appointment();
        $this->mapDtoToEntity($dto, $appointment);

        return $appointment;
    }

    public function updateEntity(Appointment $appointment, AppointmentUpdateDTO $dto): Appointment
    {
        $this->mapDtoToEntity($dto, $appointment);

        return $appointment;
    }

    private function mapDtoToEntity($dto, Appointment $appointment): void
    {
        if (property_exists($dto, 'pacienteId') && $dto->pacienteId !== null) {
            $appointment->setPaciente($this->patientRepository->find($dto->pacienteId));
        }
        if (property_exists($dto, 'odontologoId') && $dto->odontologoId !== null) {
            $appointment->setOdontologo($this->dentistRepository->find($dto->odontologoId));
        }
        if (property_exists($dto, 'boxId') && $dto->boxId !== null) {
            $appointment->setBox($this->boxRepository->find($dto->boxId));
        }
        if (property_exists($dto, 'fecha') && $dto->fecha !== null) {
            $appointment->setFecha(new \DateTime($dto->fecha));
        }
        if (property_exists($dto, 'hora_inicio') && $dto->hora_inicio !== null) {
            $appointment->setHoraInicio(new \DateTime($dto->hora_inicio));
        }
        if (property_exists($dto, 'duracion') && $dto->duracion !== null) {
            $appointment->setDuracion($dto->duracion);
        }
        if (property_exists($dto, 'prioridad') && $dto->prioridad !== null) {
            $appointment->setPrioridad($dto->prioridad);
        }
        if (property_exists($dto, 'estado') && $dto->estado !== null) {
            $appointment->setEstado($dto->estado);
        }
    }

    public function toResponseDto(Appointment $appointment): AppointmentResponseDTO
    {
        $dto = new AppointmentResponseDTO();
        $dto->id = $appointment->getId();
        $dto->pacienteId = $appointment->getPaciente()?->getId();
        $dto->pacienteNombre = $appointment->getPaciente()?->getNombre() . ' ' . $appointment->getPaciente()?->getApellidos();
        $dto->odontologoId = $appointment->getOdontologo()?->getId();
        $dto->odontologoNombre = $appointment->getOdontologo()?->getNombre() . ' ' . $appointment->getOdontologo()?->getApellidos();
        $dto->boxId = $appointment->getBox()?->getId();
        $dto->boxNombre = $appointment->getBox()?->getNombre();
        $dto->fecha = $appointment->getFecha()?->format('Y-m-d');
        $dto->hora_inicio = $appointment->getHoraInicio()?->format('H:i:s');
        $dto->duracion = $appointment->getDuracion();
        $dto->prioridad = $appointment->getPrioridad();
        $dto->estado = $appointment->getEstado();

        return $dto;
    }
}
