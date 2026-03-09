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
}
