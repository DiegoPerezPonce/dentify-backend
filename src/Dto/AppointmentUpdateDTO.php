<?php

namespace App\Dto;

use App\Domain\Appointment\AppointmentKind;
use Symfony\Component\Validator\Constraints as Assert;

class AppointmentUpdateDTO
{
    public ?int $pacienteId = null;
    public ?int $odontologoId = null;
    public ?int $boxId = null;
    public ?string $fecha = null;
    public ?string $hora_inicio = null;
    public ?int $duracion = null;
    public ?int $tiempo_limpieza = null;
    public ?string $prioridad = null;
    public ?string $estado = null;

    #[Assert\Choice(callback: [AppointmentKind::class, 'all'])]
    public ?string $appointment_kind = null;

    public ?int $catalog_treatment_id = null;

    public ?string $treatment = null;
    public ?bool $isInfectious = null;
    public ?string $notes = null;
}
