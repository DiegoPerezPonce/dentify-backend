<?php

namespace App\Entity;

use App\Repository\AppointmentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AppointmentRepository::class)]
#[ORM\Table(name: 'appointment')]
class Appointment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Patient::class, inversedBy: 'appointments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?Patient $paciente = null;

    #[ORM\ManyToOne(targetEntity: Dentist::class, inversedBy: 'appointments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?Dentist $odontologo = null;

    #[ORM\ManyToOne(targetEntity: Box::class, inversedBy: 'appointments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?Box $box = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank]
    private ?\DateTimeInterface $fecha = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    #[Assert\NotBlank]
    private ?\DateTimeInterface $hora_inicio = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    private ?int $duracion = 30;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank]
    private ?string $prioridad = 'media';

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank]
    private ?string $estado = 'programada';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPaciente(): ?Patient
    {
        return $this->paciente;
    }

    public function setPaciente(?Patient $paciente): static
    {
        $this->paciente = $paciente;

        return $this;
    }

    public function getOdontologo(): ?Dentist
    {
        return $this->odontologo;
    }

    public function setOdontologo(?Dentist $odontologo): static
    {
        $this->odontologo = $odontologo;

        return $this;
    }

    public function getBox(): ?Box
    {
        return $this->box;
    }

    public function setBox(?Box $box): static
    {
        $this->box = $box;

        return $this;
    }

    public function getFecha(): ?\DateTimeInterface
    {
        return $this->fecha;
    }

    public function setFecha(\DateTimeInterface $fecha): static
    {
        $this->fecha = $fecha;

        return $this;
    }

    public function getHoraInicio(): ?\DateTimeInterface
    {
        return $this->hora_inicio;
    }

    public function setHoraInicio(\DateTimeInterface $hora_inicio): static
    {
        $this->hora_inicio = $hora_inicio;

        return $this;
    }

    public function getDuracion(): ?int
    {
        return $this->duracion;
    }

    public function setDuracion(int $duracion): static
    {
        $this->duracion = $duracion;

        return $this;
    }

    public function getPrioridad(): ?string
    {
        return $this->prioridad;
    }

    public function setPrioridad(string $prioridad): static
    {
        $this->prioridad = $prioridad;

        return $this;
    }

    public function getEstado(): ?string
    {
        return $this->estado;
    }

    public function setEstado(string $estado): static
    {
        $this->estado = $estado;

        return $this;
    }
}
