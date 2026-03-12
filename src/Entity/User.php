<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

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
    private ?string $password = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $ultimo_acceso = null;

    public function getId(): ?int
    {
        return $this->id;
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
     * Symfony usa este identificador internamente.
     * Aquí usamos el email como identificador principal.
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * Roles del usuario
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        // Garantiza que siempre tenga ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * Password hasheado
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Limpia datos sensibles (no lo usamos aquí)
     */
    public function eraseCredentials(): void
    {
        // Si guardaras un plainPassword, aquí lo limpiarías
    }

    public function getUltimoAcceso(): ?\DateTime
    {
        return $this->ultimo_acceso;
    }

    public function setUltimoAcceso(?\DateTime $ultimo_acceso): static
    {
        $this->ultimo_acceso = $ultimo_acceso;
        return $this;
    }
}