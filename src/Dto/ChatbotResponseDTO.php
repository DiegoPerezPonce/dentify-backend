<?php

namespace App\Dto;

class ChatbotResponseDTO
{
    public ?string $answer = null;

    public function __construct(string $answer)
    {
        $this->answer = $answer;
    }
}
