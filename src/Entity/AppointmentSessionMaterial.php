<?php

namespace App\Entity;

use App\Repository\AppointmentSessionMaterialRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AppointmentSessionMaterialRepository::class)]
#[ORM\Table(name: 'appointment_session_material')]
class AppointmentSessionMaterial
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: AppointmentSession::class, inversedBy: 'materials')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?AppointmentSession $session = null;

    #[ORM\ManyToOne(targetEntity: StockMaterial::class)]
    #[ORM\JoinColumn(name: 'stock_material_id', nullable: false, onDelete: 'RESTRICT')]
    private ?StockMaterial $material = null;

    #[ORM\Column(type: Types::FLOAT)]
    private float $cantidad_planificada = 0.0;

    #[ORM\Column(type: Types::FLOAT)]
    private float $cantidad_usada = 0.0;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $stock_reduced = false;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $from_protocol = false;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $protocol_notes = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSession(): ?AppointmentSession
    {
        return $this->session;
    }

    public function setSession(?AppointmentSession $session): static
    {
        $this->session = $session;

        return $this;
    }

    public function getMaterial(): ?StockMaterial
    {
        return $this->material;
    }

    public function setMaterial(StockMaterial $material): static
    {
        $this->material = $material;

        return $this;
    }

    public function getCantidadPlanificada(): float
    {
        return $this->cantidad_planificada;
    }

    public function setCantidadPlanificada(float $cantidad_planificada): static
    {
        $this->cantidad_planificada = $cantidad_planificada;

        return $this;
    }

    public function getCantidadUsada(): float
    {
        return $this->cantidad_usada;
    }

    public function setCantidadUsada(float $cantidad_usada): static
    {
        $this->cantidad_usada = $cantidad_usada;

        return $this;
    }

    public function isStockReduced(): bool
    {
        return $this->stock_reduced;
    }

    public function setStockReduced(bool $stock_reduced): static
    {
        $this->stock_reduced = $stock_reduced;

        return $this;
    }

    public function isFromProtocol(): bool
    {
        return $this->from_protocol;
    }

    public function setFromProtocol(bool $from_protocol): static
    {
        $this->from_protocol = $from_protocol;

        return $this;
    }

    public function getProtocolNotes(): ?string
    {
        return $this->protocol_notes;
    }

    public function setProtocolNotes(?string $protocol_notes): static
    {
        $this->protocol_notes = $protocol_notes;

        return $this;
    }
}
