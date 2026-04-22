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
    public ?string $treatment = null;
    public bool $isInfectious = false;
    public ?string $notes = null;
    
    // Campos adicionales para compatibilidad con frontend
    public ?string $startDateTime = null;
    public ?string $endDateTime = null;
    public ?int $patientId = null;
    public ?string $patientName = null;
    public ?int $dentistId = null;
    public ?string $dentistName = null;
    public ?string $status = null;
    public ?bool $isInfectiousPatient = null;
}
