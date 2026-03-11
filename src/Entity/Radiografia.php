<?php

namespace App\Entity;

use App\Repository\RadiografiaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RadiografiaRepository::class)]
#[ORM\Table(name: 'radiografia')]
class Radiografia
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre_archivo = null;

    #[ORM\Column(length: 255)]
    private ?string $ruta_archivo = null;

    #[ORM\Column(length: 100)]
    private ?string $tipo_mime = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $fecha_subida = null;

    #[ORM\ManyToOne(inversedBy: 'radiografias')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Patient $patient = null;

    public function __construct()
    {
        $this->fecha_subida = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombreArchivo(): ?string
    {
        return $this->nombre_archivo;
    }

    public function setNombreArchivo(string $nombre_archivo): static
    {
        $this->nombre_archivo = $nombre_archivo;

        return $this;
    }

    public function getRutaArchivo(): ?string
    {
        return $this->ruta_archivo;
    }

    public function setRutaArchivo(string $ruta_archivo): static
    {
        $this->ruta_archivo = $ruta_archivo;

        return $this;
    }

    public function getTipoMime(): ?string
    {
        return $this->tipo_mime;
    }

    public function setTipoMime(string $tipo_mime): static
    {
        $this->tipo_mime = $tipo_mime;

        return $this;
    }

    public function getFechaSubida(): ?\DateTimeImmutable
    {
        return $this->fecha_subida;
    }

    public function setFechaSubida(\DateTimeImmutable $fecha_subida): static
    {
        $this->fecha_subida = $fecha_subida;

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
}
