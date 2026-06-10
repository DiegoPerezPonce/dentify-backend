<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class AppointmentSessionMaterialsUpdateDTO
{
    /** @var AppointmentSessionMaterialLineUpdateDTO[] */
    #[Assert\Valid]
    #[Assert\Count(min: 0)]
    public array $materials = [];
}
