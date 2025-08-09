<?php

namespace App\Entity\Web;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Vazební entita (N:M) s rolí.
 * DB má kompozitní PK (service_provider_id + user_id) – zachováváme.
 */
#[ORM\Entity]
#[ORM\Table(name: 'service_provider_user')]
class ServiceProviderUser
{
    public const ROLE_OWNER = 'owner';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_STAFF = 'staff';

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: ServiceProvider::class, inversedBy: 'managerLinks')]
    #[ORM\JoinColumn(name: 'service_provider_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ServiceProvider $serviceProvider;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'providerLinks')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    private User $user;

    #[ORM\Column(name: 'role', type: 'string', length: 16, options: ['default' => self::ROLE_OWNER])]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: [self::ROLE_OWNER, self::ROLE_ADMIN, self::ROLE_STAFF])]
    private string $role = self::ROLE_OWNER;

    public function getServiceProvider(): ServiceProvider { return $this->serviceProvider; }
    public function setServiceProvider(ServiceProvider $serviceProvider): self { $this->serviceProvider = $serviceProvider; return $this; }

    public function getUser(): User { return $this->user; }
    public function setUser(User $user): self { $this->user = $user; return $this; }

    public function getRole(): string { return $this->role; }
    public function setRole(string $role): self { $this->role = $role; return $this; }
}
