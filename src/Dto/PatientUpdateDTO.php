<?php

namespace App\Dto;

use App\Domain\Patient\MedicalFlags;
use Symfony\Component\Validator\Constraints as Assert;

class PatientUpdateDTO
{
    public ?string $nombre = null;

    public ?string $apellidos = null;

    public ?string $dni = null;

    public ?string $nss = null;

    public ?string $telefono = null;

    #[Assert\Email]
    public ?string $email = null;

    public ?string $enfermedades = null;

    public ?string $alergias = null;

    public ?string $historial_clinico = null;

    public ?string $datos_facturacion = null;

    /**
     * @var list<string>|null
     */
    #[Assert\All([
        new Assert\Type('string'),
        new Assert\Length(max: MedicalFlags::CUSTOM_FLAG_MAX_BYTES),
        new Assert\Callback(callback: [MedicalFlags::class, 'validateMedicalFlagElement']),
    ])]
    public ?array $medical_flags = null;

    #[Assert\Length(max: 8000)]
    public ?string $medical_notes = null;

    public ?bool $terminos_generales_aceptados = null;
}
