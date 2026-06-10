<?php

namespace App\Entity;

use App\Repository\AppointmentSessionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AppointmentSessionRepository::class)]
#[ORM\Table(name: 'appointment_session')]
class AppointmentSession
{
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Appointment::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Appointment $appointment = null;

    #[ORM\Column(length: 20)]
    private string $status = self::STATUS_IN_PROGRESS;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $started_at = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $completed_at = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    /** @var Collection<int, AppointmentSessionMaterial> */
    #[ORM\OneToMany(targetEntity: AppointmentSessionMaterial::class, mappedBy: 'session', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $materials;

    public function __construct()
    {
        $this->materials = new ArrayCollection();
        $this->started_at = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAppointment(): ?Appointment
    {
        return $this->appointment;
    }

    public function setAppointment(Appointment $appointment): static
    {
        $this->appointment = $appointment;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getStartedAt(): ?\DateTimeInterface
    {
        return $this->started_at;
    }

    public function setStartedAt(\DateTimeInterface $started_at): static
    {
        $this->started_at = $started_at;

        return $this;
    }

    public function getCompletedAt(): ?\DateTimeInterface
    {
        return $this->completed_at;
    }

    public function setCompletedAt(?\DateTimeInterface $completed_at): static
    {
        $this->completed_at = $completed_at;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;

        return $this;
    }

    /** @return Collection<int, AppointmentSessionMaterial> */
    public function getMaterials(): Collection
    {
        return $this->materials;
    }

    public function addMaterial(AppointmentSessionMaterial $material): static
    {
        if (!$this->materials->contains($material)) {
            $this->materials->add($material);
            $material->setSession($this);
        }

        return $this;
    }

    public function removeMaterial(AppointmentSessionMaterial $material): static
    {
        if ($this->materials->removeElement($material)) {
            if ($material->getSession() === $this) {
                $material->setSession(null);
            }
        }

        return $this;
    }
}
