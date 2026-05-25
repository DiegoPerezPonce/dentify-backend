<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class BoxCreateDTO
{
    #[Assert\NotBlank]
    public ?string $nombre = null;

    public ?string $descripcion = null;

    #[Assert\NotBlank]
    public ?string $estado = 'disponible';
}
