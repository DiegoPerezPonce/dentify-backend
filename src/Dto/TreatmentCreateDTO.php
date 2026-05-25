<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class TreatmentCreateDTO
{
    #[Assert\NotBlank]
    public ?string $name = null;

    #[Assert\NotNull]
    public ?int $categoryId = null;

    #[Assert\NotNull]
    public ?int $dentistSpecialtyId = null;

    #[Assert\Positive]
    public int $defaultDurationMinutes = 30;

    public bool $allowsUrgency = true;

    public bool $forFirstVisit = false;
}
