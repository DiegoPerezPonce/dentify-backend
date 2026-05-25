<?php

namespace App\Entity;

use App\Repository\ClinicScheduleSettingsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClinicScheduleSettingsRepository::class)]
#[ORM\Table(name: 'clinic_schedule_settings')]
class ClinicScheduleSettings
{
    #[ORM\Id]
    #[ORM\Column]
    private int $id = 1;

    #[ORM\Column(length: 5)]
    private string $openTime = '08:00';

    #[ORM\Column(length: 5)]
    private string $closeTime = '21:00';

    #[ORM\Column(length: 5)]
    private string $morningEndTime = '15:00';

    #[ORM\Column(length: 5)]
    private string $afternoonStartTime = '14:00';

    public function getId(): int
    {
        return $this->id;
    }

    public function getOpenTime(): string
    {
        return $this->openTime;
    }

    public function setOpenTime(string $openTime): static
    {
        $this->openTime = $openTime;

        return $this;
    }

    public function getCloseTime(): string
    {
        return $this->closeTime;
    }

    public function setCloseTime(string $closeTime): static
    {
        $this->closeTime = $closeTime;

        return $this;
    }

    public function getMorningEndTime(): string
    {
        return $this->morningEndTime;
    }

    public function setMorningEndTime(string $morningEndTime): static
    {
        $this->morningEndTime = $morningEndTime;

        return $this;
    }

    public function getAfternoonStartTime(): string
    {
        return $this->afternoonStartTime;
    }

    public function setAfternoonStartTime(string $afternoonStartTime): static
    {
        $this->afternoonStartTime = $afternoonStartTime;

        return $this;
    }
}
