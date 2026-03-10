<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class PrimeraVisitaCreateDTO
{
    #[Assert\NotBlank]
    public ?int $patient_id = null;

    #[Assert\NotBlank]
    public ?string $motivo_consulta = null;

    #[Assert\NotBlank]
    #[Assert\Range(min: 0, max: 10)]
    public ?int $nivel_dolor = null;

    public ?string $observaciones = null;

    public ?int $odontograma_id = null;
}
