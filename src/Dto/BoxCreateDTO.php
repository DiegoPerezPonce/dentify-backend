<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class BoxCreateDTO
{
    #[Assert\NotBlank]
    public ?string $nombre = null;

    public ?string $descripcion = null;

    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['disponible', 'mantenimiento'], message: 'El estado debe ser disponible o mantenimiento.')]
    public ?string $estado = 'disponible';
}
