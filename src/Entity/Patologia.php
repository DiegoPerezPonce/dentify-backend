<?php

namespace App\Entity;

use App\Repository\PatologiaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PatologiaRepository::class)]
#[ORM\Table(name: 'patologia')]
class Patologia
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $diente = null;

    #[ORM\Column(length: 255)]
    private ?string $cara = null;

    #[ORM\Column(length: 255)]
    private ?string $tipo_patologia = null;

    #[ORM\Column(length: 255)]
    private ?string $estado = null;

    #[ORM\Column(length: 50)]
    private ?string $color = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $fecha = null;

    #[ORM\ManyToOne(inversedBy: 'patologias')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Odontograma $odontograma = null;

    public function __construct()
    {
        $this->fecha = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDiente(): ?string
    {
        return $this->diente;
    }

    public function setDiente(string $diente): static
    {
        $this->diente = $diente;

        return $this;
    }

    public function getCara(): ?string
    {
        return $this->cara;
    }

    public function setCara(string $cara): static
    {
        $this->cara = $cara;

        return $this;
    }

    public function getTipoPatologia(): ?string
    {
        return $this->tipo_patologia;
    }

    public function setTipoPatologia(string $tipo_patologia): static
    {
        $this->tipo_patologia = $tipo_patologia;

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

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): static
    {
        $this->color = $color;

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

    public function getOdontograma(): ?Odontograma
    {
        return $this->odontograma;
    }

    public function setOdontograma(?Odontograma $odontograma): static
    {
        $this->odontograma = $odontograma;

        return $this;
    }
}
