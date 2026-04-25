<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class ProtocoloTratamientoUpdateDTO
{
    public ?string $nombre_tratamiento = null;

    #[Assert\Positive]
    public ?int $material_id = null;

    #[Assert\Positive]
    public ?float $cantidad_necesaria = null;

    public ?string $notas_paso_a_paso = null;
}
