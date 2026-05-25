<?php

namespace App\Entity;

use App\Domain\User\UiPreferences;
use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'user')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $username = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    #[Assert\NotBlank]
    private ?string $password = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $ultimo_acceso = null;

    #[ORM\ManyToOne(targetEntity: Dentist::class)]
    #[ORM\JoinColumn(name: 'dentist_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Dentist $dentist = null;

    /** @var array<string, mixed>|null Preferencias de tema / layout (por usuario). */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $ui_preferences = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    // Email
    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    // Username
    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;
        return $this;
    }

    /**
     * Symfony usa este identificador internamente (JWT y login)
     * Aquí usamos el email como identificador principal.
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    // Roles
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    // Password
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    // Limpia credenciales sensibles
    public function eraseCredentials(): void
    {
        // Si usaras un plainPassword, aquí lo limpiarías
    }

    // Último acceso
    public function getUltimoAcceso(): ?\DateTime
    {
        return $this->ultimo_acceso;
    }

    public function setUltimoAcceso(?\DateTime $ultimo_acceso): static
    {
        $this->ultimo_acceso = $ultimo_acceso;
        return $this;
    }

    public function getDentist(): ?Dentist
    {
        return $this->dentist;
    }

    public function setDentist(?Dentist $dentist): static
    {
        $this->dentist = $dentist;

        return $this;
    }

    /** @return array<string, mixed>|null */
    public function getUiPreferencesRaw(): ?array
    {
        return $this->ui_preferences;
    }

    public function getUiPreferences(): UiPreferences
    {
        return UiPreferences::fromArray($this->ui_preferences);
    }

    public function setUiPreferences(UiPreferences $preferences): static
    {
        $this->ui_preferences = $preferences->toArray();

        return $this;
    }
}