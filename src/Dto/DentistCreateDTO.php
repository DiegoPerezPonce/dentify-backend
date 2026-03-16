<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class DentistCreateDTO
{
    #[Assert\NotBlank]
    public ?string $nombre = null;

    #[Assert\NotBlank]
    public ?string $apellidos = null;

    #[Assert\NotBlank]
    public ?string $especialidad = null;

    #[Assert\NotBlank]
    #[Assert\Email]
    public ?string $email = null;
}
