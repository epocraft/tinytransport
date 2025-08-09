<?php

namespace App\Entity\Web;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'quote')]
class Quote
{
    public const STATUS_OPEN      = 'open';
    public const STATUS_SELECTED  = 'selected';
    public const STATUS_CLOSED    = 'closed';
    public const STATUS_CANCELLED = 'cancelled';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    // Vlastník poptávky (zákazník)
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private User $user;

    // Kategorie služby
    #[ORM\ManyToOne(targetEntity: Category::class)]
    #[ORM\JoinColumn(name: 'category_id', referencedColumnName: 'id', nullable: false)]
    private Category $category;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private string $name = '';

    // --- NAKLÁDKA ---
    #[ORM\Column(name: 'loading_address', type: 'string', length: 255)]
    #[Assert\NotBlank]
    private string $loadingAddress = '';

    // původně tinyint → boolean
    #[ORM\Column(name: 'loading_address_assistance', type: 'boolean', nullable: true)]
    private ?bool $loadingAddressAssistance = null;

    #[ORM\Column(name: 'loading_address_assistance_number_of_persons', type: 'smallint', nullable: true)]
    private ?int $loadingAddressAssistanceNumberOfPersons = null;

    #[ORM\Column(name: 'loading_address_floor', type: 'smallint', nullable: true)]
    private ?int $loadingAddressFloor = null;

    // v DB smallint, sémanticky boolean → mapujeme jako boolean
    #[ORM\Column(name: 'loading_address_lift', type: 'boolean', nullable: true)]
    private ?bool $loadingAddressLift = null;

    // DECIMAL(10,2) → string
    #[ORM\Column(name: 'loading_address_width_of_staircase', type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $loadingAddressWidthOfStaircase = null;

    // původně tinyint → boolean
    #[ORM\Column(name: 'loading_address_ramp', type: 'boolean', nullable: true)]
    private ?bool $loadingAddressRamp = null;

    // --- VYKLÁDKA ---
    #[ORM\Column(name: 'unloading_address', type: 'string', length: 255)]
    #[Assert\NotBlank]
    private string $unloadingAddress = '';

    #[ORM\Column(name: 'unloading_address_assistance', type: 'boolean', nullable: true)]
    private ?bool $unloadingAddressAssistance = null;

    #[ORM\Column(name: 'unloading_address_assistance_number_of_persons', type: 'smallint', nullable: true)]
    private ?int $unloadingAddressAssistanceNumberOfPersons = null;

    #[ORM\Column(name: 'unloading_address_floor', type: 'smallint', nullable: true)]
    private ?int $unloadingAddressFloor = null;

    #[ORM\Column(name: 'unloading_address_lift', type: 'boolean', nullable: true)]
    private ?bool $unloadingAddressLift = null;

    #[ORM\Column(name: 'unloading_address_width_of_staircase', type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $unloadingAddressWidthOfStaircase = null;

    #[ORM\Column(name: 'unloading_address_ramp', type: 'boolean', nullable: true)]
    private ?bool $unloadingAddressRamp = null;

    #[ORM\Column(name: 'delivery_timeframe', type: 'string', length: 255, nullable: true)]
    private ?string $deliveryTimeframe = null;

    // --- Stav + audit ---
    #[ORM\Column(type: 'string', length: 32, options: ['default' => self::STATUS_OPEN])]
    #[Assert\Choice(choices: [self::STATUS_OPEN, self::STATUS_SELECTED, self::STATUS_CLOSED, self::STATUS_CANCELLED])]
    private string $status = self::STATUS_OPEN;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(name: 'chosen_at', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $chosenAt = null;

    #[ORM\Column(name: 'closed_at', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $closedAt = null;

    // Vybraná nabídka (nullable, SET NULL při smazání nabídky)
    #[ORM\ManyToOne(targetEntity: Offer::class)]
    #[ORM\JoinColumn(name: 'chosen_offer_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Offer $chosenOffer = null;

    /** @var Collection<int, QuoteItem> */
    #[ORM\OneToMany(mappedBy: 'quote', targetEntity: QuoteItem::class, cascade: ['persist','remove'], orphanRemoval: true)]
    private Collection $items;

    /** @var Collection<int, Offer> */
    #[ORM\OneToMany(mappedBy: 'quote', targetEntity: Offer::class)]
    private Collection $offers;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->items = new ArrayCollection();
        $this->offers = new ArrayCollection();
    }

    // --- getters/setters ---

    public function getId(): ?int { return $this->id; }

    public function getUser(): User { return $this->user; }
    public function setUser(User $user): self { $this->user = $user; return $this; }

