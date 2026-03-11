<?php

namespace App\Dto;

class OdontogramaResponseDTO
{
    public ?int $id = null;
    public array $dientes = [];
    public ?string $fecha_creacion = null;
    public ?string $fecha_modificacion = null;
}
