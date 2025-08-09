<?php

namespace App\Entity\Web;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'service_provider_contact')]
class ServiceProviderContact
{
    public const TYPE_EMAIL = 'email';
    public const TYPE_PHONE = 'phone';
    public const TYPE_WEB   = 'web';
    public const TYPE_SOCIAL = 'social';
    public const TYPE_IM     = 'im';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ServiceProvider::class, inversedBy: 'contacts')]
    #[ORM\JoinColumn(name: 'service_provider_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ServiceProvider $serviceProvider;

    #[ORM\Column(type: 'string', length: 16)]
    #[Assert\Choice(choices: [self::TYPE_EMAIL, self::TYPE_PHONE, self::TYPE_WEB, self::TYPE_SOCIAL, self::TYPE_IM])]
    private string $type = self::TYPE_EMAIL;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $label = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private string $value = '';

    #[ORM\Column(type: 'boolean', options: ['default' => 1])]
    private bool $publication = true;

    // --- getters/setters ---

    public function getId(): ?int { return $this->id; }

    public function getServiceProvider(): ServiceProvider { return $this->serviceProvider; }
    public function setServiceProvider(ServiceProvider $serviceProvider): self { $this->serviceProvider = $serviceProvider; return $this; }

    public function getType(): string { return $this->type; }
    public function setType(string $type): self { $this->type = $type; return $this; }

    public function getLabel(): ?string { return $this->label; }
    public function setLabel(?string $label): self { $this->label = $label; return $this; }

    public function getValue(): string { return $this->value; }
    public function setValue(string $value): self { $this->value = $value; return $this; }

    public function isPublication(): bool { return $this->publication; }
    public function setPublication(bool $publication): self { $this->publication = $publication; return $this; }

    public function __toString(): string
    {
        return '['.$this->type.'] '.$this->value;
    }
}
