<?php

namespace App\Dto;

class AppointmentUpdateDTO
{
    public ?int $pacienteId = null;
    public ?int $odontologoId = null;
    public ?int $boxId = null;
    public ?string $fecha = null;
    public ?string $hora_inicio = null;
    public ?int $duracion = null;
    public ?string $prioridad = null;
    public ?string $estado = null;
}
