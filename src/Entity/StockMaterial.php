<?php

namespace App\Entity;

use App\Repository\StockMaterialRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: StockMaterialRepository::class)]
#[ORM\Table(name: 'stock_material')]
class StockMaterial
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $nombre = null;

    #[ORM\Column(type: Types::FLOAT)]
    #[Assert\NotBlank]
    #[Assert\PositiveOrZero]
    private ?float $cantidad_actual = null;

    /** Minimum stock level; cantidad_actual at or below this is considered low stock. */
    #[ORM\Column(type: Types::FLOAT)]
    #[Assert\PositiveOrZero]
    private ?float $umbral_minimo = 10.0;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    private ?string $unidad = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $fecha_ultima_reposicion = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getCantidadActual(): ?float
    {
        return $this->cantidad_actual;
    }

    public function setCantidadActual(float $cantidad_actual): static
    {
        $this->cantidad_actual = $cantidad_actual;

        return $this;
    }

    public function getUmbralMinimo(): ?float
    {
        return $this->umbral_minimo;
    }

    public function setUmbralMinimo(float $umbral_minimo): static
    {
        $this->umbral_minimo = $umbral_minimo;

        return $this;
    }

    public function getUnidad(): ?string
    {
        return $this->unidad;
    }

    public function setUnidad(string $unidad): static
    {
        $this->unidad = $unidad;

        return $this;
    }

    public function getFechaUltimaReposicion(): ?\DateTimeInterface
    {
        return $this->fecha_ultima_reposicion;
    }

    public function setFechaUltimaReposicion(?\DateTimeInterface $fecha_ultima_reposicion): static
    {
        $this->fecha_ultima_reposicion = $fecha_ultima_reposicion;

        return $this;
    }
}
