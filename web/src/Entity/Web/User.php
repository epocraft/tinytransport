<?php

namespace App\Entity\Web;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'user')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private string $email = '';

    #[ORM\Column(type: 'string', length: 255)]
    private string $password = '';

    /**
     * @var string[]
     */
    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(name: 'last_login', type: 'datetime')]
    private \DateTimeInterface $lastLogin;

    #[ORM\Column(name: 'publication', type: 'boolean')]
    private bool $publication = true;

    public function __construct()
    {
        $this->lastLogin = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getEmail(): string { return $this->email; }
    public function setEmail(string $email): self { $this->email = $email; return $this; }

    public function getUserIdentifier(): string { return $this->email; }

    /** @deprecated use getUserIdentifier() */
    public function getUsername(): string { return $this->email; }

    public function getPassword(): string { return $this->password; }
    public function setPassword(string $password): self { $this->password = $password; return $this; }

    public function eraseCredentials(): void
    {
        // Pokud bys ukládal plain password, tady ho vyčisti
    }

    /**
     * Vždy vrátí aspoň ROLE_USER a ROLE_CUSTOMER (baseline).
     * ROLE_PROVIDER přidávej podle vazeb/spu.
     *
     * @return string[]
     */
    public function getRoles(): array
    {
        $roles = $this->roles ?? [];
        if (!in_array('ROLE_USER', $roles, true)) {
            $roles[] = 'ROLE_USER';
        }
        if (!in_array('ROLE_CUSTOMER', $roles, true)) {
            $roles[] = 'ROLE_CUSTOMER';
        }
        return array_values(array_unique($roles));
    }

    /**
     * Nastaví role bez baseline úprav (getRoles() je doplní při čtení).
     *
     * @param string[] $roles
     */
    public function setRoles(array $roles): self
    {
        // Normalizace
        $roles = array_values(array_unique(array_map('strval', $roles)));
        $this->roles = $roles;
        return $this;
    }

    public function hasRole(string $role): bool
    {
        return in_array(strtoupper($role), $this->getRoles(), true);
    }

    public function addRole(string $role): self
    {
        $role = strtoupper($role);
        if (!$this->hasRole($role)) {
            $this->roles[] = $role;
        }
        return $this;
    }

    public function removeRole(string $role): self
    {
        $role = strtoupper($role);
        $this->roles = array_values(array_filter(
            $this->roles,
            fn ($r) => strtoupper($r) !== $role
        ));
        return $this;
    }

    public function getLastLogin(): \DateTimeInterface { return $this->lastLogin; }
    public function setLastLogin(\DateTimeInterface $dt): self { $this->lastLogin = $dt; return $this; }

    public function isPublication(): bool { return $this->publication; }
    public function setPublication(bool $publication): self { $this->publication = $publication; return $this; }

    public function __toString(): string
    {
        return $this->email;
    }
}
