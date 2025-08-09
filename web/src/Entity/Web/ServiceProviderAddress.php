<?php

namespace App\Entity\Web;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'service_provider_address')]
class ServiceProviderAddress
{
    public const KIND_REGISTERED = 'registered';
    public const KIND_OFFICE     = 'office';
    public const KIND_BILLING    = 'billing';
    public const KIND_WAREHOUSE  = 'warehouse';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ServiceProvider::class, inversedBy: 'addresses')]
    #[ORM\JoinColumn(name: 'service_provider_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ServiceProvider $serviceProvider;

    #[ORM\Column(type: 'string', length: 16, options: ['default' => self::KIND_OFFICE])]
    #[Assert\Choice(choices: [self::KIND_REGISTERED, self::KIND_OFFICE, self::KIND_BILLING, self::KIND_WAREHOUSE])]
    private string $kind = self::KIND_OFFICE;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    private string $street = '';

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    private string $city = '';

    #[ORM\Column(type: 'string', length: 20)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 20)]
    private string $zipcode = '';

    #[ORM\Column(type: 'string', length: 2, options: ['default' => 'CZ'])]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 2)]
    private string $country = 'CZ';

    // DECIMAL → string
    #[ORM\Column(type: 'decimal', precision: 10, scale: 7, nullable: true)]
    private ?string $lat = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 7, nullable: true)]
    private ?string $lng = null;

    #[ORM\Column(name: 'is_primary', type: 'boolean', options: ['default' => 0])]
    private bool $isPrimary = false;

    // --- getters/setters ---

    public function getId(): ?int { return $this->id; }

    public function getServiceProvider(): ServiceProvider { return $this->serviceProvider; }
    public function setServiceProvider(ServiceProvider $serviceProvider): self { $this->serviceProvider = $serviceProvider; return $this; }

    public function getKind(): string { return $this->kind; }
    public function setKind(string $kind): self { $this->kind = $kind; return $this; }

    public function getName(): ?string { return $this->name; }
    public function setName(?string $name): self { $this->name = $name; return $this; }

    public function getStreet(): string { return $this->street; }
    public function setStreet(string $street): self { $this->street = $street; return $this; }

    public function getCity(): string { return $this->city; }
    public function setCity(string $city): self { $this->city = $city; return $this; }

    public function getZipcode(): string { return $this->zipcode; }
    public function setZipcode(string $zipcode): self { $this->zipcode = $zipcode; return $this; }

    public function getCountry(): string { return $this->country; }
    public function setCountry(string $country): self { $this->country = $country; return $this; }

    public function getLat(): ?string { return $this->lat; }
    public function setLat(?string $lat): self { $this->lat = $lat; return $this; }

    public function getLng(): ?string { return $this->lng; }
    public function setLng(?string $lng): self { $this->lng = $lng; return $this; }

    public function isPrimary(): bool { return $this->isPrimary; }
    public function setIsPrimary(bool $isPrimary): self { $this->isPrimary = $isPrimary; return $this; }

    public function __toString(): string
    {
        return ($this->name ?: $this->street.', '.$this->city).' ('.$this->kind.')';
    }
}
