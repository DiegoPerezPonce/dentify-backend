<?php

namespace App\Dto;

class UserResponseDTO
{
    public ?int $id = null;
    public ?string $email = null;
    public ?string $nombre_usuario = null;
    public array $roles = [];
    public ?string $ultimo_acceso = null;
}
