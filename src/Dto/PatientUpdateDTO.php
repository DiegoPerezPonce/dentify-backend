<?php

namespace App\Dto;

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
}
