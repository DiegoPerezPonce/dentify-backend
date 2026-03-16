<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class DentistUpdateDTO
{
    public ?string $nombre = null;

    public ?string $apellidos = null;

    public ?string $especialidad = null;

    #[Assert\Email]
    public ?string $email = null;
}
