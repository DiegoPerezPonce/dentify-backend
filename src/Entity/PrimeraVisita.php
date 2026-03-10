<?php

namespace App\Entity;

use App\Repository\PrimeraVisitaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PrimeraVisitaRepository::class)]
#[ORM\Table(name: 'primera_visita')]
class PrimeraVisita
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $motivo_consulta = null;

    #[ORM\Column]
    private ?int $nivel_dolor = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $observaciones = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $fecha = null;

    #[ORM\ManyToOne(inversedBy: 'primerasVisitas')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Patient $patient = null;

    #[ORM\Column(nullable: true)]
    private ?int $odontograma_id = null;

    public function __construct()
    {
        $this->fecha = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMotivoConsulta(): ?string
    {
        return $this->motivo_consulta;
    }

    public function setMotivoConsulta(string $motivo_consulta): static
    {
        $this->motivo_consulta = $motivo_consulta;

        return $this;
    }

    public function getNivelDolor(): ?int
    {
        return $this->nivel_dolor;
    }

    public function setNivelDolor(int $nivel_dolor): static
    {
        $this->nivel_dolor = $nivel_dolor;

        return $this;
    }

    public function getObservaciones(): ?string
    {
        return $this->observaciones;
    }

    public function setObservaciones(?string $observaciones): static
    {
        $this->observaciones = $observaciones;

        return $this;
    }

    public function getFecha(): ?\DateTimeImmutable
    {
        return $this->fecha;
    }

    public function setFecha(\DateTimeImmutable $fecha): static
    {
        $this->fecha = $fecha;

        return $this;
    }

    public function getPatient(): ?Patient
    {
        return $this->patient;
    }

    public function setPatient(?Patient $patient): static
    {
        $this->patient = $patient;

        return $this;
    }

    public function getOdontogramaId(): ?int
    {
        return $this->odontograma_id;
    }

    public function setOdontogramaId(?int $odontograma_id): static
    {
        $this->odontograma_id = $odontograma_id;

        return $this;
    }
}
