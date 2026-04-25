<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class BoxUpdateDTO
{
    public ?string $nombre = null;

    public ?string $descripcion = null;

    public ?string $estado = null;

    /** When true, removes dentist assignment (takes precedence over dentist_id). */
    public bool $clear_dentist = false;

    #[Assert\Positive]
    public ?int $dentist_id = null;
}
