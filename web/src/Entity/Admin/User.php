<?php

namespace App\Entity\Admin;

use App\Repository\Admin\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $email = null;

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    /**
     * @var Collection<int, DateLog>
     */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: DateLog::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $userDateLogs;

    public function __construct()
    {
        $this->userDateLogs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column(type: 'json')]
    private $roles = [];

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $lastLogin = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $publication = null;

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?UserContact $userUserContact = null;

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?UserSettings $userUserSettings = null;

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
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // Zaručení, že je vždy aspoň jedna role
        if (empty($roles)) {
            $roles[] = 'ROLE_USER';
        }
        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getLastLogin(): ?\DateTimeImmutable
    {
        return $this->lastLogin;
    }

    public function setLastLogin(?\DateTimeImmutable $lastLogin): static
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }

    public function getPublication(): ?int
    {
        return $this->publication;
    }

    public function setPublication(int $publication): static
    {
        $this->publication = $publication;

        return $this;
    }

    public function getUserUserContact(): ?UserContact
    {
        return $this->userUserContact;
    }

    public function setUserUserContact(?UserContact $userUserContact): self
    {
        if ($userUserContact && $userUserContact->getUser() !== $this) {
            $userUserContact->setUser($this);
        }
        $this->userUserContact = $userUserContact;

        return $this;
    }

    public function getUserUserSettings(): ?UserSettings
    {
        return $this->userUserSettings;
    }

    public function setUserUserSettings(?UserSettings $userUserSettings): self
    {
        if ($userUserSettings && $userUserSettings->getUser() !== $this) {
            $userUserSettings->setUser($this);
        }
        $this->userUserSettings = $userUserSettings;

        return $this;
    }

    /**
     * @return Collection<int, DateLog>
     */
    public function getUserDateLogs(): Collection
    {
        return $this->userDateLogs;
    }

    public function addUserDateLog(DateLog $userDateLog): static
    {
        if (!$this->userDateLogs->contains($userDateLog)) {
            $this->userDateLogs[] = $userDateLog;
            $userDateLog->setUser($this);
        }

        return $this;
    }

    public function removeUserDateLog(DateLog $userDateLog): static
    {
        if ($this->userDateLogs->removeElement($userDateLog)) {
            // set the owning side to null (unless already changed)
            if ($userDateLog->getUser() === $this) {
                $userDateLog->setUser(null);
            }
        }

        return $this;
    }

    public function getFullName(): string
    {
        $userContact = $this->getUserUserContact();
        
        if ($userContact) {
            return $userContact->getTitleBefore() . ' ' . $userContact->getFirstname() . ' ' . $userContact->getSurname() . ' ' . $userContact->getTitleAfter();
        }
        
        return '';
    }
}
