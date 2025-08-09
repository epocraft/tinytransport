<?php

namespace App\Entity\Web;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'service_provider_vehicle')]
class ServiceProviderVehicle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ServiceProvider::class, inversedBy: 'vehicles')]
    #[ORM\JoinColumn(name: 'service_provider_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ServiceProvider $serviceProvider;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private string $name = '';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(name: 'license_plate', type: 'string', length: 64)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 64)]
    private string $licensePlate = '';

    #[ORM\Column(name: 'capacity_kg', type: 'integer', nullable: true)]
    #[Assert\Range(min: 0, max: 50000)]
    private ?int $capacityKg = null;

    // DECIMAL(8,2) → string
    #[ORM\Column(name: 'volume_m3', type: 'decimal', precision: 8, scale: 2, nullable: true)]
    private ?string $volumeM3 = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $refrigerated = null;

    #[ORM\Column(name: 'tail_lift', type: 'boolean', nullable: true)]
    private ?bool $tailLift = null;

    #[ORM\Column(name: 'created_at', type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'boolean', options: ['default' => 1])]
    private bool $publication = true;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    // --- getters/setters ---

    public function getId(): ?int { return $this->id; }

    public function getServiceProvider(): ServiceProvider { return $this->serviceProvider; }
    public function setServiceProvider(ServiceProvider $serviceProvider): self { $this->serviceProvider = $serviceProvider; return $this; }

    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): self { $this->description = $description; return $this; }

    public function getLicensePlate(): string { return $this->licensePlate; }
    public function setLicensePlate(string $licensePlate): self { $this->licensePlate = $licensePlate; return $this; }

    public function getCapacityKg(): ?int { return $this->capacityKg; }
    public function setCapacityKg(?int $capacityKg): self { $this->capacityKg = $capacityKg; return $this; }

    public function getVolumeM3(): ?string { return $this->volumeM3; }
    public function setVolumeM3(?string $volumeM3): self { $this->volumeM3 = $volumeM3; return $this; }

    public function getRefrigerated(): ?bool { return $this->refrigerated; }
    public function setRefrigerated(?bool $refrigerated): self { $this->refrigerated = $refrigerated; return $this; }

    public function getTailLift(): ?bool { return $this->tailLift; }
    public function setTailLift(?bool $tailLift): self { $this->tailLift = $tailLift; return $this; }

    public function getCreatedAt(): \DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(\DateTimeInterface $createdAt): self { $this->createdAt = $createdAt; return $this; }

    public function isPublication(): bool { return $this->publication; }
    public function setPublication(bool $publication): self { $this->publication = $publication; return $this; }

    public function __toString(): string
    {
        return $this->name.' ('.$this->licensePlate.')';
    }
}
