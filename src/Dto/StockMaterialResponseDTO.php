<?php

namespace App\Dto;

class StockMaterialResponseDTO
{
    public ?int $id = null;
    public ?string $nombre = null;
    public ?float $cantidad_actual = null;
    public ?float $umbral_minimo = null;
    public bool $is_low_stock = false;
    public ?string $unidad = null;
    public ?string $fecha_ultima_reposicion = null;
}
