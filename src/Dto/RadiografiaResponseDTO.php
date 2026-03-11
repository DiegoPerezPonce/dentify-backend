<?php

namespace App\Dto;

class RadiografiaResponseDTO
{
    public ?int $id = null;
    public ?string $nombre_archivo = null;
    public ?string $ruta_archivo = null;
    public ?string $tipo_mime = null;
    public ?string $fecha_subida = null;
}
