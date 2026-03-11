<?php

namespace App\Entity;

use App\Repository\OdontogramaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OdontogramaRepository::class)]
#[ORM\Table(name: 'odontograma')]
#[ORM\HasLifecycleCallbacks]
class Odontograma
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'odontogramas')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Patient $patient = null;

    #[ORM\Column(type: Types::JSON)]
    private array $dientes = [];

    #[ORM\Column]
    private ?\DateTimeImmutable $fecha_creacion = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $fecha_modificacion = null;

    /**
     * @var Collection<int, Patologia>
     */
    #[ORM\OneToMany(mappedBy: 'odontograma', targetEntity: Patologia::class, orphanRemoval: true)]
    private Collection $patologias;

    public function __construct()
    {
        $this->patologias = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDientes(): array
    {
        return $this->dientes;
    }

    public function setDientes(array $dientes): static
    {
        $this->dientes = $dientes;

        return $this;
    }

    public function getFechaCreacion(): ?\DateTimeImmutable
    {
        return $this->fecha_creacion;
    }

    public function setFechaCreacion(\DateTimeImmutable $fecha_creacion): static
    {
        $this->fecha_creacion = $fecha_creacion;

        return $this;
    }

    public function getFechaModificacion(): ?\DateTimeImmutable
    {
        return $this->fecha_modificacion;
    }

    public function setFechaModificacion(\DateTimeImmutable $fecha_modificacion): static
    {
        $this->fecha_modificacion = $fecha_modificacion;

        return $this;
    }

    #[ORM\PrePersist]
    public function setFechaCreacionValue(): void
    {
        $this->fecha_creacion = new \DateTimeImmutable();
        $this->fecha_modificacion = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function setFechaModificacionValue(): void
    {
        $this->fecha_modificacion = new \DateTimeImmutable();
    }

    /**
     * @return Collection<int, Patologia>
     */
    public function getPatologias(): Collection
    {
        return $this->patologias;
    }

    public function addPatologia(Patologia $patologia): static
    {
        if (!$this->patologias->contains($patologia)) {
            $this->patologias->add($patologia);
            $patologia->setOdontograma($this);
        }

        return $this;
    }

    public function removePatologia(Patologia $patologia): static
    {
        if ($this->patologias->removeElement($patologia)) {
            // set the owning side to null (unless already changed)
            if ($patologia->getOdontograma() === $this) {
                $patologia->setOdontograma(null);
            }
        }

        return $this;
    }
}
