<?php

namespace App\Mapper;

use App\Dto\PrimeraVisitaCreateDTO;
use App\Dto\PrimeraVisitaResponseDTO;
use App\Entity\PrimeraVisita;
use App\Repository\PatientRepository;

class PrimeraVisitaMapper
{
    public function __construct(
        private PatientRepository $patientRepository
    ) {}

    public function toEntity(PrimeraVisitaCreateDTO $dto): PrimeraVisita
    {
        $visita = new PrimeraVisita();
        $visita->setMotivoConsulta($dto->motivo_consulta);
        $visita->setNivelDolor($dto->nivel_dolor);
        $visita->setObservaciones($dto->observaciones);
        $visita->setOdontogramaId($dto->odontograma_id);

        $patient = $this->patientRepository->find($dto->patient_id);
        if ($patient) {
            $visita->setPatient($patient);
        }

        return $visita;
    }

    public function toResponseDto(PrimeraVisita $visita): PrimeraVisitaResponseDTO
    {
        $dto = new PrimeraVisitaResponseDTO();
        $dto->id = $visita->getId();
        $dto->motivo_consulta = $visita->getMotivoConsulta();
        $dto->nivel_dolor = $visita->getNivelDolor();
        $dto->observaciones = $visita->getObservaciones();
        $dto->fecha = $visita->getFecha()?->format(\DateTimeInterface::ATOM);
        $dto->patient_id = $visita->getPatient()?->getId();
        $dto->odontograma_id = $visita->getOdontogramaId();

        return $dto;
    }
}
