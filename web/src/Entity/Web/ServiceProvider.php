<?php

namespace App\Entity\Web;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'service_provider')]
class ServiceProvider
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'serviceProvidersOwned')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private User $owner;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private string $name = '';

    #[ORM\Column(type: 'string', length: 30)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 30)]
    #[Assert\Regex(pattern: '/^[a-z0-9-]+$/', message: 'Povolena malá písmena, číslice a pomlčka.')]
    private string $alias = '';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(name: 'radius_km', type: 'smallint', nullable: true, options: ['default' => 50])]
    #[Assert\Range(min: 0, max: 1000)]
    private ?int $radiusKm = 50;

    #[ORM\Column(name: 'location_address', type: 'string', length: 255, nullable: true)]
    private ?string $locationAddress = null;

    #[ORM\Column(name: 'location_lat', type: 'decimal', precision: 10, scale: 7, nullable: true)]
    private ?string $locationLat = null;

    #[ORM\Column(name: 'location_lng', type: 'decimal', precision: 10, scale: 7, nullable: true)]
    private ?string $locationLng = null;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    #[Assert\NotNull]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private bool $publication = false;

    /** @var Collection<int, Category> */
    #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'serviceProviders')]
    #[ORM\JoinTable(name: 'service_provider_category')]
    #[ORM\JoinColumn(name: 'service_provider_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'category_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Collection $categories;

    /** @var Collection<int, ServiceProviderUser> */
    #[ORM\OneToMany(mappedBy: 'serviceProvider', targetEntity: ServiceProviderUser::class, cascade: ['persist','remove'], orphanRemoval: true)]
    private Collection $managerLinks;

    /** @var Collection<int, Offer> */
    #[ORM\OneToMany(mappedBy: 'serviceProvider', targetEntity: Offer::class)]
    private Collection $offers;

    /** @var Collection<int, ServiceProviderAddress> */
    #[ORM\OneToMany(mappedBy: 'serviceProvider', targetEntity: ServiceProviderAddress::class, cascade: ['persist','remove'], orphanRemoval: true)]
    private Collection $addresses;

    /** @var Collection<int, ServiceProviderContact> */
    #[ORM\OneToMany(mappedBy: 'serviceProvider', targetEntity: ServiceProviderContact::class, cascade: ['persist','remove'], orphanRemoval: true)]
    private Collection $contacts;

    /** @var Collection<int, ServiceProviderVehicle> */
    #[ORM\OneToMany(mappedBy: 'serviceProvider', targetEntity: ServiceProviderVehicle::class, cascade: ['persist','remove'], orphanRemoval: true)]
    private Collection $vehicles;

    /** @var Collection<int, ServiceProviderOpeningHours> */
    #[ORM\OneToMany(mappedBy: 'serviceProvider', targetEntity: ServiceProviderOpeningHours::class, cascade: ['persist','remove'], orphanRemoval: true)]
    private Collection $openingHours;

    /** @var Collection<int, ServiceProviderOpeningException> */
    #[ORM\OneToMany(mappedBy: 'serviceProvider', targetEntity: ServiceProviderOpeningException::class, cascade: ['persist','remove'], orphanRemoval: true)]
    private Collection $openingExceptions;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->categories = new ArrayCollection();
        $this->managerLinks = new ArrayCollection();
        $this->offers = new ArrayCollection();
        $this->addresses = new ArrayCollection();
        $this->contacts = new ArrayCollection();
        $this->vehicles = new ArrayCollection();
        $this->openingHours = new ArrayCollection();
        $this->openingExceptions = new ArrayCollection();
    }

    // --- getters/setters/convenience ---

    public function getId(): ?int { return $this->id; }

    public function getOwner(): User { return $this->owner; }
    public function setOwner(User $owner): self { $this->owner = $owner; return $this; }

    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }

    public function getAlias(): string { return $this->alias; }
    public function setAlias(string $alias): self { $this->alias = $alias; return $this; }
    
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): self { $this->description = $description; return $this; }

    public function getRadiusKm(): ?int { return $this->radiusKm; }
    public function setRadiusKm(?int $radiusKm): self { $this->radiusKm = $radiusKm; return $this; }

    public function getLocationAddress(): ?string { return $this->locationAddress; }
    public function setLocationAddress(?string $locationAddress): self { $this->locationAddress = $locationAddress; return $this; }

    public function getLocationLat(): ?string { return $this->locationLat; }
    public function setLocationLat(?string $locationLat): self { $this->locationLat = $locationLat; return $this; }

    public function getLocationLng(): ?string { return $this->locationLng; }
    public function setLocationLng(?string $locationLng): self { $this->locationLng = $locationLng; return $this; }

    public function getCreatedAt(): \DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(\DateTimeInterface $createdAt): self { $this->createdAt = $createdAt; return $this; }

    public function isPublication(): bool { return $this->publication; }
    public function setPublication(bool $publication): self { $this->publication = $publication; return $this; }

    /** @return Collection<int, Category> */
    public function getCategories(): Collection { return $this->categories; }
    public function addCategory(Category $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
            if (!$category->getServiceProviders()->contains($this)) {
                $category->addServiceProvider($this);
            }
        }
        return $this;
    }
    public function removeCategory(Category $category): self
    {
        if ($this->categories->removeElement($category)) {
            if ($category->getServiceProviders()->contains($this)) {
                $category->removeServiceProvider($this);
            }
        }
        return $this;
    }

    /** @return Collection<int, ServiceProviderUser> */
    public function getManagerLinks(): Collection { return $this->managerLinks; }
    public function addManagerLink(ServiceProviderUser $link): self
    {
        if (!$this->managerLinks->contains($link)) {
            $this->managerLinks->add($link);
            $link->setServiceProvider($this);
        }
        return $this;
    }
    public function removeManagerLink(ServiceProviderUser $link): self
    {
        if ($this->managerLinks->removeElement($link)) {
            if ($link->getServiceProvider() === $this) { /* orphanRemoval */ }
        }
        return $this;
    }

    /** @return Collection<int, Offer> */
    public function getOffers(): Collection { return $this->offers; }

    /** @return Collection<int, ServiceProviderAddress> */
    public function getAddresses(): Collection { return $this->addresses; }
    public function addAddress(ServiceProviderAddress $a): self
    {
        if (!$this->addresses->contains($a)) {
            $this->addresses->add($a);
            $a->setServiceProvider($this);
        }
        return $this;
    }
    public function removeAddress(ServiceProviderAddress $a): self
    {
        if ($this->addresses->removeElement($a)) {
            if ($a->getServiceProvider() === $this) { /* orphanRemoval */ }
        }
        return $this;
    }
    public function getPrimaryAddress(): ?ServiceProviderAddress
    {
        foreach ($this->addresses as $addr) {
            if ($addr->isPrimary()) return $addr;
        }
        return null;
    }

    /** @return Collection<int, ServiceProviderContact> */
    public function getContacts(): Collection { return $this->contacts; }
    public function addContact(ServiceProviderContact $c): self
    {
        if (!$this->contacts->contains($c)) {
            $this->contacts->add($c);
            $c->setServiceProvider($this);
        }
        return $this;
    }
    public function removeContact(ServiceProviderContact $c): self
    {
        if ($this->contacts->removeElement($c)) {
            if ($c->getServiceProvider() === $this) { /* orphanRemoval */ }
        }
        return $this;
    }

    /** @return Collection<int, ServiceProviderVehicle> */
    public function getVehicles(): Collection { return $this->vehicles; }
    public function addVehicle(ServiceProviderVehicle $v): self
    {
        if (!$this->vehicles->contains($v)) {
            $this->vehicles->add($v);
            $v->setServiceProvider($this);
        }
        return $this;
    }
    public function removeVehicle(ServiceProviderVehicle $v): self
    {
        if ($this->vehicles->removeElement($v)) {
            if ($v->getServiceProvider() === $this) { /* orphanRemoval */ }
        }
        return $this;
    }

    /** @return Collection<int, ServiceProviderOpeningHours> */
    public function getOpeningHours(): Collection { return $this->openingHours; }
    public function addOpeningHour(ServiceProviderOpeningHours $h): self
    {
        if (!$this->openingHours->contains($h)) {
            $this->openingHours->add($h);
            $h->setServiceProvider($this);
        }
        return $this;
    }
    public function removeOpeningHour(ServiceProviderOpeningHours $h): self
    {
        if ($this->openingHours->removeElement($h)) {
            if ($h->getServiceProvider() === $this) { /* orphanRemoval */ }
        }
        return $this;
    }

    /** @return Collection<int, ServiceProviderOpeningException> */
    public function getOpeningExceptions(): Collection { return $this->openingExceptions; }
    public function addOpeningException(ServiceProviderOpeningException $e): self
    {
        if (!$this->openingExceptions->contains($e)) {
            $this->openingExceptions->add($e);
            $e->setServiceProvider($this);
        }
        return $this;
    }
    public function removeOpeningException(ServiceProviderOpeningException $e): self
    {
        if ($this->openingExceptions->removeElement($e)) {
            if ($e->getServiceProvider() === $this) { /* orphanRemoval */ }
        }
        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?: 'ServiceProvider#'.$this->id;
    }
}
