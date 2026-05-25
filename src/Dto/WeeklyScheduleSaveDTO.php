<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class WeeklyScheduleSaveDTO
{
    /** @var WeeklyScheduleDayInputDTO[] */
    #[Assert\Count(min: 1)]
    #[Assert\Valid]
    public array $days = [];
}
