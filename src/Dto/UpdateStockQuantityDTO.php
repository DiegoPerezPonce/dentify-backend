<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateStockQuantityDTO
{
    #[Assert\NotBlank]
    #[Assert\Positive]
    public ?float $cantidad = null;
}
