<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class UserUpdateDTO
{
    #[Assert\Email]
    public ?string $email = null;

    public ?string $password = null;

    public ?string $nombre_usuario = null;

    /**
     * @var list<string>|null
     */
    public ?array $roles = null;

    /** Odontólogo vinculado; null desvincula si el JSON incluye la clave. */
    public int|string|null $dentistId = null;

    public int|string|null $dentist_id = null;
}
