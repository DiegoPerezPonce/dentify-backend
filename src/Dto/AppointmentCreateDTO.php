<?php

namespace App\Dto;

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

    #[Assert\NotBlank]
    public ?string $prioridad = 'media';

    #[Assert\NotBlank]
    public ?string $estado = 'programada';
}
