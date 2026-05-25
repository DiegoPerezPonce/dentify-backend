<?php

namespace App\Dto;

class OdontogramaResponseDTO
{
    public ?int $id = null;
    public array $dientes = [];
    /** @var list<array<string, mixed>> */
    public array $puentes = [];
    public ?string $fecha_creacion = null;
    public ?string $fecha_modificacion = null;
}
