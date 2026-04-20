<?php

namespace App\Dto;
//s
use Symfony\Component\Validator\Constraints as Assert;

class UserCreateDTO
{
    #[Assert\NotBlank]
    #[Assert\Email]
    public ?string $email = null;

    #[Assert\NotBlank]
    public ?string $password = null;

    #[Assert\NotBlank]
    public ?string $nombre_usuario = null;

    /**
     * @var list<string>
     */
    public array $roles = [];
}
