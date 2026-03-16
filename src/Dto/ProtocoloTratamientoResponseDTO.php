<?php

namespace App\Dto;

class ProtocoloTratamientoResponseDTO
{
    public ?int $id = null;
    public ?string $nombre_tratamiento = null;
    public ?StockMaterialResponseDTO $material = null;
    public ?float $cantidad_necesaria = null;
    public ?string $notas_paso_a_paso = null;
}
