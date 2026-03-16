<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class ProtocoloTratamientoCreateDTO
{
    #[Assert\NotBlank]
    public ?string $nombre_tratamiento = null;

    #[Assert\NotBlank]
    public ?int $material_id = null;

    #[Assert\NotBlank]
    #[Assert\Positive]
    public ?float $cantidad_necesaria = null;

    public ?string $notas_paso_a_paso = null;
}
