<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class StockRestockCreateDTO
{
    #[Assert\NotBlank]
    public ?int $material_id = null;

    #[Assert\NotBlank]
    #[Assert\Positive]
    public ?float $cantidad_recibida = null;

    #[Assert\NotBlank]
    public ?string $proveedor = null;

    #[Assert\NotBlank]
    public ?string $fecha = null;
}
