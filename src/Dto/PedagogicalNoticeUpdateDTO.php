<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class PedagogicalNoticeUpdateDTO
{
    #[Assert\NotBlank(allowNull: true)]
    public ?string $title = null;

    #[Assert\NotBlank(allowNull: true)]
    public ?string $body = null;

    public ?bool $active = null;

    public ?string $published_at = null;

    public ?string $expires_at = null;

    public ?bool $requires_ack = null;
}
