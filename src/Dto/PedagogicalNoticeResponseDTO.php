<?php

namespace App\Dto;

class PedagogicalNoticeResponseDTO
{
    /** Hub discriminator for FE #21 (appointments / stock / pedagogical). */
    public string $type = 'pedagogical_notice';

    public ?int $id = null;
    public ?string $title = null;
    public ?string $body = null;
    public bool $active = true;
    public ?string $published_at = null;
    public ?string $expires_at = null;
    public bool $requires_ack = false;
    public ?string $created_at = null;
    public ?string $updated_at = null;
    public ?int $created_by_id = null;
}
