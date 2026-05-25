<?php

namespace App\Dto;

class PatientResponseDTO
{
    public ?int $id = null;
    public ?string $nombre = null;
    public ?string $apellidos = null;
    public ?string $dni = null;
    public ?string $nss = null;
    public ?string $telefono = null;
    public ?string $email = null;
    public ?string $enfermedades = null;
    public ?string $alergias = null;
    public ?string $historial_clinico = null;
    public ?string $datos_facturacion = null;

    /** @var list<string>|null */
    public ?array $medical_flags = null;

    public ?string $medical_notes = null;

    public bool $terminos_generales_aceptados = false;

    public bool $terminos_pendientes = true;
}
