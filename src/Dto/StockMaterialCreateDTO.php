<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class StockMaterialCreateDTO
{
    #[Assert\NotBlank]
    public ?string $nombre = null;

    #[Assert\NotBlank]
    #[Assert\PositiveOrZero]
    public ?float $cantidad_actual = null;

    #[Assert\NotBlank]
    public ?string $unidad = null;

    #[Assert\PositiveOrZero]
    public ?float $umbral_minimo = 10.0;

    public ?string $fecha_ultima_reposicion = null;
}
