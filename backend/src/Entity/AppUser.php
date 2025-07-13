<?php

namespace App\Entity;

use App\Repository\AppUserRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: AppUserRepository::class)]
class AppUser implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 180, unique: true)]
    private string $username;

    #[ORM\Column(type: "string")]
    private string $password;

    #[ORM\Column(type: "string", length: 100, unique: true)]
    private string $email;

    #[ORM\Column(type: "string", length: 25, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $description = null;

    /**
     * Many users have many roles.
     * This is for RBAC and permissions.
     */
    #[ORM\ManyToMany(targetEntity: Role::class)]
    #[ORM\JoinTable(name: "app_user_roles")]
    private Collection $roles;

    #[ORM\Column(type: "boolean")]
    private bool $locked = false;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
    }

    // --- Basic fields ---
    public function getId(): ?int { return $this->id; }

    public function getUsername(): string { return $this->username; }
    public function setUsername(string $username): self { $this->username = $username; return $this; }

    public function getPassword(): string { return $this->password; }
    public function setPassword(string $password): self { $this->password = $password; return $this; }

    public function getEmail(): string { return $this->email; }
    public function setEmail(string $email): self { $this->email = $email; return $this; }

    public function getPhone(): ?string { return $this->phone; }
    public function setPhone(?string $phone): self { $this->phone = $phone; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): self { $this->description = $description; return $this; }

    public function isLocked(): bool { return $this->locked; }
    public function setLocked(bool $locked): self { $this->locked = $locked; return $this; }

    // --- RBAC: Roles as objects ---
    /**
     * @return Collection|Role[]
     */
    public function getRoleEntities(): Collection
    {
        return $this->roles;
    }
    public function addRole(Role $role): self
    {
        if (!$this->roles->contains($role)) {
            $this->roles[] = $role;
        }
        return $this;
    }
    public function removeRole(Role $role): self
    {
        $this->roles->removeElement($role);
        return $this;
    }

    // --- UserInterface: Roles as strings for Symfony ---
    public function getRoles(): array
    {
        // Return role codes (strings) for Symfony
        $roleCodes = $this->roles->map(fn($role) => $role->getCode())->toArray();
        $roleCodes[] = 'ROLE_USER';
        return array_unique($roleCodes);
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data, clear it here
    }
}
