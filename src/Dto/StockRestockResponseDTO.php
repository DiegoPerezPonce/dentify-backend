<?php

namespace App\Dto;

class StockRestockResponseDTO
{
    public ?int $id = null;
    public ?StockMaterialResponseDTO $material = null;
    public ?float $cantidad_recibida = null;
    public ?string $proveedor = null;
    public ?string $fecha = null;
}
