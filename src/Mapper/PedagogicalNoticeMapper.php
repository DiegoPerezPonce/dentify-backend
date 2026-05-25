<?php

namespace App\Mapper;

use App\Dto\PedagogicalNoticeCreateDTO;
use App\Dto\PedagogicalNoticeUpdateDTO;
use App\Dto\PedagogicalNoticeResponseDTO;
use App\Entity\PedagogicalNotice;

class PedagogicalNoticeMapper
{
    public function toEntity(PedagogicalNoticeCreateDTO $dto): PedagogicalNotice
    {
        $entity = new PedagogicalNotice();
        $now = new \DateTimeImmutable();

        $entity->setTitle(trim((string) $dto->title));
        $entity->setBody(trim((string) $dto->body));
        $entity->setActive($dto->active ?? true);
        $entity->setPublishedAt($this->parseImmutableOrNow($dto->published_at) ?? $now);
        $entity->setExpiresAt($this->parseImmutableOrNull($dto->expires_at));
        $entity->setRequiresAck($dto->requires_ack ?? false);
        $entity->setCreatedAt($now);
        $entity->setUpdatedAt(null);

        return $entity;
    }

    public function updateEntity(PedagogicalNotice $entity, PedagogicalNoticeUpdateDTO $dto): PedagogicalNotice
    {
        if ($dto->title !== null) {
            $entity->setTitle(trim($dto->title));
        }
        if ($dto->body !== null) {
            $entity->setBody(trim($dto->body));
        }
        if ($dto->active !== null) {
            $entity->setActive($dto->active);
        }
        if ($dto->published_at !== null) {
            $t = trim($dto->published_at);
            if ($t === '') {
                throw new \InvalidArgumentException('published_at cannot be empty when provided.');
            }
            $entity->setPublishedAt($this->parseImmutable($t));
        }
        if ($dto->expires_at !== null) {
            $t = trim($dto->expires_at);
            $entity->setExpiresAt($t === '' ? null : $this->parseImmutable($t));
        }
        if ($dto->requires_ack !== null) {
            $entity->setRequiresAck($dto->requires_ack);
        }
        $entity->setUpdatedAt(new \DateTimeImmutable());

        return $entity;
    }

    public function toResponseDto(PedagogicalNotice $entity): PedagogicalNoticeResponseDTO
    {
        $dto = new PedagogicalNoticeResponseDTO();
        $dto->id = $entity->getId();
        $dto->title = $entity->getTitle();
        $dto->body = $entity->getBody();
        $dto->active = $entity->isActive();
        $dto->published_at = $entity->getPublishedAt()?->format('c');
        $dto->expires_at = $entity->getExpiresAt()?->format('c');
        $dto->requires_ack = $entity->isRequiresAck();
        $dto->created_at = $entity->getCreatedAt()?->format('c');
        $dto->updated_at = $entity->getUpdatedAt()?->format('c');
        $dto->created_by_id = $entity->getCreatedBy()?->getId();

        return $dto;
    }

    private function parseImmutableOrNow(?string $raw): ?\DateTimeImmutable
    {
        if ($raw === null || trim($raw) === '') {
            return null;
        }

        return $this->parseImmutable(trim($raw));
    }

    private function parseImmutableOrNull(?string $raw): ?\DateTimeImmutable
    {
        if ($raw === null || trim($raw) === '') {
            return null;
        }

        return $this->parseImmutable(trim($raw));
    }

    private function parseImmutable(string $raw): \DateTimeImmutable
    {
        try {
            return new \DateTimeImmutable($raw);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException('Invalid date: ' . $raw, 0, $e);
        }
    }
}
