<?php

namespace App\Entity;

use App\Repository\ProtocoloTratamientoRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProtocoloTratamientoRepository::class)]
#[ORM\Table(name: 'protocolo_tratamiento')]
class ProtocoloTratamiento
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $nombre_tratamiento = null;

    #[ORM\ManyToOne(targetEntity: StockMaterial::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?StockMaterial $material = null;

    #[ORM\Column(type: Types::FLOAT)]
    #[Assert\NotBlank]
    #[Assert\Positive]
    private ?float $cantidad_necesaria = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notas_paso_a_paso = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombreTratamiento(): ?string
    {
        return $this->nombre_tratamiento;
    }

    public function setNombreTratamiento(string $nombre_tratamiento): static
    {
        $this->nombre_tratamiento = $nombre_tratamiento;

        return $this;
    }

    public function getMaterial(): ?StockMaterial
    {
        return $this->material;
    }

    public function setMaterial(?StockMaterial $material): static
    {
        $this->material = $material;

        return $this;
    }

    public function getCantidadNecesaria(): ?float
    {
        return $this->cantidad_necesaria;
    }

    public function setCantidadNecesaria(float $cantidad_necesaria): static
    {
        $this->cantidad_necesaria = $cantidad_necesaria;

        return $this;
    }

    public function getNotasPasoAPaso(): ?string
    {
        return $this->notas_paso_a_paso;
    }

    public function setNotasPasoAPaso(?string $notas_paso_a_paso): static
    {
        $this->notas_paso_a_paso = $notas_paso_a_paso;

        return $this;
    }
}
