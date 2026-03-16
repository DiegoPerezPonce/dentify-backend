<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class ChatbotRequestDTO
{
    #[Assert\NotBlank]
    public ?string $question = null;
}
