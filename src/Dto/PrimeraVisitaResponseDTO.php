<?php

namespace App\Dto;

class PrimeraVisitaResponseDTO
{
    public ?int $id = null;
    public ?string $motivo_consulta = null;
    public ?int $nivel_dolor = null;
    public ?string $observaciones = null;
    public ?string $fecha = null;
    public ?int $patient_id = null;
    public ?int $odontograma_id = null;
}
