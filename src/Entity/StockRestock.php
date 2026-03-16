<?php

namespace App\Entity;

use App\Repository\StockRestockRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: StockRestockRepository::class)]
#[ORM\Table(name: 'stock_restock')]
class StockRestock
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: StockMaterial::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?StockMaterial $material = null;

    #[ORM\Column(type: Types::FLOAT)]
    #[Assert\NotBlank]
    #[Assert\Positive]
    private ?float $cantidad_recibida = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $proveedor = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank]
    private ?\DateTimeInterface $fecha = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCantidadRecibida(): ?float
    {
        return $this->cantidad_recibida;
    }

    public function setCantidadRecibida(float $cantidad_recibida): static
    {
        $this->cantidad_recibida = $cantidad_recibida;

        return $this;
    }

    public function getProveedor(): ?string
    {
        return $this->proveedor;
    }

    public function setProveedor(string $proveedor): static
    {
        $this->proveedor = $proveedor;

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
}
