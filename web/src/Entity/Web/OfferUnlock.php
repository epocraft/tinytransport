<?php

namespace App\Entity\Web;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'offer_unlock')]
#[UniqueEntity(fields: ['offer', 'customer'], message: 'Tuto nabídku jste již odemkli.')]
class OfferUnlock
{
    public const STATUS_PENDING  = 'pending';
    public const STATUS_ACTIVE   = 'active';
    public const STATUS_EXPIRED  = 'expired';
    public const STATUS_CANCELLED = 'cancelled';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    // FK: offer_unlock.offer_id → offer.id (ON DELETE CASCADE)
    #[ORM\ManyToOne(targetEntity: Offer::class)]
    #[ORM\JoinColumn(name: 'offer_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull]
    private Offer $offer;

    // FK: offer_unlock.customer_id → user.id (bez CASCADE)
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'customer_id', referencedColumnName: 'id', nullable: false)]
    #[Assert\NotNull]
    private User $customer;

    #[ORM\Column(type: 'string', length: 16, options: ['default' => self::STATUS_PENDING])]
    #[Assert\Choice(choices: [
        self::STATUS_PENDING, self::STATUS_ACTIVE, self::STATUS_EXPIRED, self::STATUS_CANCELLED
    ])]
    private string $status = self::STATUS_PENDING;

    // DECIMAL(5,2) → string (např. "10.00" = 10 %)
    #[ORM\Column(name: 'fee_percent', type: 'decimal', precision: 5, scale: 2)]
    #[Assert\NotNull]
    #[Assert\Range(min: 0, max: 100)]
    private string $feePercent = '0.00';

    // DECIMAL(10,2) → string (částka bez DPH)
    #[ORM\Column(name: 'fee_amount', type: 'decimal', precision: 10, scale: 2)]
    #[Assert\NotNull]
    #[Assert\GreaterThanOrEqual(0)]
    private string $feeAmount = '0.00';

    #[ORM\Column(type: 'string', length: 3, options: ['default' => 'CZK'])]
    #[Assert\Currency]
    private string $currency = 'CZK';

    #[ORM\Column(name: 'created_at', type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'])]
    #[Assert\NotNull]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(name: 'unlocked_at', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $unlockedAt = null;

    #[ORM\Column(name: 'expires_at', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $expiresAt = null;

    /** @var Collection<int, Payment> */
    #[ORM\OneToMany(mappedBy: 'unlock', targetEntity: Payment::class, cascade: ['persist','remove'], orphanRemoval: true)]
    private Collection $payments;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->payments = new ArrayCollection();
    }

    // --- getters/setters ---

    public function getId(): ?int { return $this->id; }

    public function getOffer(): Offer { return $this->offer; }
    public function setOffer(Offer $offer): self { $this->offer = $offer; return $this; }

    public function getCustomer(): User { return $this->customer; }
    public function setCustomer(User $customer): self { $this->customer = $customer; return $this; }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }

    public function getFeePercent(): string { return $this->feePercent; }
    public function setFeePercent(string $feePercent): self { $this->feePercent = $feePercent; return $this; }

    public function getFeeAmount(): string { return $this->feeAmount; }
    public function setFeeAmount(string $feeAmount): self { $this->feeAmount = $feeAmount; return $this; }

    public function getCurrency(): string { return $this->currency; }
    public function setCurrency(string $currency): self { $this->currency = $currency; return $this; }

    public function getCreatedAt(): \DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(\DateTimeInterface $createdAt): self { $this->createdAt = $createdAt; return $this; }

    public function getUnlockedAt(): ?\DateTimeInterface { return $this->unlockedAt; }
    public function setUnlockedAt(?\DateTimeInterface $unlockedAt): self { $this->unlockedAt = $unlockedAt; return $this; }

    public function getExpiresAt(): ?\DateTimeInterface { return $this->expiresAt; }
    public function setExpiresAt(?\DateTimeInterface $expiresAt): self { $this->expiresAt = $expiresAt; return $this; }

    /** @return Collection<int, Payment> */
    public function getPayments(): Collection { return $this->payments; }

    public function addPayment(Payment $payment): self
    {
        if (!$this->payments->contains($payment)) {
            $this->payments->add($payment);
            $payment->setUnlock($this);
        }
        return $this;
    }

    public function removePayment(Payment $payment): self
    {
        if ($this->payments->removeElement($payment)) {
            if ($payment->getUnlock() === $this) { /* orphanRemoval smaže payment */ }
        }
        return $this;
    }

    // --- convenience ---

    public function isPending(): bool { return $this->status === self::STATUS_PENDING; }
    public function isActive(): bool { return $this->status === self::STATUS_ACTIVE; }
    public function isExpired(): bool { return $this->status === self::STATUS_EXPIRED; }
    public function isCancelled(): bool { return $this->status === self::STATUS_CANCELLED; }

    public function __toString(): string
    {
        return 'OfferUnlock#'.$this->id.' ('.$this->status.')';
    }
}
