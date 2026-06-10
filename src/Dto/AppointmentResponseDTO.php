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
    public ?int $tiempo_limpieza = null;
    public ?string $prioridad = null;
    public ?string $estado = null;
    public ?string $appointment_kind = null;
    public ?int $catalog_treatment_id = null;
    public ?string $catalog_treatment_name = null;
    public ?string $treatment_category_name = null;
    public ?string $specialty_name = null;
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
    public ?int $cleaningTimeMinutes = null;
    public ?int $treatmentDurationMinutes = null;
    public ?int $duration = null;

    /** Etiquetas médicas del paciente (VIH, alergias, etc.) para alertas en agenda. */
    public ?array $patientMedicalFlags = null;
}
