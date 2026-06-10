<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class BoxUpdateDTO
{
    public ?string $nombre = null;

    public ?string $descripcion = null;

    #[Assert\Choice(choices: ['disponible', 'mantenimiento'], message: 'El estado debe ser disponible o mantenimiento.')]
    public ?string $estado = null;
}
