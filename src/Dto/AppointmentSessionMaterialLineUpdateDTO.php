<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class AppointmentSessionMaterialLineUpdateDTO
{
    public ?int $id = null;

    #[Assert\Positive]
    public ?int $stock_material_id = null;

    #[Assert\Positive]
    public ?float $cantidad_usada = null;

    public ?float $cantidad_planificada = null;
}
