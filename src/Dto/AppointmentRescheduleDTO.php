<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class AppointmentRescheduleDTO
{
    #[Assert\NotBlank]
    public ?string $fecha = null;

    #[Assert\NotBlank]
    public ?string $hora_inicio = null;

    #[Assert\NotBlank]
    public ?int $duracion = null;
}
