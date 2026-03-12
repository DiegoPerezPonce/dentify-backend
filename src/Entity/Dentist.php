<?php

namespace App\Entity;

use App\Repository\DentistRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DentistRepository::class)]
#[ORM\Table(name: 'dentist')]
class Dentist
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $nombre = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $apellidos = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $especialidad = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $email = null;

    /**
     * @var Collection<int, Box>
     */
    #[ORM\OneToMany(mappedBy: 'dentist', targetEntity: Box::class)]
    private Collection $boxes;

    public function __construct()
    {
        $this->boxes = new ArrayCollection();
    }

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

    public function getApellidos(): ?string
    {
        return $this->apellidos;
    }

    public function setApellidos(string $apellidos): static
    {
        $this->apellidos = $apellidos;

        return $this;
    }

    public function getEspecialidad(): ?string
    {
        return $this->especialidad;
    }

    public function setEspecialidad(string $especialidad): static
    {
        $this->especialidad = $especialidad;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return Collection<int, Box>
     */
    public function getBoxes(): Collection
    {
        return $this->boxes;
    }

    public function addBox(Box $box): static
    {
        if (!$this->boxes->contains($box)) {
            $this->boxes->add($box);
            $box->setDentist($this);
        }

        return $this;
    }

    public function removeBox(Box $box): static
    {
        if ($this->boxes->removeElement($box)) {
            // set the owning side to null (unless already changed)
            if ($box->getDentist() === $this) {
                $box->setDentist(null);
            }
        }

        return $this;
    }
}
