<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class OdontogramaBridgeDTO
{
    #[Assert\NotBlank]
    public ?string $diente_inicio = null;

    #[Assert\NotBlank]
    public ?string $diente_fin = null;

    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['PUENTE_REALIZADO', 'PUENTE_FUTURO'])]
    public ?string $estado = null;
}
