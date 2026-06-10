<?php

namespace App\Dto;

class AppointmentSessionResponseDTO
{
    public ?int $id = null;
    public ?int $appointment_id = null;
    public ?string $status = null;
    public ?string $started_at = null;
    public ?string $completed_at = null;
    public ?string $notes = null;
    public ?string $treatment_name = null;
    /** @var AppointmentSessionMaterialLineDTO[] */
    public array $materials = [];
}
