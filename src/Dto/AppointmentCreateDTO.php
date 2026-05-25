<?php

namespace App\Dto;

use App\Domain\Appointment\AppointmentKind;
use Symfony\Component\Validator\Constraints as Assert;

class AppointmentCreateDTO
{
    #[Assert\NotNull]
    public ?int $pacienteId = null;

    #[Assert\NotNull]
    public ?int $odontologoId = null;

    #[Assert\NotNull]
    public ?int $boxId = null;

    #[Assert\NotBlank]
    #[Assert\Date]
    public ?string $fecha = null;

    #[Assert\NotBlank]
    #[Assert\Time]
    public ?string $hora_inicio = null;

    #[Assert\NotBlank]
    public ?int $duracion = 30;

    public int $tiempo_limpieza = 5;

    #[Assert\NotBlank]
    public ?string $prioridad = 'media';

    #[Assert\NotBlank]
    public ?string $estado = 'programada';

    #[Assert\Choice(callback: [AppointmentKind::class, 'all'])]
    public ?string $appointment_kind = 'tratamiento';

    public ?int $catalog_treatment_id = null;

    public ?string $treatment = null;

    public bool $isInfectious = false;

    public ?string $notes = null;
}