    public function getCategory(): Category { return $this->category; }
    public function setCategory(Category $category): self { $this->category = $category; return $this; }

    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }

    public function getLoadingAddress(): string { return $this->loadingAddress; }
    public function setLoadingAddress(string $loadingAddress): self { $this->loadingAddress = $loadingAddress; return $this; }

    public function getLoadingAddressAssistance(): ?bool { return $this->loadingAddressAssistance; }
    public function setLoadingAddressAssistance(?bool $v): self { $this->loadingAddressAssistance = $v; return $this; }

    public function getLoadingAddressAssistanceNumberOfPersons(): ?int { return $this->loadingAddressAssistanceNumberOfPersons; }
    public function setLoadingAddressAssistanceNumberOfPersons(?int $v): self { $this->loadingAddressAssistanceNumberOfPersons = $v; return $this; }

    public function getLoadingAddressFloor(): ?int { return $this->loadingAddressFloor; }
    public function setLoadingAddressFloor(?int $v): self { $this->loadingAddressFloor = $v; return $this; }

    public function getLoadingAddressLift(): ?bool { return $this->loadingAddressLift; }
    public function setLoadingAddressLift(?bool $v): self { $this->loadingAddressLift = $v; return $this; }

    public function getLoadingAddressWidthOfStaircase(): ?string { return $this->loadingAddressWidthOfStaircase; }
    public function setLoadingAddressWidthOfStaircase(?string $v): self { $this->loadingAddressWidthOfStaircase = $v; return $this; }

    public function getLoadingAddressRamp(): ?bool { return $this->loadingAddressRamp; }
    public function setLoadingAddressRamp(?bool $v): self { $this->loadingAddressRamp = $v; return $this; }

    public function getUnloadingAddress(): string { return $this->unloadingAddress; }
    public function setUnloadingAddress(string $unloadingAddress): self { $this->unloadingAddress = $unloadingAddress; return $this; }

    public function getUnloadingAddressAssistance(): ?bool { return $this->unloadingAddressAssistance; }
    public function setUnloadingAddressAssistance(?bool $v): self { $this->unloadingAddressAssistance = $v; return $this; }

    public function getUnloadingAddressAssistanceNumberOfPersons(): ?int { return $this->unloadingAddressAssistanceNumberOfPersons; }
    public function setUnloadingAddressAssistanceNumberOfPersons(?int $v): self { $this->unloadingAddressAssistanceNumberOfPersons = $v; return $this; }

    public function getUnloadingAddressFloor(): ?int { return $this->unloadingAddressFloor; }
    public function setUnloadingAddressFloor(?int $v): self { $this->unloadingAddressFloor = $v; return $this; }

    public function getUnloadingAddressLift(): ?bool { return $this->unloadingAddressLift; }
    public function setUnloadingAddressLift(?bool $v): self { $this->unloadingAddressLift = $v; return $this; }

    public function getUnloadingAddressWidthOfStaircase(): ?string { return $this->unloadingAddressWidthOfStaircase; }
    public function setUnloadingAddressWidthOfStaircase(?string $v): self { $this->unloadingAddressWidthOfStaircase = $v; return $this; }

    public function getUnloadingAddressRamp(): ?bool { return $this->unloadingAddressRamp; }
    public function setUnloadingAddressRamp(?bool $v): self { $this->unloadingAddressRamp = $v; return $this; }

    public function getDeliveryTimeframe(): ?string { return $this->deliveryTimeframe; }
    public function setDeliveryTimeframe(?string $deliveryTimeframe): self { $this->deliveryTimeframe = $deliveryTimeframe; return $this; }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }

    public function getCreatedAt(): \DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(\DateTimeInterface $createdAt): self { $this->createdAt = $createdAt; return $this; }

    public function getChosenAt(): ?\DateTimeInterface { return $this->chosenAt; }
    public function setChosenAt(?\DateTimeInterface $chosenAt): self { $this->chosenAt = $chosenAt; return $this; }

    public function getClosedAt(): ?\DateTimeInterface { return $this->closedAt; }
    public function setClosedAt(?\DateTimeInterface $closedAt): self { $this->closedAt = $closedAt; return $this; }

    public function getChosenOffer(): ?Offer { return $this->chosenOffer; }
    public function setChosenOffer(?Offer $chosenOffer): self { $this->chosenOffer = $chosenOffer; return $this; }

    /** @return Collection<int, QuoteItem> */
    public function getItems(): Collection { return $this->items; }
    public function addItem(QuoteItem $item): self
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setQuote($this);
        }
        return $this;
    }
    public function removeItem(QuoteItem $item): self
    {
        if ($this->items->removeElement($item)) {
            if ($item->getQuote() === $this) { /* orphanRemoval */ }
        }
        return $this;
    }

    /** @return Collection<int, Offer> */
    public function getOffers(): Collection { return $this->offers; }

    public function __toString(): string
    {
        return $this->name ?: 'Quote#' . $this->id;
    }
}
