<?php

namespace App\Entity;

use App\Repository\TreatmentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TreatmentRepository::class)]
#[ORM\Table(name: 'treatment')]
class Treatment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: TreatmentCategory::class, inversedBy: 'treatments')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull]
    private ?TreatmentCategory $category = null;

    #[ORM\ManyToOne(targetEntity: Specialty::class)]
    #[ORM\JoinColumn(name: 'dentist_specialty_id', nullable: false, onDelete: 'RESTRICT')]
    #[Assert\NotNull]
    private ?Specialty $dentistSpecialty = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column]
    private int $defaultDurationMinutes = 30;

    #[ORM\Column]
    private bool $allowsUrgency = true;

    #[ORM\Column]
    private bool $forFirstVisit = false;

    #[ORM\Column]
    private bool $isCustom = false;

    #[ORM\Column]
    private bool $active = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategory(): ?TreatmentCategory
    {
        return $this->category;
    }

    public function setCategory(?TreatmentCategory $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getDentistSpecialty(): ?Specialty
    {
        return $this->dentistSpecialty;
    }

    public function setDentistSpecialty(?Specialty $dentistSpecialty): static
    {
        $this->dentistSpecialty = $dentistSpecialty;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDefaultDurationMinutes(): int
    {
        return $this->defaultDurationMinutes;
    }

    public function setDefaultDurationMinutes(int $defaultDurationMinutes): static
    {
        $this->defaultDurationMinutes = $defaultDurationMinutes;

        return $this;
    }

    public function isAllowsUrgency(): bool
    {
        return $this->allowsUrgency;
    }

    public function setAllowsUrgency(bool $allowsUrgency): static
    {
        $this->allowsUrgency = $allowsUrgency;

        return $this;
    }

    public function isForFirstVisit(): bool
    {
        return $this->forFirstVisit;
    }

    public function setForFirstVisit(bool $forFirstVisit): static
    {
        $this->forFirstVisit = $forFirstVisit;

        return $this;
    }

    public function isCustom(): bool
    {
        return $this->isCustom;
    }

    public function setIsCustom(bool $isCustom): static
    {
        $this->isCustom = $isCustom;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }
}
