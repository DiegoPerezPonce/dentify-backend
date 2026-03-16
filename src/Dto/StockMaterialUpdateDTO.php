<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class StockMaterialUpdateDTO
{
    public ?string $nombre = null;

    #[Assert\PositiveOrZero]
    public ?float $cantidad_actual = null;

    public ?string $unidad = null;

    public ?string $fecha_ultima_reposicion = null;
}
