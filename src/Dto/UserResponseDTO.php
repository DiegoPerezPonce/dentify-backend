<?php

namespace App\Dto;

class UserResponseDTO
{
    public ?int $id = null;
    public ?string $email = null;
    public ?string $nombre_usuario = null;
    public array $roles = [];
    public ?string $ultimo_acceso = null;

    public ?int $dentistId = null;

    public ?string $dentistNombre = null;
    public ?string $dentistApellidos = null;
    public ?string $dentistEspecialidad = null;

    public ?UserUiPreferencesDTO $ui_preferences = null;
}
