<?php

namespace App\Dto;

class AppointmentSessionMaterialLineDTO
{
    public ?int $id = null;
    public ?int $stock_material_id = null;
    public ?string $material_nombre = null;
    public ?string $unidad = null;
    public float $cantidad_planificada = 0.0;
    public float $cantidad_usada = 0.0;
    public float $stock_disponible = 0.0;
    public bool $from_protocol = false;
    public ?string $protocol_notes = null;
}
