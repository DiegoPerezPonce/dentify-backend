<?php

namespace App\Dto;

class DentistResponseDTO
{
    public ?int $id = null;
    public ?string $nombre = null;
    public ?string $apellidos = null;
    public ?string $especialidad = null;
    public ?string $email = null;
    /** @var array<int, string> */
    public array $boxes = [];
}
