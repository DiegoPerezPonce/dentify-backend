<?php

namespace App\Dto;

class AppointmentResponseDTO
{
    public ?int $id = null;
    public ?int $pacienteId = null;
    public ?string $pacienteNombre = null;
    public ?int $odontologoId = null;
    public ?string $odontologoNombre = null;
    public ?int $boxId = null;
    public ?string $boxNombre = null;
    public ?string $fecha = null;
    public ?string $hora_inicio = null;
    public ?int $duracion = null;
    public ?string $prioridad = null;
    public ?string $estado = null;
}
