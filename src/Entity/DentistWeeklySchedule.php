<?php

namespace App\Entity;

use App\Repository\DentistWeeklyScheduleRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DentistWeeklyScheduleRepository::class)]
#[ORM\Table(name: 'dentist_weekly_schedule')]
#[ORM\UniqueConstraint(name: 'UNIQ_DENTIST_WEEKDAY', columns: ['dentist_id', 'weekday'])]
class DentistWeeklySchedule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Dentist::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull]
    private ?Dentist $dentist = null;

    /** 1 = Lunes … 6 = Sábado */
    #[ORM\Column(type: 'smallint')]
    private int $weekday = 1;

    #[ORM\Column(length: 16)]
    private string $shift = 'off';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDentist(): ?Dentist
    {
        return $this->dentist;
    }

    public function setDentist(?Dentist $dentist): static
    {
        $this->dentist = $dentist;

        return $this;
    }

    public function getWeekday(): int
    {
        return $this->weekday;
    }

    public function setWeekday(int $weekday): static
    {
        $this->weekday = $weekday;

        return $this;
    }

    public function getShift(): string
    {
        return $this->shift;
    }

    public function setShift(string $shift): static
    {
        $this->shift = $shift;

        return $this;
    }
}
