<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class OdontogramaToothStatusDTO
{
    #[Assert\NotBlank]
    public ?string $diente = null;

    #[Assert\NotBlank]
    public ?string $cara = null;

    #[Assert\NotBlank]
    public ?string $estado = null;

    #[Assert\NotBlank]
    public ?string $color = null;

    #[Assert\NotBlank]
    public ?string $tipo = null;
}
