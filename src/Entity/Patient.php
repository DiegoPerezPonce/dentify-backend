<?php

namespace App\Entity;

use App\Repository\PatientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PatientRepository::class)]
#[ORM\Table(name: 'patient')]
#[ORM\UniqueConstraint(name: 'UNIQ_PATIENT_DNI', fields: ['dni'])]
#[ORM\UniqueConstraint(name: 'UNIQ_PATIENT_EMAIL', fields: ['email'])]
class Patient
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

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank]
    private ?string $dni = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $nss = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $telefono = null;

    #[ORM\Column(length: 180, nullable: true)]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $enfermedades = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $alergias = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $historial_clinico = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $datos_facturacion = null;

    /**
     * @var Collection<int, ClinicalHistory>
     */
    #[ORM\OneToMany(mappedBy: 'patient', targetEntity: ClinicalHistory::class, orphanRemoval: true)]
    private Collection $clinicalHistories;

    public function __construct()
    {
        $this->clinicalHistories = new ArrayCollection();
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

    public function getDni(): ?string
    {
        return $this->dni;
    }

    public function setDni(string $dni): static
    {
        $this->dni = $dni;

        return $this;
    }

    public function getNss(): ?string
    {
        return $this->nss;
    }

    public function setNss(?string $nss): static
    {
        $this->nss = $nss;

        return $this;
    }

    public function getTelefono(): ?string
    {
        return $this->telefono;
    }

    public function setTelefono(?string $telefono): static
    {
        $this->telefono = $telefono;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getEnfermedades(): ?string
    {
        return $this->enfermedades;
    }

    public function setEnfermedades(?string $enfermedades): static
    {
        $this->enfermedades = $enfermedades;

        return $this;
    }

    public function getAlergias(): ?string
    {
        return $this->alergias;
    }

    public function setAlergias(?string $alergias): static
    {
        $this->alergias = $alergias;

        return $this;
    }

    public function getHistorialClinico(): ?string
    {
        return $this->historial_clinico;
    }

    public function setHistorialClinico(?string $historial_clinico): static
    {
        $this->historial_clinico = $historial_clinico;

        return $this;
    }

    public function getDatosFacturacion(): ?string
    {
        return $this->datos_facturacion;
    }

    public function setDatosFacturacion(?string $datos_facturacion): static
    {
        $this->datos_facturacion = $datos_facturacion;

        return $this;
    }

    /**
     * @return Collection<int, ClinicalHistory>
     */
    public function getClinicalHistories(): Collection
    {
        return $this->clinicalHistories;
    }

    public function addClinicalHistory(ClinicalHistory $clinicalHistory): static
    {
        if (!$this->clinicalHistories->contains($clinicalHistory)) {
            $this->clinicalHistories->add($clinicalHistory);
            $clinicalHistory->setPatient($this);
        }

        return $this;
    }

    public function removeClinicalHistory(ClinicalHistory $clinicalHistory): static
    {
        if ($this->clinicalHistories->removeElement($clinicalHistory)) {
            // set the owning side to null (unless already changed)
            if ($clinicalHistory->getPatient() === $this) {
                $clinicalHistory->setPatient(null);
            }
        }

        return $this;
    }
}
